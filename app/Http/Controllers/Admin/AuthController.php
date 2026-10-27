<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()?->is_admin && $request->user()->role) {
            return redirect(\App\Support\Akses::home($request->user()));
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt(['email' => mb_strtolower($cred['email']), 'password' => $cred['password'], 'is_admin' => true], true)) {
            $request->session()->regenerate();
            $u = Auth::user();
            if (! $u->role) {
                Auth::logout();

                return back()->withInput($request->only('email'))->with('error', 'Akses akun ini sudah dicabut.');
            }

            return redirect()->intended(\App\Support\Akses::home($u));
        }

        return back()->withInput($request->only('email'))->with('error', 'Email atau kata sandi admin salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
