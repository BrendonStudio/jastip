<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Order;
use App\Models\User;
use App\Models\WarDrop;
use App\Models\WarEntry;
use App\Support\Harga;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WarController extends Controller
{
    public function index(Request $request)
    {
        $drops = WarDrop::withCount('entries')->orderByDesc('release_at')->take(30)->get()
            ->sortBy(fn ($d) => [$d->step() >= 3 ? 1 : 0, $d->step() >= 3 ? -$d->release_at->timestamp : $d->release_at->timestamp])->values()->take(9);
        $new = $request->boolean('baru');
        $drop = null;
        if (! $new) {
            if ($request->filled('entry') && ($en = WarEntry::find($request->query('entry')))) {
                $drop = WarDrop::find($en->war_drop_id);
            } elseif ($request->filled('drop')) {
                $drop = WarDrop::find($request->query('drop'));
            } else {
                // Drop yang belum rilis & paling dekat; kalau tidak ada, yang terakhir
                $drop = WarDrop::where('release_at', '>=', now())->orderBy('release_at')->first() ?? $drops->first();
            }
        }
        $drop?->load(['entries' => fn ($q) => $q->orderBy('queue_no'), 'entries.user', 'entries.order', 'entries.drop']);

        $fans = 0;
        if ($drop && $drop->group_name) {
            $fans = User::where('is_admin', false)->where('notify_war', true)->where('favorite_group', 'like', '%'.$drop->group_name.'%')->count();
        }

        return view('admin.war', ['drops' => $drops, 'drop' => $drop, 'isNew' => $new || ! $drop, 'fans' => $fans]);
    }

    protected function validated(Request $request): array
    {
        $d = $request->validate([
            'name' => 'required|string|max:190',
            'group_name' => 'nullable|string|max:120',
            'release_url' => 'nullable|url|max:1000',
            'country' => 'required|in:jp,kr',
            'release_date' => 'required|date',
            'release_time' => 'required|date_format:H:i',
            'store_name' => 'nullable|string|max:120',
            'price' => 'required|numeric|min:0',
            'variants' => 'nullable|string|max:500',
            'max_per_person' => 'required|integer|min:1|max:20',
            'fee' => 'required|integer|min:0',
            'quota' => 'required|integer|min:1|max:999',
            'close_hours' => 'required|integer|min:0|max:240',
            'dp_pct' => 'required|integer|min:1|max:100',
            'settle_hours' => 'required|integer|min:1|max:720',
            'refund_mode' => 'required|in:potong,penuh',
            'image' => 'nullable|image|max:5120',
        ], ['name.required' => 'Isi nama produk.', 'release_date.required' => 'Isi tanggal rilis.']);

        // Jam yang diisi = jam lokal JST/KST (WIB + 2). Simpan dalam WIB.
        $d['release_at'] = Carbon::parse($d['release_date'].' '.$d['release_time'], config('app.timezone'))->subHours(2);
        $d['variants'] = array_values(array_filter(array_map('trim', explode(',', (string) ($d['variants'] ?? '')))));
        if ($request->hasFile('image')) {
            $d['image'] = $request->file('image')->store('war', 'uploads');
        } else {
            unset($d['image']);
        }
        unset($d['release_date'], $d['release_time']);

        return $d;
    }

    public function store(Request $request)
    {
        $drop = WarDrop::create($this->validated($request));
        Activity::log('Drop war baru **'.$drop->name.'** dibuat');

        return redirect()->route('admin.war', ['drop' => $drop->id])->with('flash', 'Drop war dibuat dan tampil di halaman Jasa war.');
    }

    public function update(Request $request, WarDrop $drop)
    {
        $drop->update($this->validated($request));
        $n = \App\Models\WarWaitlist::notifyOpen($drop->fresh());

        return redirect()->route('admin.war', ['drop' => $drop->id])->with('flash', 'Drop war disimpan.'.($n ? ' '.$n.' orang di daftar tunggu dikabari ada slot kosong.' : ''));
    }

    public function notifyFans(WarDrop $drop)
    {
        if (! $drop->group_name) {
            return back()->with('error', 'Isi nama grup dulu.');
        }
        $users = User::where('is_admin', false)->where('notify_war', true)->where('favorite_group', 'like', '%'.$drop->group_name.'%')->get();
        foreach ($users as $u) {
            Notify::send($u, $drop->name, [
                'Grup favoritmu punya rilisan terbatas. Daftar war sekarang dengan DP '.$drop->dp_pct.'%:',
            ], 'Daftar war', route('war.register', $drop), null, [
                'tag' => 'WAR BARU · '.mb_strtoupper($drop->group_name), 'c' => 'red', 'marketing' => true,
                'subject' => 'War baru: '.$drop->name, 'pre' => 'Daftar sebelum '.tgl($drop->closesAt(), 'j M H:i').' WIB. Slot terbatas.',
                'rows' => [['Rilis', tgl($drop->release_at, 'j M').' · '.$drop->release_at->format('H:i').' WIB'], ['Slot', (string) $drop->quota], ['Tutup pendaftaran', $drop->close_hours.' jam sebelum rilis']],
            ]);
        }

        return back()->with('flash', 'Notifikasi dikirim ke '.$users->count().' penitip.');
    }

    public function setResult(Request $request, WarEntry $entry)
    {
        if ($entry->order_id || $entry->settled_at || $entry->refunded_at) {
            return back()->with('error', 'Hasil peserta ini sudah final.');
        }
        $v = $request->input('result');
        $new = $entry->result === $v ? null : (in_array($v, ['menang', 'kalah'], true) ? $v : null);
        $refund = 0;
        if ($new === 'kalah') {
            $refund = max(0, $entry->dp_amount - ($entry->drop->refund_mode === 'potong' ? $entry->fee * $entry->qty : 0));
        }
        $entry->update(['result' => $new, 'refund_amount' => $refund]);
        if ($entry->drop->step() < 2) {
            $entry->drop->update(['step' => 2]);
        }

        return redirect()->route('admin.war', ['drop' => $entry->war_drop_id]);
    }

    /** Tombol langkah: tutup pendaftaran → war selesai → (kirim hasil lewat sendResults). */
    public function advance(Request $request, WarDrop $drop)
    {
        $to = (int) $request->input('step', $drop->step() + 1);
        if ($to <= $drop->step() || $to > 2) {
            return back();
        }
        $drop->update(['step' => $to]);
        Activity::log('Drop **'.$drop->name.'**: '.($to === 1 ? 'pendaftaran ditutup, mulai war' : 'war selesai, input hasil'));

        return redirect()->route('admin.war', ['drop' => $drop->id])->with('flash', $to === 1 ? 'Pendaftaran ditutup. Selamat war!' : 'Sekarang isi hasil menang / kalah tiap peserta.');
    }

    public function sendResults(WarDrop $drop)
    {
        $n = 0;
        foreach ($drop->entries()->with('user')->whereNotNull('result')->whereNull('order_id')->get() as $e) {
            if ($e->result === 'menang') {
                if (! $e->settle_deadline) {
                    $e->update(['settle_deadline' => now()->addHours($drop->settle_hours)]);
                }
                if ($e->settle_proof) {
                    continue;
                }
                Notify::send($e->user, 'Selamat, kamu dapat!', [
                    'Kami berhasil membeli '.$drop->name.' ('.$e->variant_main.' × '.$e->qty.') untukmu. Lunasi sisanya supaya barang kami proses:',
                ], 'Lunasi sekarang', $e->payUrl(), null, [
                    'tag' => 'MENANG WAR', 'c' => 'green', 'subject' => 'Selamat, kamu menang war '.$drop->name.'!',
                    'pre' => 'Lunasi sisa '.(100 - $drop->dp_pct).'% dalam '.$drop->settle_hours.' jam supaya slotmu aman.',
                    'rows' => [['DP '.$drop->dp_pct.'% (sudah dibayar)', rp($e->dp_amount)], ['Sisa '.(100 - $drop->dp_pct).'%', rp($e->remaining()), true], ['Batas pelunasan', tgl($e->settle_deadline, 'j M H:i').' WIB']],
                    'note' => 'Lewat batas waktu, DP hangus sesuai ketentuan.',
                ]);
            } else {
                Notify::send($e->user, 'Kali ini belum beruntung', [
                    'Maaf, stok habis sebelum giliranmu. DP kamu kami kembalikan ke rekening refund'.($e->user->refund_account ? ' ('.$e->user->refund_account.')' : '').':',
                ], 'Lihat riwayat refund', route('history', ['tab' => 'refund']), null, [
                    'tag' => 'HASIL WAR', 'c' => 'gray', 'subject' => 'Hasil war '.$drop->name.': belum beruntung', 'pre' => 'DP kamu kami kembalikan.',
                    'rows' => array_merge([['DP dibayar', rp($e->dp_amount)]], $e->dp_amount > $e->refund_amount ? [['Fee war', '− '.rp($e->dp_amount - $e->refund_amount)]] : [], [['Dikembalikan', rp($e->refund_amount), true]]),
                ]);
            }
            $n++;
        }
        $drop->update(['results_sent_at' => now()]);
        Activity::log('Hasil war **'.$drop->name.'** dikirim ke '.$n.' peserta');

        return back()->with('flash', 'Hasil & tagihan dikirim ke '.$n.' peserta.');
    }

    public function confirmSettle(WarEntry $entry)
    {
        if ($entry->result !== 'menang' || $entry->order_id) {
            return back()->with('error', 'Tidak bisa dikonfirmasi.');
        }
        $entry->load('drop', 'user');
        $order = DB::transaction(function () use ($entry) {
            $d = $entry->drop;
            $h = Harga::negara($d->country);
            $order = Order::create([
                'user_id' => $entry->user_id,
                'type' => 'war',
                'country' => $d->country,
                'stage' => Order::PAID,
                'store_price' => $entry->price * $entry->qty,
                'domestic_shipping' => 0,
                'kurs_modal' => $entry->kurs_modal,
                'kurs_jual' => $entry->kurs_jual,
                'tarif_modal' => $h['tm'],
                'tarif_jual' => $h['tj'],
                'war_fee' => $entry->fee * $entry->qty,
                'offered_at' => $entry->created_at,
                'payment_proof' => $entry->settle_proof,
                'payment_submitted_at' => $entry->settle_submitted_at,
                'paid_at' => now(),
            ]);
            $order->items()->create([
                'url' => $d->release_url,
                'name' => $d->name,
                'variant' => $entry->variant_main,
                'web_price' => $entry->price,
                'qty' => $entry->qty,
                'note' => $entry->variant_backup ? 'Cadangan: '.$entry->variant_backup : null,
            ]);
            $entry->update(['settled_at' => now(), 'order_id' => $order->id]);

            return $order->fresh();
        });
        Notify::send($entry->user, 'Pelunasan war diterima', [
            'Pelunasan '.$entry->drop->name.' sudah kami terima. Pesanan kamu: '.$order->code.'.',
            'Bukti checkout dan foto unboxing akan muncul di halaman lacak.',
        ], 'Lacak pesanan', route('track', ['no' => $order->code]));
        Activity::log('Pelunasan war **'.$entry->user->displayName().'** dikonfirmasi, pesanan '.$order->code.' dibuat');

        if (str_contains(url()->previous(), '/admin/pesanan')) {
            return redirect()->route('admin.orders', ['id' => $order->id])->with('flash', 'Lunas dikonfirmasi. Pesanan '.$order->code.' lanjut ke tahap Checkout.');
        }

        return back()->with('flash', 'Lunas dikonfirmasi. Pesanan '.$order->code.' masuk menu Pesanan di tahap Checkout.');
    }

    public function markRefunded(WarEntry $entry)
    {
        if ($entry->result !== 'kalah') {
            return back()->with('error', 'Hanya peserta yang kalah bisa direfund.');
        }
        $entry->update(['refunded_at' => now()]);

        return back()->with('flash', 'Refund DP ditandai sudah ditransfer.');
    }
}
