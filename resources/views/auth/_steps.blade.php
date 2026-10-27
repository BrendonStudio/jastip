@php
    $h1 = 'margin: 0; font-size: '.($m ? '28px' : '30px').'; font-weight: 800; letter-spacing: -0.02em';
    $p = 'margin: 0; font-size: '.($m ? '15px' : '15.5px').'; line-height: 1.55; color: #4E515C';
    $bh = $m ? '52px' : '54px';
    $linkBack = 'align-self: flex-start; min-height: 40px; display: inline-flex; align-items: center; padding: 0; font-weight: 700; font-size: 14px; color: #4E515C; text-decoration: none';
@endphp

@if ($devCode && $step === 1)
<div style="background: #FFF6E5; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; line-height: 1.5; color: #5C3900"><strong>Mode uji</strong> (email belum disetel di .env): kode kamu <strong style="font-size: 16px; letter-spacing: 0.1em">{{ $devCode }}</strong></div>
@endif

{{-- ---------- Langkah 1: email ---------- --}}
@if ($step === 0)
<form method="POST" action="{{ route('login.send') }}" style="display: flex; flex-direction: column; gap: 20px">
@csrf
<div style="display: flex; flex-direction: column; gap: 8px">
<h1 style="{{ $h1 }}">Masuk atau daftar</h1>
<p style="{{ $p }}">Cukup email. Tanpa kata sandi, kode masuk dikirim ke email kamu.</p>
</div>
<label style="{{ $lab }}">Email
<input type="email" name="email" inputmode="email" autocomplete="email" required placeholder="nama@email.com" value="{{ old('email', session('login_email')) }}" style="height: {{ $bh }}; {{ $inp }}; font-size: 17px; font-weight: 700">
</label>
<button type="submit" style="min-height: {{ $bh }}; {{ $btn }}">Kirim kode ke email</button>
@if ($m)
<ul style="list-style: none; margin: 8px 0 0; padding: 16px; background: #F4F3EF; border-radius: 16px; display: flex; flex-direction: column; gap: 12px; font-size: 14px">
<li style="font-weight: 800">Dengan akun kamu bisa:</li>
@foreach (['Ikut war dan dapat nomor antrian', 'Lacak semua pesanan di satu tempat', 'Lihat riwayat pembayaran, DP, dan refund', 'Isi data Shopee sekali, dipakai seterusnya', 'Kabar pesanan, tagihan & war langsung ke email'] as $t)
<li style="display: flex; gap: 10px"><span style="color: #1D7A4C; display: flex">{!! str_replace(['width="20"', 'height="20"'], ['width="18"', 'height="18"'], $check) !!}</span>{{ $t }}</li>
@endforeach
</ul>
<p style="margin: 0; font-size: 13px; color: #4E515C; text-align: center">Kalkulator dan jadwal war bisa dilihat tanpa akun.</p>
@endif
</form>
@endif

{{-- ---------- Langkah 2: kode ---------- --}}
@if ($step === 1)
<div style="display: flex; flex-direction: column; gap: 20px">
@unless ($m)
<a href="{{ route('login', ['ganti' => 1]) }}" style="{{ $linkBack }}">← Ganti email</a>
@endunless
<div style="display: flex; flex-direction: column; gap: 8px">
<h1 style="{{ $h1 }}">Masukkan kode</h1>
<p style="{{ $p }}">Kode 6 digit sudah dikirim ke <strong style="color: #15161C">{{ session('login_email', '[email]') }}</strong>. Tidak ada di inbox? Cek folder Promosi atau Spam.</p>
</div>
<form method="POST" action="{{ route('login.verify') }}" style="display: flex; flex-direction: column; gap: 20px">
@csrf
<label style="{{ $lab }}">Kode verifikasi
<input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required placeholder="••••••" autofocus @if(session('code_error')) aria-invalid="true" aria-describedby="otp-err-{{ $m ? 'm' : 'd' }}" @endif style="height: {{ $m ? '60px' : '64px' }}; border: {{ session('code_error') ? '2px solid #C2311F' : '1.5px solid #15161C' }}; border-radius: 12px; padding: 0 16px; font-family: inherit; font-size: {{ $m ? '28px' : '30px' }}; font-weight: 800; letter-spacing: 0.4em; text-align: center; color: #15161C; background: #FFFFFF">
</label>
@if (session('code_error'))
<p id="otp-err-{{ $m ? 'm' : 'd' }}" role="alert" style="margin: -8px 0 0; display: flex; gap: 8px; align-items: flex-start; font-size: 14px; font-weight: 600; color: #B02A19"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" style="flex: none; margin-top: 1px"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v5M12 16h.01"></path></svg>{{ session('code_error') }}</p>
@endif
<button type="submit" style="min-height: {{ $bh }}; {{ $btn }}">Verifikasi</button>
</form>
<form method="POST" action="{{ route('login.resend') }}" style="margin: 0; text-align: center">
@csrf
<p style="margin: 0; font-size: 14px; color: #4E515C">Belum dapat kode? <button type="submit" style="border: 0; background: transparent; padding: 8px 0; font-family: inherit; font-size: 14px; font-weight: 700; color: #15161C; text-decoration: underline; cursor: pointer">Kirim ulang</button></p>
</form>
</div>
@endif

{{-- ---------- Langkah 3: profil ---------- --}}
@if ($step === 2)
<form method="POST" action="{{ route('profile.save') }}" style="display: flex; flex-direction: column; gap: 18px">
@csrf
@if ($editing)<input type="hidden" name="editing" value="1">@endif
@if (! $m && ! $editing)
<a href="{{ route('home') }}" style="{{ $linkBack }}">← Kembali</a>
@endif
<div style="display: flex; flex-direction: column; gap: 8px">
<h1 style="{{ $h1 }}">{{ $editing ? 'Ubah data akun' : 'Lengkapi profil' }}</h1>
<p style="{{ $p }}">{{ $editing ? 'Email: '.$u->email : 'Sekali saja. Dipakai untuk penawaran dan pembayaran tahap 2.' }}</p>
</div>
<div style="display: grid; grid-template-columns: {{ $m ? '1fr' : 'repeat(2, minmax(0, 1fr))' }}; gap: {{ $m ? '18px' : '12px' }}">
<label style="{{ $lab }}">Nama lengkap
<input type="text" name="name" autocomplete="name" required value="{{ old('name', $u->name) }}" style="height: 50px; {{ $inp }}; min-width: 0">
</label>
<label style="{{ $lab }}">Username Shopee
<input type="text" name="shopee_username" required placeholder="Tanpa @" value="{{ old('shopee_username', $u->shopee_username) }}" style="height: 50px; {{ $inp }}; min-width: 0">
@if ($m)<span style="font-size: 12.5px; font-weight: 500">Link pembayaran ongkir &amp; packing dikirim ke akun ini.</span>@endif
</label>
</div>
<label style="{{ $lab }}">Nomor WhatsApp (opsional)
<input type="tel" name="whatsapp" inputmode="tel" autocomplete="tel" placeholder="Untuk chat dengan admin" value="{{ old('whatsapp', $u->whatsapp) }}" style="height: 50px; {{ $inp }}">
</label>
<label style="{{ $lab }}">Grup / idola favorit (opsional)
<input type="text" name="favorite_group" placeholder="{{ $m ? 'Mis. nama grup, nama grup' : 'Mis. nama grup, nama grup · kami kabari war & rilisan barunya' }}" value="{{ old('favorite_group', $u->favorite_group) }}" style="height: 50px; {{ $inp }}">
@if ($m)<span style="font-size: 12.5px; font-weight: 500">Kami kabari lewat email kalau ada war atau rilisan baru dari grup ini.</span>@endif
</label>
<label style="{{ $lab }}">Rekening untuk refund (opsional)
<input type="text" name="refund_account" placeholder="Bank · nomor · atas nama" value="{{ old('refund_account', $u->refund_account) }}" style="height: 50px; {{ $inp }}">
</label>
@unless ($editing)
<label style="display: flex; gap: 12px; align-items: flex-start; font-size: {{ $m ? '13.5px' : '14px' }}; line-height: 1.5; color: #2C2E36">
<input type="checkbox" name="agree" value="1" required style="width: 22px; height: 22px; margin: 0; flex: none">
<span>Saya setuju dengan <a href="{{ route('terms') }}" target="_blank" style="font-weight: 700">syarat &amp; ketentuan</a> termasuk aturan DP dan refund.</span>
</label>
@endunless
<button type="submit" style="min-height: {{ $bh }}; {{ $btn }}">{{ $editing ? 'Simpan perubahan' : 'Buat akun' }}</button>
</form>
@endif

{{-- ---------- Selesai ---------- --}}
@if ($step === 3)
<div style="padding: {{ $m ? '40px 0 0' : '24px 0' }}; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px">
<div style="width: 72px; height: 72px; border-radius: 50%; background: #E6F4EC; display: flex; align-items: center; justify-content: center"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1D7A4C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path></svg></div>
<h1 style="margin: 0; font-size: {{ $m ? '26px' : '28px' }}; font-weight: 800">Akun siap dipakai</h1>
<p style="margin: 0; font-size: 15.5px; color: #4E515C">Mau mulai dari mana?</p>
@if ($m)
<a href="{{ route('order.create') }}" style="display: flex; align-items: center; justify-content: center; min-height: 52px; width: 100%; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 16px">Titip barang</a>
<a href="{{ route('war') }}" style="display: flex; align-items: center; justify-content: center; min-height: 52px; width: 100%; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 16px; box-sizing: border-box">Lihat jadwal war</a>
<a href="{{ route('account') }}" style="min-height: 48px; display: flex; align-items: center; font-weight: 700; font-size: 15px">Ke akun saya</a>
@else
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; width: 100%">
<a href="{{ route('order.create') }}" style="display: flex; align-items: center; justify-content: center; min-height: 52px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 15px">Titip barang</a>
<a href="{{ route('war') }}" style="display: flex; align-items: center; justify-content: center; min-height: 52px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 15px">Jadwal war</a>
<a href="{{ route('account') }}" style="display: flex; align-items: center; justify-content: center; min-height: 52px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 15px">Akun saya</a>
</div>
@endif
</div>
@endif
