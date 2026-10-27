<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\LoginCode;
use App\Models\User;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/** Masuk / daftar penitip dengan kode 6 digit lewat email (tanpa kata sandi). */
class AuthController extends Controller
{
    public function showEmail(Request $request)
    {
        if (Auth::check() && ! $request->has('ganti')) {
            return Auth::user()->profileComplete() ? redirect()->route('account') : redirect()->route('profile');
        }

        return $this->page(0);
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate(['email' => 'required|email:filter|max:190'], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
        ]);
        $email = mb_strtolower(trim($data['email']));
        $this->issueCode($email);
        session(['login_email' => $email]);

        return redirect()->route('login.code');
    }

    public function showCode()
    {
        if (! session('login_email')) {
            return redirect()->route('login');
        }

        return $this->page(1);
    }

    public function resend()
    {
        $email = session('login_email');
        if (! $email) {
            return redirect()->route('login');
        }
        $this->issueCode($email);

        return redirect()->route('login.code')->with('flash', 'Kode baru sudah dikirim.');
    }

    public function verify(Request $request)
    {
        $email = session('login_email');
        if (! $email) {
            return redirect()->route('login');
        }
        $code = preg_replace('/\D/', '', (string) $request->input('code'));

        $row = LoginCode::where('email', $email)->latest('id')->first();
        if (! $row || $row->expires_at->isPast()) {
            return back()->with('code_error', 'Kode salah atau sudah kedaluwarsa. Kirim ulang kode ke email.');
        }
        if ($row->attempts >= 5) {
            return back()->with('code_error', 'Terlalu banyak percobaan. Kirim ulang kode ke email.');
        }
        if (! Hash::check($code, $row->code_hash)) {
            $row->increment('attempts');
            $left = max(0, 5 - $row->attempts);

            return back()->with('code_error', $left ? 'Kode salah atau sudah kedaluwarsa. Sisa '.$left.' percobaan.' : 'Kode salah. Kirim ulang kode ke email.');
        }

        LoginCode::where('email', $email)->delete();
        $user = User::firstOrCreate(['email' => $email]);
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
        $isNew = $user->wasRecentlyCreated;
        Auth::login($user, true);
        $request->session()->regenerate();
        session()->forget(['login_email', 'dev_code']);

        if ($isNew) {
            Activity::log('Akun baru **'.$email.'** mendaftar');
        }

        if (! $user->profileComplete()) {
            return redirect()->route('profile');
        }

        return redirect()->intended(route('account'));
    }

    public function showProfile(Request $request)
    {
        return $this->page(2, $request->routeIs('account.edit') || Auth::user()->profileComplete());
    }

    public function saveProfile(Request $request)
    {
        $user = $request->user();
        $editing = $request->boolean('editing') || $user->profileComplete();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'shopee_username' => 'required|string|max:60',
            'whatsapp' => 'nullable|string|max:30',
            'favorite_group' => 'nullable|string|max:190',
            'refund_account' => 'nullable|string|max:190',
            'agree' => $editing ? 'nullable' : 'accepted',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'shopee_username.required' => 'Username Shopee wajib diisi.',
            'agree.accepted' => 'Centang persetujuan syarat & ketentuan dulu.',
        ]);
        $data['shopee_username'] = ltrim(trim($data['shopee_username']), '@');
        unset($data['agree']);
        $user->update($data);

        if ($editing) {
            return redirect()->route('account')->with('flash', 'Data akun tersimpan.');
        }

        $intended = session()->pull('url.intended');
        if ($intended) {
            return redirect($intended);
        }

        return redirect()->route('login.done');
    }

    public function done()
    {
        return $this->page(3);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function page(int $step, bool $editing = false)
    {
        return view('auth.daftar', ['step' => $step, 'editing' => $editing]);
    }

    protected function issueCode(string $email): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        LoginCode::where('email', $email)->delete();
        LoginCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        Notify::send($email, 'Kode masuk', [
            'Masukkan kode ini untuk masuk ke akunmu. Kode berlaku 15 menit.',
        ], null, null, $code, ['tag' => 'AKUN', 'c' => 'blue', 'subject' => 'Kode masuk kamu: '.$code, 'pre' => 'Berlaku 15 menit. Jangan bagikan ke siapa pun.', 'note' => 'Kami tidak pernah meminta kode ini lewat chat atau telepon. Kalau kamu tidak merasa meminta kode, abaikan email ini.']);

        if (Notify::isTestMode()) {
            session(['dev_code' => $code]);
        }
    }
}
