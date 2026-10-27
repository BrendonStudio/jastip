<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Support\Akses;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Tim & akses: undang staf, atur peran, matriks hak akses, catatan aktivitas. */
class TeamController extends Controller
{
    public function index(Request $request)
    {
        $members = User::where('is_admin', true)->whereNotNull('role')->get()
            ->sortBy(fn ($u) => [array_search($u->role, array_keys(Akses::ROLES)), $u->name ?? $u->email])->values();

        return view('admin.tim', [
            'members' => $members,
            'perm' => Akses::perm(),
            'invite' => in_array($request->query('peran'), ['admin', 'keu', 'wh'], true) ? $request->query('peran') : old('role', 'admin'),
            'log' => Activity::with('user')->whereNotNull('user_id')->latest('created_at')->latest('id')->take(10)->get(),
            'edit' => $request->query('kelola'),
        ]);
    }

    public function invite(Request $request)
    {
        $d = $request->validate([
            'email' => 'required|email|max:190',
            'role' => ['required', Rule::in(['admin', 'keu', 'wh'])],
            'wh_country' => 'nullable|in:jp,kr',
        ], ['email.required' => 'Isi email anggota yang diundang.', 'email.email' => 'Format email belum benar.']);
        $email = mb_strtolower(trim($d['email']));
        $u = User::firstOrNew(['email' => $email]);
        if ($u->exists && ! $u->is_admin && $u->orders()->exists()) {
            return back()->withInput()->with('error', 'Email ini sudah dipakai akun penitip. Pakai email lain untuk akun staf.');
        }
        if ($u->exists && $u->role === 'owner') {
            return back()->with('error', 'Pemilik tidak bisa diubah lewat undangan.');
        }
        $u->fill([
            'is_admin' => true,
            'role' => $d['role'],
            'wh_country' => $d['role'] === 'wh' ? ($d['wh_country'] ?? null) : null,
            'invite_token' => Str::random(48),
            'invited_at' => now(),
            'name' => $u->name ?: null,
        ]);
        $u->save();

        $label = Akses::roleLabel($u);
        Notify::send($u, 'Kamu diundang ke tim '.nama_jastip(), [
            Auth::user()->displayName().' mengundangmu sebagai '.$label.'.',
            Akses::ROLE_DESC[$d['role']] ?? '',
            'Buat kata sandi lewat tombol di bawah. Link ini hanya bisa dipakai sekali.',
        ], 'Terima undangan', route('invite.show', $u->invite_token), null, ['tag' => 'UNDANGAN TIM', 'c' => 'blue']);
        Activity::log('mengundang **'.$email.'** sebagai '.$label);

        $msg = 'Undangan terkirim ke '.$email.'.';
        if (Notify::isTestMode()) {
            $msg .= ' (Mode uji: link undangan '.route('invite.show', $u->invite_token).')';
        }

        return redirect()->route('admin.team')->with('flash', $msg);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->is_admin && $user->role, 404);
        if ($user->role === 'owner' || $user->id === Auth::id()) {
            return back()->with('error', 'Peran pemilik dan akunmu sendiri tidak bisa diubah di sini.');
        }
        if ($request->input('action') === 'cabut') {
            $user->update(['role' => null, 'invite_token' => null]);
            Activity::log('mencabut akses **'.$user->displayName().'**');

            return redirect()->route('admin.team')->with('flash', 'Akses '.$user->displayName().' dicabut.');
        }
        $d = $request->validate([
            'role' => ['required', Rule::in(['admin', 'keu', 'wh'])],
            'wh_country' => 'nullable|in:jp,kr',
        ]);
        $user->update(['role' => $d['role'], 'wh_country' => $d['role'] === 'wh' ? ($d['wh_country'] ?? null) : null]);
        Activity::log('mengubah peran **'.$user->displayName().'** menjadi '.Akses::roleLabel($user));

        return redirect()->route('admin.team')->with('flash', 'Peran '.$user->displayName().' disimpan.');
    }

    /** Klik kotak matriks: nyalakan / matikan satu menu untuk satu peran. */
    public function perm(Request $request)
    {
        $d = $request->validate([
            'role' => ['required', Rule::in(['admin', 'keu', 'wh'])],
            'menu' => ['required', Rule::in(array_keys(Akses::MENUS))],
        ]);
        $p = Akses::perm();
        $list = $p[$d['role']];
        $on = in_array($d['menu'], $list, true);
        $p[$d['role']] = $on ? array_values(array_diff($list, [$d['menu']])) : array_values(array_merge($list, [$d['menu']]));
        Akses::savePerm($p);
        Activity::log(($on ? 'menutup' : 'membuka').' menu '.Akses::MENUS[$d['menu']][0].' untuk peran '.Akses::ROLES[$d['role']]);

        return redirect()->route('admin.team')->with('flash', 'Hak akses '.Akses::ROLES[$d['role']].' diperbarui.');
    }

    /* ---------------- Terima undangan (tanpa login) ---------------- */

    public function showInvite(string $token)
    {
        $u = User::where('invite_token', $token)->whereNotNull('role')->first();
        if (! $u) {
            return redirect()->route('admin.login')->with('error', 'Link undangan sudah dipakai atau tidak berlaku. Minta undangan baru ke pemilik.');
        }

        return view('admin.undangan', ['u' => $u, 'token' => $token]);
    }

    public function acceptInvite(Request $request, string $token)
    {
        $u = User::where('invite_token', $token)->whereNotNull('role')->firstOrFail();
        $d = $request->validate([
            'name' => 'required|string|max:120',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Isi nama kamu.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Ulangi kata sandi yang sama.',
        ]);
        $u->forceFill([
            'name' => $d['name'], 'password' => $d['password'], 'invite_token' => null,
            'email_verified_at' => $u->email_verified_at ?? now(), 'last_seen_at' => now(),
        ])->save();
        Auth::login($u, true);
        $request->session()->regenerate();
        Activity::log('bergabung ke tim sebagai '.Akses::roleLabel($u));

        return redirect(Akses::home($u))->with('flash', 'Selamat datang di tim, '.$u->displayName().'!');
    }
}
