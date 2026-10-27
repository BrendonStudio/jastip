<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with(['items', 'batch'])->latest()->get();
        $entries = $user->warEntries()->with('drop')->whereNull('order_id')->latest()->get();

        $aktif = collect();
        $selesai = collect();

        foreach ($entries as $e) {
            $card = $e->card() + [
                'no' => 'WAR-'.str_pad((string) $e->id, 4, '0', STR_PAD_LEFT),
                'jenis' => 'War',
                'neg' => $e->drop->neg(),
                'title' => $e->drop->name.' · '.$e->variant_main.' × '.$e->qty,
                'thumb' => upload_url($e->drop->image),
                'track' => route('war.entry', $e),
                'sort' => $e->updated_at,
            ];
            ($e->result === 'kalah' ? $selesai : $aktif)->push($card);
        }

        foreach ($orders as $o) {
            $card = $o->card() + [
                'no' => $o->code,
                'jenis' => $o->type === 'war' ? 'War' : 'Titip',
                'neg' => $o->neg(),
                'title' => $o->title(),
                'thumb' => $o->thumb(),
                'track' => route('track', ['no' => $o->code]),
                'sort' => $o->updated_at,
            ];
            ($o->stage >= \App\Models\Order::DONE || $o->cancelled_at ? $selesai : $aktif)->push($card);
        }

        // Yang perlu dibayar di atas
        $aktif = $aktif->sortBy([fn ($a, $b) => ($b['action'] ? 1 : 0) <=> ($a['action'] ? 1 : 0), fn ($a, $b) => $b['sort'] <=> $a['sort']])->values();
        $selesai = $selesai->sortByDesc('sort')->values();

        return view('pages.akun', [
            'u' => $user,
            'aktif' => $aktif,
            'selesai' => $selesai,
            'nBayar' => $aktif->filter(fn ($c) => $c['action'])->count(),
            'nProses' => $aktif->filter(fn ($c) => ! $c['action'])->count(),
            'nWar' => $aktif->where('jenis', 'War')->count(),
            'saldoRefund' => $user->refundBalance(),
        ]);
    }

    /** Riwayat pembayaran & refund penitip. */
    public function history(Request $request)
    {
        $user = $request->user();
        $tab = in_array($request->query('tab'), ['bayar', 'refund'], true) ? $request->query('tab') : 'semua';
        $M = ['transfer' => 'Transfer', 'qris' => 'QRIS'];
        $tx = collect();

        foreach ($user->orders()->with('items')->get() as $o) {
            $ref = $o->code.' · '.$o->title();
            if ($o->type === 'titip' && ($o->payment_proof || $o->paid_at)) {
                $tx->push(['jenis' => 'bayar', 'd' => $o->paid_at ?? $o->payment_submitted_at, 'l' => 'Tahap 1 · harga barang', 'ref' => $ref, 'm' => $M[$o->pay_method ?? 'transfer'] ?? 'Transfer', 'st' => $o->paid_at ? 'lunas' : 'verif', 'amt' => $o->tahap1(), 'href' => $o->payUrl()]);
            }
            if ($o->stage >= \App\Models\Order::SHOPEE && $o->tahap2() > 0) {
                $tx->push(['jenis' => 'bayar', 'd' => $o->completed_at ?? $o->shopee_sent_at, 'l' => 'Tahap 2 · ongkir & packing', 'ref' => $ref, 'm' => 'Shopee', 'st' => $o->completed_at ? 'lunas' : 'shopee', 'amt' => $o->completed_at ? $o->tahap2() : -1, 'href' => route('track', ['no' => $o->code]).'#bayar']);
            }
            if ($o->refund_amount > 0) {
                $tx->push(['jenis' => 'refund', 'd' => $o->refunded_at ?? $o->refund_requested_at, 'l' => 'Refund · '.mb_strtolower($o->refund_reason ?: 'pesanan'), 'ref' => $ref, 'm' => $o->refunded_at ? 'ke rekening refund' : '—', 'st' => $o->refunded_at ? 'selesai' : 'proses', 'amt' => $o->refund_amount, 'href' => null]);
            }
        }
        foreach ($user->warEntries()->with('drop')->get() as $e) {
            $ref = $e->code().' · '.$e->drop->name;
            if ($e->dp_proof) {
                $tx->push(['jenis' => 'bayar', 'd' => $e->created_at, 'l' => 'DP war '.$e->drop->dp_pct.'%', 'ref' => $ref, 'm' => $M[$e->dp_method ?? 'transfer'] ?? 'Transfer', 'st' => $e->dp_verified_at ? 'lunas' : 'verif', 'amt' => $e->dp_amount, 'href' => $e->payUrl()]);
            }
            if ($e->settle_proof || $e->settled_at) {
                $tx->push(['jenis' => 'bayar', 'd' => $e->settled_at ?? $e->settle_submitted_at, 'l' => 'Sisa '.(100 - $e->drop->dp_pct).'% war', 'ref' => $ref, 'm' => $M[$e->settle_method ?? 'transfer'] ?? 'Transfer', 'st' => $e->settled_at ? 'lunas' : 'verif', 'amt' => $e->remaining(), 'href' => $e->payUrl()]);
            }
            if ($e->result === 'kalah') {
                $tx->push(['jenis' => 'refund', 'd' => $e->refunded_at ?? $e->updated_at, 'l' => 'Refund DP · kalah war', 'ref' => $ref, 'm' => $e->refunded_at ? 'ke rekening refund' : '—', 'st' => $e->refunded_at ? 'selesai' : 'proses', 'amt' => $e->refund_amount, 'href' => null]);
            }
        }
        $tx = $tx->sortByDesc(fn ($t) => $t['d']?->timestamp ?? 0)->values();

        return view('pages.riwayat', [
            'tab' => $tab,
            'rows' => $tab === 'semua' ? $tx : $tx->where('jenis', $tab)->values(),
            'paid' => $tx->where('jenis', 'bayar')->where('st', 'lunas')->sum('amt'),
            'verif' => $tx->where('jenis', 'bayar')->where('st', 'verif')->sum('amt'),
            'refunded' => $tx->where('jenis', 'refund')->where('st', 'selesai')->sum('amt'),
            'refundPending' => $tx->where('jenis', 'refund')->where('st', 'proses')->sum('amt'),
            'u' => $user,
        ]);
    }

    public function toggleWarInfo(Request $request)
    {
        $u = $request->user();
        $u->update(['notify_war' => ! $u->notify_war]);

        return back()->with('flash', $u->notify_war ? 'Info war & rilisan baru akan dikirim ke emailmu.' : 'Kamu tidak akan menerima info war & rilisan baru lagi.');
    }

    /** Link "berhenti menerima info war" di email (bertanda tangan, tanpa login). */
    public function unsubscribe(User $user)
    {
        $user->update(['notify_war' => false]);

        return redirect()->route('home')->with('flash', 'Kamu tidak akan menerima email info war & rilisan baru lagi. Kabar pesananmu tetap dikirim.');
    }
}
