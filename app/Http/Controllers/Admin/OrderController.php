<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Batch;
use App\Models\Order;
use App\Models\WarEntry;
use App\Support\Alur;
use App\Support\Harga;
use App\Support\Notify;
use Illuminate\Http\Request;

/** Kelola pesanan per tahap: penawaran → pembayaran → checkout → foto → jadwal → tiba → Shopee → selesai. */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $s = trim((string) $request->query('q', ''));
        $tab = $request->query('tab');
        // Cari kode WAR-xxxx otomatis pindah ke tab Pendaftar war
        if (! in_array($tab, ['titip', 'war'], true)) {
            $tab = ($request->filled('war') || preg_match('/^#?war/i', $s)) ? 'war' : 'titip';
        }
        $groups = $tab === 'war' ? Alur::WAR : Alur::POS;
        $pos = $request->filled('pos') ? max(0, min(count($groups) - 1, (int) $request->query('pos'))) : null;

        // ---- Titip: pesanan aktif + yang selesai 30 hari terakhir (semua kalau mencari) ----
        $oq = Order::with(['items', 'user', 'batch', 'warEntry.drop']);
        if ($s !== '') {
            $code = ltrim($s, '#');
            $oq->where(function ($w) use ($s, $code) {
                $w->where('code', 'like', "%$code%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                    ->orWhereHas('items', fn ($i) => $i->where('name', 'like', "%$s%"));
            });
        } else {
            $oq->where(fn ($w) => $w->where('stage', '<', Order::DONE)->orWhere('completed_at', '>=', now()->subDays(30)));
        }
        $orders = $oq->latest('updated_at')->take(400)->get();
        $rank = ['need' => 0, 'wait' => 1, 'done' => 2];
        $orders = $orders->sortBy(fn (Order $o) => [$rank[Alur::step($o)['kind']], $o->updated_at?->timestamp])->values();

        // ---- Pendaftar war ----
        $wq = WarEntry::with(['drop', 'user', 'order']);
        if ($s !== '') {
            $num = (int) preg_replace('/\D/', '', $s);
            $wq->where(function ($w) use ($s, $num) {
                if ($num) {
                    $w->orWhere('id', $num);
                }
                $w->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                    ->orWhereHas('drop', fn ($d) => $d->where('name', 'like', "%$s%"));
            });
        } else {
            $wq->where(fn ($w) => $w->where(fn ($a) => $a->whereNull('order_id')->whereNull('refunded_at'))->orWhere('updated_at', '>=', now()->subDays(14)));
        }
        $wars = $wq->latest()->take(300)->get()
            ->sortBy(fn (WarEntry $e) => [$rank[Alur::warStep($e)['kind']], $e->updated_at?->timestamp])->values();

        $src = $tab === 'war' ? $wars : $orders;
        $groupOf = $tab === 'war' ? fn ($e) => Alur::warIndex($e) : fn ($o) => Alur::pos($o);
        $counts = [];
        foreach ($groups as $i => $g) {
            $counts[$i] = $src->filter(fn ($x) => $groupOf($x) === $i)->count();
        }
        $list = $pos === null ? $src : $src->filter(fn ($x) => $groupOf($x) === $pos)->values();

        $order = null;
        $entry = null;
        if ($tab === 'war' && $request->filled('war')) {
            $entry = WarEntry::with(['drop', 'user', 'order'])->find($request->query('war'));
        } elseif ($tab === 'titip' && $request->filled('id')) {
            $order = Order::with(['items', 'user', 'batch', 'warEntry.drop'])->find($request->query('id'));
        }

        $batches = $order
            ? Batch::where('status', 0)->where('country', $order->country)->with('orders')->orderBy('ship_date')->get()
            : collect();
        $events = $order
            ? Activity::where('message', 'like', '%'.$order->code.'%')->latest('id')->take(5)->get()
            : collect();

        return view('admin.pesanan', [
            'tab' => $tab,
            'pos' => $pos,
            'q' => $s,
            'groups' => $groups,
            'counts' => $counts,
            'list' => $list,
            'nAll' => $src->count(),
            'nTitip' => $orders->count(),
            'nWar' => $wars->count(),
            'order' => $order,
            'entry' => $entry,
            'events' => $events,
            'batches' => $batches,
            'harga' => $order ? Harga::negara($order->country) : null,
            'packs' => Harga::packs(),
        ]);
    }

    protected function go(Order $o, string $msg)
    {
        return redirect()->route('admin.orders', ['id' => $o->id])->with('flash', $msg);
    }

    protected function track(Order $o): string
    {
        return route('track', ['no' => $o->code]);
    }

    public function offer(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::REQUEST, 422);
        $data = $request->validate([
            'store_price' => 'required|numeric|min:0',
            'domestic_shipping' => 'nullable|numeric|min:0',
        ]);
        $h = Harga::negara($order->country);
        $order->update([
            'store_price' => $data['store_price'],
            'domestic_shipping' => $data['domestic_shipping'] ?? 0,
            'kurs_modal' => $h['km'], 'kurs_jual' => $h['kj'],
            'tarif_modal' => $h['tm'], 'tarif_jual' => $h['tj'],
            'offered_at' => now(),
            'offer_expires_at' => now()->addHours(config('jastip.kurs_dikunci_jam')),
            'stage' => Order::OFFERED,
        ]);
        $sym = $order->neg()['sym'];
        Notify::send($order->user, 'Penawaranmu sudah siap', [
            'Barang yang kamu titip tersedia. Ini rincian harganya dengan kurs hari ini:',
        ], 'Lihat & bayar penawaran', $order->payUrl(), null, [
            'tag' => 'PENAWARAN SIAP', 'c' => 'red',
            'subject' => 'Penawaran '.$order->code.' siap · '.rp($order->tahap1()),
            'pre' => 'Kurs dikunci '.config('jastip.kurs_dikunci_jam').' jam. Setujui dan bayar sebelum hangus.',
            'rows' => array_merge(
                $order->items->map(fn ($i) => [trim($i->name.($i->variant ? ' · '.$i->variant : '')).($i->qty > 1 ? ' ×'.$i->qty : ''), $i->web_price ? $sym.angka($i->web_price * $i->qty) : '—'])->all(),
                $order->domestic_shipping > 0 ? [['Ongkir dalam '.$order->neg()['nama'], $sym.angka($order->domestic_shipping)]] : [],
                [['Kurs jastip', $sym.'1 = '.rpk($order->kurs_jual)], ['Bayar sekarang', rp($order->tahap1()), true]],
            ),
            'note' => 'Kurs dikunci sampai '.tgl($order->offer_expires_at, 'j M H:i').' WIB. Ongkir & packing dibayar nanti lewat Shopee.',
        ]);
        Activity::log('Penawaran '.$order->code.' dikirim ke **'.$order->user->displayName().'**');

        return $this->go($order, 'Penawaran terkirim ke email penitip.');
    }

    public function markPaid(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::OFFERED, 422);
        $order->update(['paid_at' => now(), 'stage' => Order::PAID, 'payment_reject_reason' => null]);
        Notify::send($order->user, 'Pembayaran diterima', [
            'Pembayaran tahap 1 untuk '.$order->code.' sudah kami terima. Kami segera checkout di toko resminya dan mengirim bukti checkout ke emailmu.',
        ], 'Lacak pesanan', $this->track($order), null, ['tag' => 'PEMBAYARAN LUNAS', 'c' => 'green', 'rows' => [
            ['Pesanan', $order->code.' · '.$order->title()], ['Dibayar', rp($order->tahap1()), true],
        ]]);
        Activity::log('menandai lunas transfer **'.$order->code.'** '.rp($order->tahap1()));

        return $this->afterPay($request, $order, 'Ditandai lunas. Lanjut checkout di toko.');
    }

    public function rejectPayment(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::OFFERED, 422);
        $request->validate(['reason' => 'nullable|string|max:180']);
        $reason = trim((string) $request->input('reason', '')) ?: 'nominal atau rekening tujuan belum sesuai';
        $order->update(['payment_proof' => null, 'payment_submitted_at' => null, 'payment_reject_reason' => $reason, 'payment_rejected_at' => now()]);
        Notify::send($order->user, 'Bukti transfer belum cocok', [
            'Bukti transfer untuk '.$order->code.' belum bisa kami konfirmasi: '.$reason.'.',
            'Upload ulang bukti transfer '.rp($order->tahap1()).' atau hubungi admin.',
        ], 'Upload ulang bukti', $order->payUrl(), null, ['tag' => 'PEMBAYARAN DITOLAK', 'c' => 'red']);
        Activity::log('menolak bukti transfer **'.$order->code.'**: '.$reason);

        return $this->afterPay($request, $order, 'Penitip diminta upload ulang bukti transfer.');
    }

    /** Catat refund untuk pesanan (kelebihan bayar, stok habis → batal, dll.). */
    public function refund(Request $request, Order $order)
    {
        $d = $request->validate([
            'amount' => 'required|integer|min:1000|max:'.max(1000, $order->tahap1() + 1000000),
            'reason' => 'required|string|max:120',
            'cancel' => 'nullable|boolean',
        ], ['amount.required' => 'Isi jumlah refund.', 'reason.required' => 'Pilih / tulis alasan refund.']);
        if ($order->refund_amount > 0 && ! $order->refunded_at) {
            return back()->with('error', 'Masih ada refund yang belum dikirim untuk pesanan ini.');
        }
        $data = ['refund_amount' => (int) $d['amount'], 'refund_reason' => $d['reason'], 'refund_requested_at' => now(), 'refunded_at' => null, 'refund_proof' => null];
        if (! empty($d['cancel'])) {
            $data += ['cancelled_at' => now(), 'cancel_reason' => $d['reason']];
        }
        $order->update($data);
        Activity::log('mencatat refund **'.$order->code.'** '.rp($d['amount']).' ('.$d['reason'].')'.(! empty($d['cancel']) ? ', pesanan dibatalkan' : ''));
        Notify::send($order->user, ! empty($d['cancel']) ? 'Pesanan '.$order->code.' dibatalkan' : 'Refund untuk '.$order->code, [
            (! empty($d['cancel']) ? 'Maaf, pesanan '.$order->title().' kami batalkan: ' : 'Ada refund untuk pesananmu: ').$d['reason'].'.',
            'Dana '.rp($d['amount']).' akan kami kembalikan ke '.($order->user->refund_account ?: 'rekening refund di akunmu (isi dulu di Akun saya)').'.',
        ], 'Lihat riwayat', route('history'), null, ['tag' => 'REFUND', 'c' => 'gray']);

        return redirect()->route('admin.payments', ['tab' => 'refund'])->with('flash', 'Refund '.rp($d['amount']).' dicatat. Kirim lalu tandai sudah ditransfer di sini.');
    }

    /** Setelah aksi dari halaman Pembayaran / HP, kembali ke halaman asal. */
    protected function afterPay(Request $request, Order $order, string $msg)
    {
        if (in_array($request->input('kembali'), ['bayar', 'hp'], true)) {
            return back()->with('flash', $order->code.': '.$msg);
        }

        return $this->go($order, $msg);
    }

    public function checkout(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::PAID, 422);
        $data = $request->validate([
            'store_order_no' => 'required|string|max:120',
            'local_resi' => 'nullable|string|max:80',
            'proof' => 'nullable|image|max:5120',
        ], ['store_order_no.required' => 'Isi nomor order dari toko.']);
        $order->update([
            'store_order_no' => $data['store_order_no'],
            'local_resi' => $data['local_resi'] ?? null,
            'checkout_proof' => $request->file('proof')?->store('checkout', 'uploads') ?? $order->checkout_proof,
            'checked_out_at' => now(),
            'stage' => Order::CHECKED_OUT,
        ]);
        Notify::send($order->user, 'Barangmu sudah kami beli', [
            'Pembayaranmu sudah kami terima dan barang sudah dibeli di toko resminya.',
        ], 'Lihat bukti checkout', $this->track($order), null, [
            'tag' => 'SUDAH DIBELI', 'c' => 'green', 'subject' => 'Sudah kami checkout! Bukti pembelian '.$order->code,
            'pre' => 'Barangmu sedang dikirim toko ke warehouse kami.',
            'rows' => [['Dibayar', rp($order->tahap1())], ['No. order toko', $order->store_order_no], ['Tujuan', 'Warehouse '.$order->neg()['nama']]],
        ]);
        Activity::log('Checkout toko '.$order->code.' selesai');

        return $this->go($order, 'Bukti checkout terkirim ke penitip.');
    }

    public function photos(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::CHECKED_OUT, 422);
        $action = $request->input('action', 'simpan');

        if ($action === 'ulang') {
            $order->update(['photos' => null, 'wh_submitted_at' => null]);

            return $this->go($order, 'Foto dihapus. Minta warehouse kirim foto ulang, lalu upload lagi di sini.');
        }

        $data = $request->validate([
            'photos' => 'nullable|array|max:8',
            'photos.*' => 'image|max:6144',
            'photocard_note' => 'nullable|string|max:190',
            'weight' => $action === 'setujui' ? 'required|numeric|min:0.01|max:500' : 'nullable|numeric|min:0|max:500',
            'condition_note' => 'nullable|string|max:190',
            'pack_type' => $action === 'setujui' ? 'required|in:standar,bubble,kayu' : 'nullable|in:standar,bubble,kayu',
        ], [
            'weight.required' => 'Isi berat timbang sebelum menyetujui.',
            'pack_type.required' => 'Pilih jenis packing.',
        ]);

        $paths = $order->photoList();
        foreach ($request->file('photos', []) as $f) {
            $paths[] = $f->store('warehouse', 'uploads');
        }
        $upd = [
            'photos' => array_values(array_slice($paths, -8)),
            'photocard_note' => $data['photocard_note'] ?? $order->photocard_note,
            'weight' => $data['weight'] ?? $order->weight,
            'condition_note' => $data['condition_note'] ?? $order->condition_note,
        ];
        if (! empty($data['pack_type'])) {
            $pk = Harga::packs()[$data['pack_type']];
            $upd += ['pack_type' => $pk['id'], 'pack_modal' => $pk['modal'], 'pack_jual' => $pk['jual']];
        }
        $order->update($upd);

        if ($action !== 'setujui') {
            return $this->go($order, 'Foto & data warehouse disimpan (belum terlihat penitip).');
        }
        if (! $order->photoList()) {
            return redirect()->route('admin.orders', ['id' => $order->id])->with('error', 'Upload minimal 1 foto sebelum menyetujui.');
        }

        $order->update(['warehouse_approved_at' => now(), 'stage' => Order::WAREHOUSE]);
        Notify::send($order->user, 'Sudah tiba di gudang '.$order->neg()['nama'], [
            'Paketmu sudah kami buka, foto, dan timbang. Ini hasilnya:',
        ], 'Lihat semua foto', $this->track($order), null, [
            'tag' => 'DI GUDANG', 'c' => 'purple', 'subject' => 'Barangmu sudah di gudang · foto unboxing '.$order->code,
            'pre' => 'Lihat photocard yang kamu dapat dan berat paketnya.',
            'rows' => [['Photocard / POB', $order->photocard_note ?: '—'], ['Berat timbang', angka($order->weight).' kg'], ['Kondisi', $order->condition_note ?: 'Baik']],
            'photos' => array_map(fn ($p) => upload_url($p), array_slice($order->photoList(), 0, 4)),
        ]);
        Activity::log('menyetujui foto unboxing **'.$order->code.'**');

        return $this->go($order, 'Foto disetujui dan tampil ke penitip.');
    }

    public function schedule(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::WAREHOUSE, 422);
        $data = $request->validate(['batch_id' => 'required|exists:batches,id'], ['batch_id.required' => 'Pilih batch.']);
        $batch = Batch::findOrFail($data['batch_id']);
        abort_unless($batch->status === 0, 422);
        $order->update(['batch_id' => $batch->id, 'scheduled_at' => now(), 'stage' => Order::SCHEDULED]);
        Notify::send($order->user, 'Paketmu sudah dijadwalkan', [
            'Paketmu masuk batch pengiriman berikutnya ke Indonesia.',
        ], 'Lacak pesanan', $this->track($order), null, [
            'tag' => 'DIJADWALKAN', 'c' => 'blue', 'subject' => $order->code.' ikut pengiriman '.tgl($batch->ship_date),
            'rows' => [['Batch', tgl($batch->ship_date).' · '.strtolower($batch->routeLabel())], ['Estimasi tiba', $batch->eta_date ? tgl($batch->eta_date) : 'dikabari saat dikirim']],
        ]);

        return $this->go($order, 'Pesanan masuk '.$batch->label().'.');
    }

    public function arrived(Order $order)
    {
        abort_unless($order->stage === Order::SCHEDULED, 422);
        $order->update(['arrived_at' => now(), 'stage' => Order::ARRIVED]);
        Notify::send($order->user, 'Barang tiba di Indonesia', [
            'Pesanan '.$order->code.' sudah tiba. Kami siapkan link pembayaran ongkir & packing di Shopee.',
        ], 'Lacak pesanan', $this->track($order));

        return $this->go($order, 'Ditandai tiba di Indonesia.');
    }

    public function shopee(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::ARRIVED, 422);
        $data = $request->validate(['shopee_link' => 'required|url|max:1000'], ['shopee_link.required' => 'Tempel link produk Shopee.']);
        $order->update(['shopee_link' => $data['shopee_link'], 'shopee_sent_at' => now(), 'stage' => Order::SHOPEE]);
        Notify::send($order->user, 'Barangmu sudah di Indonesia', [
            'Tinggal satu langkah lagi. Checkout produk khusus ini di Shopee untuk membayar ongkir dan packing, lalu laporkan nomor pesanan Shopee-nya di halaman lacak.',
        ], 'Checkout di Shopee', $order->shopee_link, null, [
            'tag' => 'BAYAR DI SHOPEE', 'c' => 'orange', 'subject' => 'Barang tiba! Bayar ongkir & packing '.$order->code.' di Shopee',
            'pre' => 'Checkout lewat link khusus ini supaya barang segera dikirim.',
            'rows' => [['Ongkir · '.$order->billedKg().' kg × '.rp($order->tarif_jual), rp($order->ongkir())], ['Packing · '.(\App\Support\Harga::PACK_LABEL[$order->pack_type] ?? 'packing'), rp($order->pack_jual)], ['Total di Shopee', rp($order->tahap2()), true]],
            'note' => 'Checkout pakai akun Shopee @'.$order->user->shopee_username.' supaya mudah kami cocokkan.',
        ]);
        Activity::log('Link Shopee '.$order->code.' dikirim ke **'.$order->user->displayName().'**');

        if ($request->input('kembali') === 'jadwal') {
            return back()->with('flash', 'Link Shopee '.$order->code.' terkirim ke '.$order->user->displayName().'.');
        }

        return $this->go($order, 'Link Shopee terkirim ke penitip.');
    }

    public function complete(Request $request, Order $order)
    {
        abort_unless($order->stage === Order::SHOPEE, 422);
        $request->validate(['shopee_order_no' => ['nullable', 'max:40', 'regex:/^[A-Za-z0-9\-]+$/']], [], ['shopee_order_no' => 'nomor pesanan Shopee']);
        $no = strtoupper(trim((string) $request->input('shopee_order_no', '')));
        $order->update([
            'completed_at' => now(),
            'stage' => Order::DONE,
            'shopee_order_no' => $no !== '' ? $no : $order->shopee_order_no,
        ]);
        Activity::log('**'.$order->user->displayName().'** checkout di Shopee untuk pesanan '.$order->code);
        Notify::send($order->user, 'Pesanan selesai', ['Terima kasih! Pesanan '.$order->code.' sudah dikirim ke alamatmu. Resi ada di aplikasi Shopee.'], 'Titip lagi', route('order.create'));

        if ($request->input('kembali') === 'jadwal') {
            return back()->with('flash', 'Pesanan '.$order->code.' selesai.');
        }

        return $this->go($order, 'Pesanan selesai.');
    }

    public function back(Order $order)
    {
        if ($order->stage > 0) {
            $order->update(['stage' => $order->stage - 1]);
        }

        return $this->go($order, 'Pesanan dikembalikan ke tahap '.Order::ADMIN_STEPS[min($order->stage, 7)].'.');
    }
}
