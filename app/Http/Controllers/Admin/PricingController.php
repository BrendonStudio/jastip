<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\MonthlyCost;
use App\Models\Setting;
use App\Support\Harga;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        return view('admin.harga', [
            'a' => Harga::all(),
            'shopeePct' => MonthlyCost::forMonth(now()->format('Y-m'))->shopee_pct,
        ]);
    }

    public function save(Request $request)
    {
        $rules = [];
        foreach (array_keys(Harga::DEFAULTS) as $k) {
            $rules[$k] = 'required|numeric|min:0';
        }
        $data = $request->validate($rules);
        foreach ($data as $k => $v) {
            Setting::put($k, $v);
        }
        Harga::forget();
        Activity::log('Harga diperbarui: ¥1 = '.rpk($data['jp_kurs_jual']).', ₩1 = '.rpk($data['kr_kurs_jual']));

        return redirect()->route('admin.pricing', ['neg' => $request->input('tab_neg', 'jp')])->with('flash', 'Harga tersimpan dan langsung tampil di website.');
    }

    /** Gambar QRIS toko (opsional) untuk halaman pembayaran penitip. */
    public function qris(Request $request)
    {
        if ($request->boolean('hapus')) {
            Setting::query()->where('key', 'qris_image')->delete();
            Activity::log('mematikan pembayaran QRIS');

            return back()->with('flash', 'QRIS dihapus. Penitip hanya bisa bayar lewat transfer bank.');
        }
        $request->validate(['qris' => 'required|image|max:4096'], ['qris.required' => 'Pilih gambar QRIS.']);
        Setting::put('qris_image', $request->file('qris')->store('qris', 'uploads'));
        Activity::log('memperbarui gambar QRIS');

        return back()->with('flash', 'QRIS tersimpan. Penitip sekarang bisa memilih bayar via QRIS.');
    }
}
