<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Batch;
use App\Models\Order;
use App\Models\WarDrop;
use App\Support\Alur;
use App\Support\Harga;
use App\Support\Keuangan;

class DashboardController extends Controller
{
    public function index()
    {
        $nextBatch = Batch::with('orders')->where('status', 0)->orderByRaw('ship_date is null')->orderBy('ship_date')->first();
        $nextWar = WarDrop::withCount('entries')->where('release_at', '>=', now())->orderBy('release_at')->first();

        // Posisi semua pesanan
        $active = Order::with('batch')->where('stage', '<', Order::DONE)->whereNull('cancelled_at')->get();
        $flow = [];
        foreach (Alur::POS as $i => $p) {
            if ($i === count(Alur::POS) - 1) {
                $n = Order::where('stage', Order::DONE)->where('completed_at', '>=', now()->startOfMonth())->count();
                $flow[] = ['t' => $p['t'], 'n' => $n, 'aksi' => 0, 'last' => true, 'href' => route('admin.orders', ['pos' => $i])];
                continue;
            }
            $in = $active->filter(fn ($o) => Alur::pos($o) === $i);
            $flow[] = [
                't' => $p['t'], 'n' => $in->count(),
                'aksi' => $in->filter(fn ($o) => Alur::step($o)['kind'] === 'need')->count(),
                'last' => false, 'href' => route('admin.orders', ['pos' => $i]),
            ];
        }

        return view('admin.ringkasan', [
            'flow' => $flow,
            'tasks' => Alur::tasks(),
            'nextWar' => $nextWar,
            'nextBatch' => $nextBatch,
            'jp' => Harga::negara('jp'),
            'kr' => Harga::negara('kr'),
            'keu' => Keuangan::bulan(now()->format('Y-m')),
            'activities' => Activity::with('user')->latest('created_at')->latest('id')->take(6)->get(),
        ]);
    }
}
