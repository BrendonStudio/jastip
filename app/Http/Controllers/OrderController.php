<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Order;
use App\Models\User;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        return view('pages.titip', ['u' => $request->user()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'country' => 'required|in:jp,kr',
            'items' => 'required|array|min:1|max:20',
            'items.*.url' => 'nullable|url|max:1000',
            'items.*.name' => 'required|string|max:190',
            'items.*.variant' => 'nullable|string|max:190',
            'items.*.web_price' => 'required|numeric|min:0.01',
            'items.*.qty' => 'nullable|integer|min:1|max:99',
            'items.*.note' => 'nullable|string|max:1000',
            'items.*.screenshot' => 'nullable|image|max:5120',
            'proof' => 'required|image|max:5120',
        ], [
            'items.*.web_price.required' => 'Isi harga di toko untuk setiap barang.',
            'proof.required' => 'Upload bukti transfer pembayaran dulu.',
            'proof.image' => 'Bukti transfer harus berupa gambar.',
            'items.*.name.required' => 'Nama produk wajib diisi untuk setiap barang.',
            'items.*.url.url' => 'Link produk harus berupa alamat web lengkap (https://…).',
            'items.*.screenshot.image' => 'Screenshot harus berupa gambar.',
            'items.*.screenshot.max' => 'Ukuran screenshot maksimal 5 MB.',
        ]);

        $user = $request->user();
        $order = DB::transaction(function () use ($data, $user, $request) {
            $h = \App\Support\Harga::negara($data['country']);
            $total = collect($data['items'])->sum(fn ($it) => (float) $it['web_price'] * (int) ($it['qty'] ?? 1));
            // Penitip langsung bayar tahap 1: harga barang × kurs jastip (kurs dikunci sekarang)
            $order = Order::create([
                'user_id' => $user->id, 'type' => 'titip', 'country' => $data['country'], 'stage' => Order::OFFERED,
                'store_price' => $total, 'domestic_shipping' => 0,
                'kurs_modal' => $h['km'], 'kurs_jual' => $h['kj'], 'tarif_modal' => $h['tm'], 'tarif_jual' => $h['tj'],
                'offered_at' => now(),
                'payment_proof' => $request->file('proof')->store('pembayaran', 'uploads'),
                'payment_submitted_at' => now(),
            ]);
            foreach (array_values($data['items']) as $i => $it) {
                $shot = null;
                $file = $request->file("items.$i.screenshot");
                if ($file) {
                    $shot = $file->store('screenshot', 'uploads');
                }
                $order->items()->create([
                    'url' => $it['url'] ?? null,
                    'name' => $it['name'],
                    'variant' => $it['variant'] ?? null,
                    'web_price' => $it['web_price'] ?? null,
                    'qty' => $it['qty'] ?? 1,
                    'note' => $it['note'] ?? null,
                    'screenshot' => $shot,
                ]);
            }

            return $order->fresh('items');
        });

        Activity::log('**'.$user->displayName().'** mengirim titip baru dari '.$order->neg()['nama'].' + bukti bayar '.rp($order->tahap1()).' ('.$order->code.')');
        Notify::send($user, 'Titipan & pembayaran diterima', [
            'Titipan '.$order->code.' ('.$order->title().') sudah kami terima.',
            'Pembayaran '.rp($order->tahap1()).' ('.$order->neg()['sym'].angka($order->foreignTotal()).' × kurs '.rpk($order->kurs_jual).') sedang dicek admin.',
            'Setelah stok dicek, kami checkout di toko dan mengirim bukti checkout ke kamu.',
        ], 'Lacak pesanan', route('track', ['no' => $order->code]));
        foreach (User::notifyStaff() as $admin) {
            Notify::send($admin, 'Titip baru + bukti bayar '.$order->code, [$user->displayName().' · '.$order->title().' · '.rp($order->tahap1())], 'Buka di admin', route('admin.orders', ['id' => $order->id]));
        }

        return redirect()->route('track', ['no' => $order->code])->with('flash', 'Titipan & bukti bayar terkirim! Admin akan cek stok dan pembayaran.');
    }

    public function track(Request $request)
    {
        $user = $request->user();
        $no = trim((string) $request->query('no', ''));
        $q = $user->is_admin ? Order::query() : $user->orders();
        $q->with(['items', 'batch', 'user']);

        if ($no !== '') {
            $code = strtoupper(ltrim($no, '#'));
            $order = (clone $q)->where('code', $code)->first();
            if (! $order && ctype_digit($code)) {
                $order = (clone $q)->whereKey((int) $code)->first();
            }
            if (! $order) {
                return redirect()->route('track')->with('error', 'Pesanan '.$no.' tidak ditemukan di akunmu.');
            }
        } else {
            $order = (clone $q)->where('user_id', $user->id)->where('stage', '<', Order::DONE)->latest()->first()
                ?? (clone $q)->where('user_id', $user->id)->latest()->first();
        }

        $others = $user->orders()->with('items')->latest()->take(8)->get();

        return view('pages.lacak', ['order' => $order, 'others' => $others, 'no' => $no]);
    }

    public function pay(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        if ($order->stage !== Order::OFFERED) {
            return back()->with('error', 'Pesanan ini tidak sedang menunggu pembayaran.');
        }
        if ($order->cancelled_at || $order->offerExpired()) {
            return redirect()->route('pay', $order)->with('error', 'Penawaran ini sudah tidak berlaku.');
        }
        $d = PayController::validatePay($request);
        $order->update([
            'payment_proof' => $d['proof'], 'pay_method' => $d['pay_method'],
            'payer_name' => $d['payer_name'], 'payer_bank' => $d['payer_bank'],
            'payment_submitted_at' => now(), 'payment_rejected_at' => null,
        ]);
        Activity::log('**'.$order->user->displayName().'** mengirim bukti transfer tahap 1 untuk '.$order->code);
        foreach (User::notifyStaff() as $admin) {
            Notify::send($admin, 'Bukti transfer '.$order->code, [$order->user->displayName().' · '.rp($order->tahap1())], 'Cek di Pembayaran', route('admin.payments'));
        }

        return redirect()->route('pay', $order)->with('flash', 'Bukti transfer terkirim. Admin akan konfirmasi secepatnya.');
    }

    /** Penitip lapor sudah checkout ongkir & packing di Shopee. */
    public function reportShopee(Request $request, Order $order)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        if ($order->stage !== Order::SHOPEE) {
            return back()->with('error', 'Pesanan ini tidak sedang menunggu checkout Shopee.');
        }
        $data = $request->validate([
            'shopee_order_no' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[A-Za-z0-9\-]+$/'],
        ], [
            'shopee_order_no.required' => 'Isi nomor pesanan Shopee kamu.',
            'shopee_order_no.min' => 'Nomor pesanan Shopee terlalu pendek, cek lagi di aplikasi Shopee.',
            'shopee_order_no.regex' => 'Nomor pesanan Shopee hanya berisi huruf dan angka.',
        ]);
        $no = strtoupper(trim($data['shopee_order_no']));
        $order->update(['shopee_order_no' => $no, 'shopee_reported_at' => now()]);
        Activity::log('**'.$order->user->displayName().'** lapor sudah checkout Shopee '.$order->code.' (No. '.$no.')');
        foreach (User::notifyStaff() as $admin) {
            Notify::send($admin, 'Lapor checkout Shopee '.$order->code, [
                $order->user->displayName().' (@'.$order->user->shopee_username.') lapor sudah checkout.',
                'No. pesanan Shopee: '.$no.' · tagihan '.rp($order->tahap2()).'. Cek di Seller Centre lalu tandai selesai.',
            ], 'Buka di admin', route('admin.orders', ['id' => $order->id]));
        }

        return redirect()->route('track', ['no' => $order->code])->with('flash', 'Terima kasih! Admin akan cek pesanan Shopee kamu lalu mengirim barangnya.');
    }
}
