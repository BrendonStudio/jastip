<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Order;
use App\Models\User;
use App\Support\Notify;
use Illuminate\Http\Request;

/** Portal staf warehouse: tandai paket tiba, upload 4 foto, timbang, kondisi. */
class WarehouseController extends Controller
{
    public const SLOTS = [
        ['Barang utuh', 'Tampak depan, masih segel'],
        ['Isi & photocard', 'Setelah dibuka'],
        ['POB / bonus', 'Bonus dari toko'],
        ['Timbangan', 'Angka berat terlihat'],
    ];

    protected function scope(Request $request)
    {
        $c = $request->user()->wh_country;

        return Order::with(['items', 'user'])->whereNull('cancelled_at')->when($c, fn ($q) => $q->where('country', $c));
    }

    protected function guard(Request $request, Order $order): void
    {
        $c = $request->user()->wh_country;
        abort_if($c && $order->country !== $c, 403);
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['tiba', 'foto', 'kirim'], true) ? $request->query('tab') : 'foto';
        $base = fn () => $this->scope($request);
        $lists = [
            'tiba' => $base()->where('stage', Order::CHECKED_OUT)->whereNull('wh_arrived_at')->whereNull('wh_submitted_at')->orderBy('checked_out_at')->get(),
            'foto' => $base()->where('stage', Order::CHECKED_OUT)->whereNotNull('wh_arrived_at')->whereNull('wh_submitted_at')->orderBy('wh_arrived_at')->get(),
            'kirim' => $base()->whereNotNull('wh_submitted_at')->where(fn ($q) => $q->where('stage', Order::CHECKED_OUT)->orWhere('warehouse_approved_at', '>=', now()->subDays(14)))->latest('wh_submitted_at')->take(30)->get(),
        ];

        return view('pages.gudang', ['tab' => $tab, 'lists' => $lists, 'rows' => $lists[$tab], 'u' => $request->user()]);
    }

    public function show(Request $request, Order $order)
    {
        $this->guard($request, $order);
        if ($order->stage !== Order::CHECKED_OUT || $order->wh_submitted_at) {
            return redirect()->route('warehouse', ['tab' => 'kirim'])->with('error', 'Paket '.$order->code.' sudah dikirim ke admin.');
        }
        $order->load('items', 'user');

        return view('pages.gudang-detail', ['o' => $order, 'u' => $request->user()]);
    }

    public function arrived(Request $request, Order $order)
    {
        $this->guard($request, $order);
        abort_unless($order->stage === Order::CHECKED_OUT, 422);
        $order->update(['wh_arrived_at' => now()]);
        Activity::log('menandai paket **'.$order->code.'** tiba di gudang '.$order->neg()['nama']);

        return redirect()->route('warehouse', ['tab' => 'foto'])->with('flash', $order->code.' ditandai tiba. Lanjut foto & timbang.');
    }

    public function submit(Request $request, Order $order)
    {
        $this->guard($request, $order);
        abort_unless($order->stage === Order::CHECKED_OUT && ! $order->wh_submitted_at, 422);
        $d = $request->validate([
            'photo' => 'nullable|array',
            'photo.*' => 'nullable|image|max:8192',
            'weight' => 'required|numeric|min:0.01|max:500',
            'photocard_note' => 'nullable|string|max:190',
            'wh_condition' => 'required|in:baik,penyok,rusak',
            'wh_note' => 'nullable|string|max:500',
        ], [
            'weight.required' => 'Isi berat timbang.',
            'photo.*.image' => 'Foto harus berupa gambar.',
            'photo.*.max' => 'Ukuran foto maksimal 8 MB.',
        ]);

        $photos = array_pad(array_slice($order->photos ?? [], 0, 4), 4, null);
        foreach ((array) $request->file('photo', []) as $i => $f) {
            if ($f && $i >= 0 && $i < 4) {
                $photos[(int) $i] = $f->store('warehouse', 'uploads');
            }
        }
        $missing = count(array_filter($photos, fn ($p) => ! $p));
        // simpan dulu foto yang sudah ada supaya tidak perlu upload ulang
        $order->update(['photos' => array_values(array_filter($photos)) ? $photos : null, 'wh_arrived_at' => $order->wh_arrived_at ?? now()]);
        if ($missing > 0) {
            return back()->withInput()->with('error', 'Lengkapi '.$missing.' foto lagi.');
        }

        $order->update([
            'photos' => $photos,
            'weight' => $d['weight'],
            'photocard_note' => $d['photocard_note'] ?? null,
            'wh_condition' => $d['wh_condition'],
            'condition_note' => Order::WH_CONDITION[$d['wh_condition']],
            'wh_note' => $d['wh_note'] ?? null,
            'wh_submitted_at' => now(),
        ]);
        Activity::log('mengirim 4 foto untuk **'.$order->code.'** ('.angka($d['weight']).' kg · '.Order::WH_CONDITION[$d['wh_condition']].')');
        foreach (User::notifyStaff() as $admin) {
            Notify::send($admin, 'Foto warehouse '.$order->code.' siap dicek', [
                'Warehouse '.$order->neg()['nama'].' mengirim 4 foto · '.angka($d['weight']).' kg · kondisi '.mb_strtolower(Order::WH_CONDITION[$d['wh_condition']]).'.',
            ], 'Cek & setujui', route('admin.orders', ['id' => $order->id]));
        }

        return redirect()->route('warehouse', ['tab' => 'kirim'])->with('flash', 'Terkirim ke admin. '.$order->code.' akan dicek sebelum tampil ke penitip.');
    }
}
