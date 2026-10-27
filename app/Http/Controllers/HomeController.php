<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\WarDrop;
use App\Support\Harga;

class HomeController extends Controller
{
    public function index()
    {
        $drops = WarDrop::withCount('entries')
            ->where('release_at', '>=', now()->subDays(2))
            ->orderBy('release_at')
            ->take(3)
            ->get();

        $ref = $drops->first(fn ($d) => $d->isOpen()) ?? $drops->first() ?? WarDrop::latest('release_at')->first();

        return view('pages.home', [
            'harga' => ['jp' => Harga::negara('jp'), 'kr' => Harga::negara('kr')],
            'packs' => Harga::packs(),
            'hargaJs' => Harga::forJs(),
            'drops' => $drops,
            'warInfo' => [
                'fee' => $ref->fee ?? 25000,
                'dp' => $ref->dp_pct ?? 30,
                'close' => $ref->close_hours ?? 6,
            ],
            'nextBatch' => Batch::where('status', 0)->whereNotNull('ship_date')->where('ship_date', '>=', today())->orderBy('ship_date')->first(),
        ]);
    }
}
