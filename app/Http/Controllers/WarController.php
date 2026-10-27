<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\WarDrop;
use App\Models\WarEntry;
use App\Support\Harga;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarController extends Controller
{
    public function index()
    {
        $drops = WarDrop::withCount('entries')
            ->where('release_at', '>=', now()->subDay())
            ->orderBy('release_at')
            ->get();

        if ($drops->isEmpty()) {
            $drops = WarDrop::withCount('entries')->latest('release_at')->take(3)->get();
        }

        $waiting = auth()->check()
            ? \App\Models\WarWaitlist::where('user_id', auth()->id())->whereIn('war_drop_id', $drops->pluck('id'))->pluck('war_drop_id')->all()
            : [];

        return view('pages.war', ['drops' => $drops, 'dp' => $drops->first()->dp_pct ?? 30, 'waiting' => $waiting]);
    }

    /** Slot penuh → masuk daftar tunggu, dikabari kalau ada slot kosong. */
    public function waitlist(Request $request, WarDrop $drop)
    {
        \App\Models\WarWaitlist::firstOrCreate(['war_drop_id' => $drop->id, 'user_id' => $request->user()->id]);

        return redirect()->route('war')->with('flash', 'Kamu masuk daftar tunggu '.$drop->name.'. Kami kabari lewat email kalau ada slot kosong.');
    }

    public function register(Request $request, WarDrop $drop)
    {
        $user = $request->user();
        if ($existing = $drop->entries()->where('user_id', $user->id)->first()) {
            return redirect()->route('war.entry', $existing)->with('flash', 'Kamu sudah terdaftar di war ini.');
        }
        if ($user->is_flagged) {
            return redirect()->route('war')->with('error', 'Akunmu sementara tidak bisa ikut war. Hubungi admin.');
        }
        if (! $drop->isOpen()) {
            return redirect()->route('war')->with('error', 'Pendaftaran war ini sudah tutup.');
        }

        return view('pages.war-daftar', [
            'drop' => $drop,
            'kurs' => Harga::negara($drop->country)['kj'],
        ]);
    }

    public function store(Request $request, WarDrop $drop)
    {
        $user = $request->user();
        if ($user->is_flagged) {
            return redirect()->route('war')->with('error', 'Akunmu sementara tidak bisa ikut war. Hubungi admin.');
        }
        if ($drop->entries()->where('user_id', $user->id)->exists()) {
            return redirect()->route('war')->with('error', 'Kamu sudah terdaftar di war ini.');
        }
        $variants = $drop->variantList();
        $data = $request->validate([
            'variant_main' => ['required', \Illuminate\Validation\Rule::in($variants)],
            'variant_backup' => 'nullable|string|max:190',
            'qty' => 'required|integer|min:1|max:'.max(1, $drop->max_per_person),
            'agree' => 'accepted',
            'dp_proof' => 'required|image|max:5120',
        ], [
            'variant_main.required' => 'Pilih varian utama.',
            'qty.max' => 'Maksimal '.$drop->max_per_person.' per orang.',
            'agree.accepted' => 'Centang dulu pernyataan aturan war.',
            'dp_proof.required' => 'Upload bukti transfer DP dulu.',
            'dp_proof.image' => 'Bukti transfer harus berupa gambar.',
        ]);

        $entry = DB::transaction(function () use ($drop, $user, $data, $request) {
            // kunci baris drop supaya nomor antrian & kuota tidak bentrok
            $fresh = WarDrop::whereKey($drop->id)->lockForUpdate()->first();
            if (! $fresh->isOpen()) {
                return null;
            }
            $kurs = Harga::negara($drop->country);
            $qty = (int) $data['qty'];
            $total = (int) round($drop->price * $qty * $kurs['kj'] + $drop->fee * $qty);
            $backup = $data['variant_backup'] ?? null;

            return WarEntry::create([
                'war_drop_id' => $drop->id,
                'user_id' => $user->id,
                'queue_no' => (int) $fresh->entries()->max('queue_no') + 1,
                'variant_main' => $data['variant_main'],
                'variant_backup' => in_array($backup, $drop->variantList(), true) ? $backup : null,
                'qty' => $qty,
                'price' => $drop->price,
                'kurs_modal' => $kurs['km'],
                'kurs_jual' => $kurs['kj'],
                'fee' => $drop->fee,
                'total' => $total,
                'dp_amount' => (int) round($total * $drop->dp_pct / 100),
                'dp_proof' => $request->file('dp_proof')->store('war', 'uploads'),
            ]);
        });

        if (! $entry) {
            return redirect()->route('war')->with('error', 'Maaf, slot war ini baru saja habis atau pendaftaran sudah tutup.');
        }

        Activity::log('**'.$user->displayName().'** membayar DP war **'.$drop->name.'**');
        Notify::send($user, 'Slot war kamu aman', [
            $drop->name.' · antrian #'.$entry->queue_no.'.',
            'DP '.rp($entry->dp_amount).' kami terima dan akan dicek admin.',
            'Kami war pada '.tgl($drop->release_at, 'j M Y H:i').' WIB. Kalau menang, tagihan sisa dikirim lewat email.',
        ], 'Lihat status', route('war.entry', $entry));
        foreach (User::notifyStaff() as $admin) {
            Notify::send($admin, 'Peserta war baru: '.$drop->name, [$user->displayName().' · antrian #'.$entry->queue_no.' · DP '.rp($entry->dp_amount)], 'Buka di admin', route('admin.war', ['drop' => $drop->id]));
        }

        return redirect()->route('war.entry', $entry);
    }

    public function entry(Request $request, WarEntry $entry)
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id || $request->user()->is_admin, 403);
        $entry->load('drop', 'order');

        return view('pages.war-entri', ['e' => $entry, 'd' => $entry->drop]);
    }

    public function settle(Request $request, WarEntry $entry)
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
        if ($entry->result !== 'menang' || $entry->settled_at) {
            return back()->with('error', 'Tidak ada tagihan pelunasan untuk war ini.');
        }
        $request->validate(['proof' => 'required|image|max:5120'], [
            'proof.required' => 'Upload bukti transfer pelunasan.',
            'proof.image' => 'Bukti transfer harus berupa gambar.',
        ]);
        $entry->update([
            'settle_proof' => $request->file('proof')->store('war', 'uploads'),
            'settle_submitted_at' => now(),
        ]);
        Activity::log('**'.$entry->user->displayName().'** mengirim bukti pelunasan war **'.$entry->drop->name.'**');

        return redirect()->route('war.entry', $entry)->with('flash', 'Bukti pelunasan terkirim. Admin akan konfirmasi secepatnya.');
    }
}
