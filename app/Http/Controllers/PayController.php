<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\WarEntry;
use App\Support\Notify;
use Illuminate\Http\Request;

/** Halaman penawaran & pembayaran penitip (tahap 1 titip, DP & pelunasan war). */
class PayController extends Controller
{
    public const STEPS = ['penawaran', 'metode', 'upload', 'tolak'];

    public static function qris(): ?string
    {
        $p = Setting::query()->where('key', 'qris_image')->value('value');

        return $p ? upload_url($p) : null;
    }

    public function show(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id || $request->user()->is_admin, 403);
        $order->load('items', 'user');

        $state = match (true) {
            (bool) $order->cancelled_at => 'batal',
            $order->offerExpired() => 'hangus',
            $order->stage === Order::REQUEST => 'tunggu',
            $order->stage === Order::OFFERED && (bool) $order->payment_proof => 'menunggu',
            $order->stage >= Order::PAID => 'lunas',
            default => in_array($request->query('langkah'), self::STEPS, true) ? $request->query('langkah') : ($order->payment_rejected_at ? 'metode' : 'penawaran'),
        };

        return view('pages.bayar', [
            'kind' => 'order', 'o' => $order, 'state' => $state, 'metode' => $request->query('metode') === 'qris' && self::qris() ? 'qris' : 'transfer',
            'amount' => $order->tahap1(), 'deadline' => $order->offer_expires_at, 'qris' => self::qris(),
            'submitUrl' => route('order.pay', $order), 'selfUrl' => route('pay', $order),
        ]);
    }

    public function showWar(Request $request, WarEntry $entry)
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id || $request->user()->is_admin, 403);
        $entry->load('drop', 'user', 'order');
        $isDp = ! $entry->result;

        $state = match (true) {
            $isDp && $entry->dp_proof => $entry->dp_verified_at ? 'lunas' : 'menunggu',
            $entry->result === 'kalah' => 'kalah',
            (bool) $entry->order_id => 'lunas',
            $entry->result === 'menang' && (bool) $entry->settle_proof => 'menunggu',
            default => in_array($request->query('langkah'), ['penawaran', 'metode', 'upload'], true) ? $request->query('langkah') : ($entry->settle_reject_reason || $entry->dp_reject_reason ? 'metode' : 'penawaran'),
        };

        return view('pages.bayar', [
            'kind' => $isDp ? 'dp' : 'settle', 'e' => $entry, 'state' => $state, 'metode' => $request->query('metode') === 'qris' && self::qris() ? 'qris' : 'transfer',
            'amount' => $isDp ? $entry->dp_amount : $entry->remaining(), 'deadline' => $isDp ? $entry->drop->closesAt() : $entry->settle_deadline,
            'qris' => self::qris(), 'submitUrl' => route('pay.war.submit', $entry), 'selfUrl' => route('pay.war', $entry),
        ]);
    }

    /** Upload bukti DP ulang / pelunasan war. */
    public function submitWar(Request $request, WarEntry $entry)
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);
        $d = $this->validatePay($request);
        if (! $entry->result && ! $entry->dp_proof) {
            $entry->update(['dp_proof' => $d['proof'], 'dp_method' => $d['pay_method'], 'dp_reject_reason' => null]);
            Activity::log('**'.$entry->user->displayName().'** upload ulang bukti DP war **'.$entry->drop->name.'**');
        } elseif ($entry->result === 'menang' && ! $entry->settle_proof && ! $entry->order_id) {
            $entry->update([
                'settle_proof' => $d['proof'], 'settle_submitted_at' => now(), 'settle_method' => $d['pay_method'],
                'payer_name' => $d['payer_name'], 'payer_bank' => $d['payer_bank'], 'settle_reject_reason' => null,
            ]);
            Activity::log('**'.$entry->user->displayName().'** mengirim bukti pelunasan war **'.$entry->drop->name.'**');
            foreach (User::notifyStaff() as $admin) {
                Notify::send($admin, 'Bukti pelunasan war '.$entry->code(), [$entry->user->displayName().' · '.rp($entry->remaining())], 'Cek di Pembayaran', route('admin.payments'));
            }
        } else {
            return redirect()->route('pay.war', $entry)->with('error', 'Tidak ada tagihan yang menunggu bukti untuk war ini.');
        }

        return redirect()->route('pay.war', $entry)->with('flash', 'Bukti terkirim. Admin akan memverifikasi transfermu.');
    }

    /** Tolak penawaran (belum dibayar) → pesanan dibatalkan. */
    public function reject(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        if ($order->stage !== Order::OFFERED || $order->payment_proof || $order->paid_at || $order->cancelled_at) {
            return redirect()->route('pay', $order)->with('error', 'Penawaran ini tidak bisa ditolak lagi.');
        }
        $d = $request->validate(['alasan' => 'required|string|max:120'], ['alasan.required' => 'Pilih alasannya dulu.']);
        $order->update(['cancelled_at' => now(), 'cancel_reason' => 'Penawaran ditolak: '.$d['alasan']]);
        Activity::log('**'.$order->user->displayName().'** menolak penawaran '.$order->code.' ('.$d['alasan'].')');

        return redirect()->route('account')->with('flash', 'Penawaran '.$order->code.' ditolak. Tidak ada yang perlu dibayar.');
    }

    /** Penawaran hangus → minta penawaran baru dengan kurs hari ini. */
    public function renew(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        if (! $order->offerExpired()) {
            return redirect()->route('pay', $order);
        }
        $order->update(['stage' => Order::REQUEST, 'offer_expires_at' => null, 'payment_rejected_at' => null, 'payment_reject_reason' => null]);
        Activity::log('**'.$order->user->displayName().'** minta penawaran baru untuk '.$order->code);
        foreach (User::notifyStaff() as $admin) {
            Notify::send($admin, 'Minta penawaran baru '.$order->code, [$order->user->displayName().' · '.$order->title()], 'Kirim penawaran', route('admin.orders', ['id' => $order->id]));
        }

        return redirect()->route('pay', $order)->with('flash', 'Permintaan terkirim. Admin akan mengirim penawaran baru dengan kurs hari ini.');
    }

    public static function validatePay(Request $request): array
    {
        $d = $request->validate([
            'proof' => 'required|image|max:5120',
            'pay_method' => 'nullable|in:transfer,qris',
            'payer_name' => 'nullable|string|max:120',
            'payer_bank' => 'nullable|string|max:60',
        ], [
            'proof.required' => 'Upload foto / screenshot bukti pembayaran.',
            'proof.image' => 'Bukti pembayaran harus berupa gambar (JPG atau PNG).',
            'proof.max' => 'Ukuran bukti maksimal 5 MB.',
        ]);
        $d['proof'] = $request->file('proof')->store('pembayaran', 'uploads');
        $d['pay_method'] = $d['pay_method'] ?? 'transfer';

        return $d + ['payer_name' => null, 'payer_bank' => null];
    }
}
