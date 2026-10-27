<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = User::where('is_admin', false)
            ->withCount(['orders', 'warEntries', 'warEntries as war_menang_count' => fn ($w) => $w->where('result', 'menang')])
            ->latest();
        if ($s = trim((string) $request->query('q'))) {
            $s = ltrim($s, '@');
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('shopee_username', 'like', "%$s%"));
        }
        $users = $q->paginate(30)->withQueryString();

        $sel = $request->filled('id') ? User::where('is_admin', false)->find($request->query('id')) : $users->first();
        $detail = null;
        if ($sel) {
            $orders = $sel->orders()->with(['items', 'batch'])->latest()->take(6)->get();
            $entries = $sel->warEntries()->with('drop')->latest()->take(4)->get();
            $all = $sel->orders()->with('items')->get();
            $detail = [
                'u' => $sel,
                'orders' => $orders,
                'entries' => $entries,
                'belanja' => $all->sum(fn ($o) => ($o->paid_at ? $o->tahap1() : 0) + ($o->completed_at ? $o->tahap2() : 0)),
                'refund' => $sel->refundBalance(),
            ];
        }

        return view('admin.pelanggan', ['users' => $users, 'detail' => $detail]);
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->is_admin, 403);
        $data = $request->validate(['admin_note' => 'nullable|string|max:2000']);
        $user->update([
            'admin_note' => $data['admin_note'] ?? null,
            'is_flagged' => $request->boolean('is_flagged'),
        ]);

        return redirect()->route('admin.customers', ['id' => $user->id, 'q' => $request->input('q')])->with('flash', 'Data pelanggan disimpan.');
    }
}
