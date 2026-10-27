<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Order;
use App\Models\WarEntry;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** Pembayaran masuk (verifikasi transfer) & refund keluar. */
class PaymentController extends Controller
{
    public const METHOD = ['transfer' => 'Transfer', 'qris' => 'QRIS'];

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'refund' ? 'refund' : 'masuk';
        $masuk = $this->incoming();
        $refunds = $this->refunds();

        $verif = $masuk->where('st', 'verif');
        $today = $masuk->filter(fn ($r) => $r['st'] === 'lunas' && $r['done_at'] && $r['done_at']->isToday());
        $pending = $refunds->where('done', false);

        return view('admin.pembayaran', [
            'tab' => $tab,
            'masuk' => $masuk,
            'refunds' => $refunds,
            'nVerif' => $verif->count(),
            'sumVerif' => $verif->sum('amt'),
            'nRefund' => $pending->count(),
            'sumRefund' => $pending->sum('amt'),
            'sumMasuk' => $today->sum('amt'),
        ]);
    }

    /** Semua transfer masuk: menunggu verifikasi dulu, lalu yang terbaru. */
    protected function incoming(): Collection
    {
        $rows = collect();
        $since = now()->subDays(30);
        $m = fn ($v) => self::METHOD[$v ?? 'transfer'] ?? 'Transfer';

        // Tahap 1 pesanan titip
        $orders = Order::with('user')->whereNull('cancelled_at')->where(function ($q) use ($since) {
            $q->where(fn ($a) => $a->where('stage', Order::OFFERED)->whereNotNull('payment_proof'))
                ->orWhere(fn ($b) => $b->where('type', 'titip')->where('paid_at', '>=', $since))
                ->orWhere(fn ($c) => $c->where('stage', Order::OFFERED)->whereNull('payment_proof')->where('payment_rejected_at', '>=', $since));
        })->get();
        foreach ($orders as $o) {
            $st = $o->paid_at ? 'lunas' : ($o->payment_proof ? 'verif' : 'tolak');
            $rows->push([
                'kind' => 'order', 'model' => $o, 'st' => $st,
                'at' => $o->payment_submitted_at ?? $o->payment_rejected_at ?? $o->updated_at, 'done_at' => $o->paid_at,
                'nama' => $o->user?->displayName(), 'untuk' => 'Tahap 1 · harga barang', 'id' => $o->code,
                'm' => $m($o->pay_method), 'payer' => trim(($o->payer_name ?? '').($o->payer_bank ? ' · '.$o->payer_bank : '')),
                'amt' => $o->tahap1(), 'bukti' => $o->payment_proof, 'reason' => $o->payment_reject_reason,
            ]);
        }

        // DP & pelunasan war
        $entries = WarEntry::with(['user', 'drop'])->where(function ($q) use ($since) {
            $q->whereNotNull('dp_proof')->orWhereNotNull('settle_proof')->orWhere('updated_at', '>=', $since);
        })->where('created_at', '>=', now()->subDays(90))->get();
        foreach ($entries as $e) {
            $pct = $e->drop->dp_pct;
            if ($e->dp_proof || $e->dp_reject_reason) {
                $st = ! $e->dp_proof ? 'tolak' : ($e->dp_verified_at ? 'lunas' : 'verif');
                if ($st !== 'lunas' || $e->dp_verified_at >= $since) {
                    $rows->push([
                        'kind' => 'dp', 'model' => $e, 'st' => $st, 'at' => $e->created_at, 'done_at' => $e->dp_verified_at,
                        'nama' => $e->user?->displayName(), 'untuk' => 'DP war '.$pct.'%', 'id' => $e->code(),
                        'm' => $m($e->dp_method), 'payer' => '', 'amt' => $e->dp_amount, 'bukti' => $e->dp_proof, 'reason' => $e->dp_reject_reason,
                    ]);
                }
            }
            if ($e->result === 'menang' && ($e->settle_proof || $e->settle_reject_reason)) {
                $st = $e->settled_at ? 'lunas' : ($e->settle_proof ? 'verif' : 'tolak');
                if ($st !== 'lunas' || $e->settled_at >= $since) {
                    $rows->push([
                        'kind' => 'settle', 'model' => $e, 'st' => $st, 'at' => $e->settle_submitted_at ?? $e->updated_at, 'done_at' => $e->settled_at,
                        'nama' => $e->user?->displayName(), 'untuk' => 'Pelunasan war '.(100 - $pct).'%', 'id' => $e->code(),
                        'm' => $m($e->settle_method), 'payer' => trim(($e->payer_name ?? '').($e->payer_bank ? ' · '.$e->payer_bank : '')),
                        'amt' => $e->remaining(), 'bukti' => $e->settle_proof, 'reason' => $e->settle_reject_reason,
                    ]);
                }
            }
        }

        $rank = ['verif' => 0, 'tolak' => 1, 'lunas' => 2];

        return $rows->sortBy(fn ($r) => [$rank[$r['st']], -($r['at']?->timestamp ?? 0)])->values();
    }

    /** Refund ke penitip: DP war yang kalah + refund pesanan. */
    protected function refunds(): Collection
    {
        $rows = collect();
        foreach (WarEntry::with(['user', 'drop'])->where('result', 'kalah')->where(fn ($q) => $q->whereNull('refunded_at')->orWhere('refunded_at', '>=', now()->subDays(30)))->get() as $e) {
            $fee = $e->dp_amount - $e->refund_amount;
            $rows->push([
                'kind' => 'war', 'model' => $e, 'done' => (bool) $e->refunded_at, 'at' => $e->refunded_at ?? $e->updated_at,
                'nama' => $e->user?->displayName(), 'user' => $e->user, 'alasan' => 'Kalah war', 'id' => $e->code(),
                'rinci' => 'DP '.rp($e->dp_amount).($fee > 0 ? ' − fee '.rp($fee) : ' · penuh'), 'amt' => $e->refund_amount, 'proof' => $e->refund_proof,
            ]);
        }
        foreach (Order::with('user')->where('refund_amount', '>', 0)->where(fn ($q) => $q->whereNull('refunded_at')->orWhere('refunded_at', '>=', now()->subDays(30)))->get() as $o) {
            $rows->push([
                'kind' => 'order', 'model' => $o, 'done' => (bool) $o->refunded_at, 'at' => $o->refunded_at ?? $o->refund_requested_at ?? $o->updated_at,
                'nama' => $o->user?->displayName(), 'user' => $o->user, 'alasan' => $o->refund_reason ?: 'Refund pesanan', 'id' => $o->code,
                'rinci' => $o->cancelled_at ? 'Pesanan dibatalkan' : $o->title(), 'amt' => $o->refund_amount, 'proof' => $o->refund_proof,
            ]);
        }

        return $rows->sortBy(fn ($r) => [$r['done'] ? 1 : 0, $r['done'] ? -($r['at']?->timestamp ?? 0) : ($r['at']?->timestamp ?? 0)])->values();
    }

    /* ---------------- Aksi ---------------- */

    public function verifyDp(WarEntry $entry)
    {
        if (! $entry->dp_proof || $entry->dp_verified_at) {
            return back()->with('error', 'DP ini tidak sedang menunggu verifikasi.');
        }
        $entry->update(['dp_verified_at' => now(), 'dp_reject_reason' => null]);
        Activity::log('menandai DP **'.$entry->code().'** masuk '.rp($entry->dp_amount));

        return back()->with('flash', 'DP '.$entry->code().' ditandai masuk.');
    }

    public function rejectDp(Request $request, WarEntry $entry)
    {
        $reason = $this->reason($request);
        if (! $entry->dp_proof || $entry->dp_verified_at) {
            return back()->with('error', 'DP ini tidak bisa ditolak.');
        }
        $entry->update(['dp_proof' => null, 'dp_reject_reason' => $reason]);
        Notify::send($entry->user, 'Bukti DP belum cocok', [
            'Bukti DP war '.$entry->drop->name.' belum bisa kami konfirmasi: '.$reason.'.',
            'Upload ulang bukti DP '.rp($entry->dp_amount).' supaya slot antrian #'.$entry->queue_no.' tetap aman.',
        ], 'Upload ulang bukti', $entry->payUrl(), null, ['tag' => 'PEMBAYARAN DITOLAK', 'c' => 'red']);
        Activity::log('menolak bukti DP **'.$entry->code().'**: '.$reason);

        return back()->with('flash', 'Bukti DP ditolak. Penitip diminta upload ulang.');
    }

    public function rejectSettle(Request $request, WarEntry $entry)
    {
        $reason = $this->reason($request);
        if (! $entry->settle_proof || $entry->order_id) {
            return back()->with('error', 'Pelunasan ini tidak bisa ditolak.');
        }
        $entry->update(['settle_proof' => null, 'settle_submitted_at' => null, 'settle_reject_reason' => $reason]);
        Notify::send($entry->user, 'Bukti pelunasan belum cocok', [
            'Bukti pelunasan war '.$entry->drop->name.' belum bisa kami konfirmasi: '.$reason.'.',
            'Upload ulang bukti transfer '.rp($entry->remaining()).' sebelum '.tgl($entry->settle_deadline, 'j M H:i').' WIB.',
        ], 'Upload ulang bukti', $entry->payUrl(), null, ['tag' => 'PEMBAYARAN DITOLAK', 'c' => 'red']);
        Activity::log('menolak bukti pelunasan **'.$entry->code().'**: '.$reason);

        return back()->with('flash', 'Bukti pelunasan ditolak. Penitip diminta upload ulang.');
    }

    /** Tandai refund sudah ditransfer (bukti opsional). */
    public function refundSent(Request $request, string $kind, int $id)
    {
        $request->validate(['proof' => 'nullable|image|max:5120']);
        $model = $kind === 'war' ? WarEntry::with('user', 'drop')->findOrFail($id) : Order::with('user')->findOrFail($id);
        if ($model->refunded_at || ($kind === 'war' ? $model->result !== 'kalah' : $model->refund_amount <= 0)) {
            return back()->with('error', 'Refund ini sudah selesai atau tidak ada.');
        }
        $data = ['refunded_at' => now()];
        if ($request->hasFile('proof')) {
            $data['refund_proof'] = $request->file('proof')->store('refund', 'uploads');
        }
        $model->update($data);
        $code = $kind === 'war' ? $model->code() : $model->code;
        Notify::send($model->user, 'Refund '.rp($model->refund_amount).' sudah dikirim', [
            'Refund untuk '.$code.' sudah kami transfer ke '.($model->user->refund_account ?: 'rekening refund kamu').'.',
            'Detailnya tercatat di Riwayat pembayaran.',
        ], 'Lihat riwayat', route('history'), null, ['tag' => 'REFUND', 'c' => 'green', 'rows' => [
            ['Untuk', $code], ['Dikirim', rp($model->refund_amount), true],
        ]]);
        Activity::log('mengirim refund **'.$code.'** '.rp($model->refund_amount));

        return back()->with('flash', 'Refund '.$code.' ditandai sudah ditransfer. Penitip dikabari.');
    }

    protected function reason(Request $request): string
    {
        $d = $request->validate(['reason' => 'required|string|max:180'], ['reason.required' => 'Tulis alasan penolakan supaya penitip tahu apa yang salah.']);

        return trim($d['reason']);
    }
}
