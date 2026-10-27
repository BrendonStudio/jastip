<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Batch;
use App\Models\Order;
use App\Support\Harga;
use App\Support\Notify;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $all = Batch::withCount('orders')->with('orders')->orderByDesc('id')->take(40)->get();
        // Kartu batch: yang masih jalan (urut tanggal kirim) + 3 batch terakhir yang sudah selesai
        $cards = $all->where('status', '<', 3)->sortBy(fn ($b) => $b->ship_date?->timestamp ?? PHP_INT_MAX)->values()
            ->concat($all->where('status', 3)->take(3))->values();
        $batch = $request->filled('batch')
            ? Batch::find($request->query('batch'))
            : (Batch::where('status', 0)->orderByRaw('ship_date is null')->orderBy('ship_date')->first() ?? $all->first());
        $batch?->load('orders.items', 'orders.user');

        $pool = $batch
            ? Order::with(['items', 'user'])->where('stage', Order::WAREHOUSE)->whereNull('batch_id')->where('country', $batch->country)->orderBy('warehouse_approved_at')->get()
            : collect();

        if ($batch && ! $cards->contains('id', $batch->id)) {
            $cards->prepend($batch);
        }

        return view('admin.jadwal', ['cards' => $cards, 'batch' => $batch, 'pool' => $pool, 'h' => $batch ? Harga::negara($batch->country) : null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['country' => 'required|in:jp,kr']);
        $b = Batch::create([
            'country' => $data['country'],
            'route' => 'udara',
            'cutoff_date' => today()->addDays(5),
            'ship_date' => today()->addDays(7),
            'capacity_kg' => 30,
        ]);

        return redirect()->route('admin.batches', ['batch' => $b->id])->with('flash', 'Batch baru dibuat. Atur tanggal & kapasitasnya.');
    }

    public function update(Request $request, Batch $batch)
    {
        $data = $request->validate([
            'route' => 'required|in:udara,laut',
            'cutoff_date' => 'nullable|date',
            'ship_date' => 'nullable|date',
            'eta_date' => 'nullable|date',
            'capacity_kg' => 'required|numeric|min:1',
            'resi' => 'nullable|string|max:120',
        ]);
        $batch->update($data);

        return redirect()->route('admin.batches', ['batch' => $batch->id])->with('flash', 'Batch disimpan.');
    }

    public function toggle(Batch $batch, Order $order)
    {
        if ($batch->status > 0) {
            return back()->with('error', 'Batch sudah dikirim, isi tidak bisa diubah.');
        }
        if ((int) $order->batch_id === (int) $batch->id) {
            $order->update(['batch_id' => null, 'scheduled_at' => null, 'stage' => Order::WAREHOUSE]);
        } elseif ($order->stage === Order::WAREHOUSE && $order->country === $batch->country) {
            $order->update(['batch_id' => $batch->id, 'scheduled_at' => now(), 'stage' => Order::SCHEDULED]);
            Notify::send($order->user, 'Jadwal kirim: '.tgl($batch->ship_date), [
                'Pesanan '.$order->code.' ikut pengiriman '.strtolower($batch->routeLabel()).' tanggal '.tgl($batch->ship_date).'.',
            ], 'Lacak pesanan', route('track', ['no' => $order->code]));
        }

        return redirect()->route('admin.batches', ['batch' => $batch->id]);
    }

    public function next(Batch $batch)
    {
        return $this->moveTo($batch, $batch->status + 1);
    }

    /** Klik langsung salah satu tahap batch (maju atau mundur). */
    public function status(Request $request, Batch $batch)
    {
        return $this->moveTo($batch, (int) $request->input('status'));
    }

    protected function moveTo(Batch $batch, int $target)
    {
        $target = max(0, min(3, $target));
        $back = fn ($msg, $type = 'flash') => redirect()->route('admin.batches', ['batch' => $batch->id])->with($type, $msg);

        if ($target === $batch->status) {
            return $back('Batch sudah di tahap '.Batch::STATUS[$target].'.');
        }
        if ($target > 0 && $batch->orders()->count() === 0) {
            return $back('Batch masih kosong. Masukkan paket dulu.', 'error');
        }

        // ---- Mundur: koreksi salah tandai, tanpa email ----
        if ($target < $batch->status) {
            if ($target < 2) {
                // Paket yang belum diberi link Shopee dikembalikan ke "dalam pengiriman"
                $batch->orders()->where('stage', Order::ARRIVED)->update(['stage' => Order::SCHEDULED, 'arrived_at' => null]);
            }
            $batch->update(['status' => $target]);
            Activity::log('Batch **'.tgl($batch->ship_date).'** dikembalikan ke '.strtolower(Batch::STATUS[$target]));

            return $back('Batch dikembalikan ke tahap '.Batch::STATUS[$target].'.');
        }

        // ---- Maju: jalankan tiap tahap berurutan (penitip dikabari) ----
        $batch->load('orders.user');
        while ($batch->status < $target) {
            $batch->update(['status' => $batch->status + 1]);
            foreach ($batch->orders as $o) {
                if ($batch->status === 1) {
                    Notify::send($o->user, 'Pesananmu sedang dikirim', [
                        'Pesanan '.$o->code.' sudah berangkat ke Indonesia lewat jalur '.strtolower($batch->routeLabel()).'.'.($batch->eta_date ? ' Estimasi tiba '.tgl($batch->eta_date).'.' : ''),
                    ], 'Lacak pesanan', route('track', ['no' => $o->code]));
                }
                if ($batch->status === 2 && $o->stage === Order::SCHEDULED) {
                    $o->update(['arrived_at' => now(), 'stage' => Order::ARRIVED]);
                    Notify::send($o->user, 'Barang tiba di Indonesia', [
                        'Pesanan '.$o->code.' sudah tiba. Kami siapkan link pembayaran ongkir & packing di Shopee.',
                    ], 'Lacak pesanan', route('track', ['no' => $o->code]));
                }
            }
        }
        $label = Batch::STATUS[$batch->status];
        Activity::log('Batch **'.tgl($batch->ship_date).'** ditandai '.strtolower($label));
        $msg = 'Batch ditandai: '.$label.'. Penitip dikabari lewat email.';
        if ($batch->status >= 2) {
            $msg .= ' Tempel link Shopee tiap paket di Isi batch ini.';
        }

        return $back($msg);
    }
}
