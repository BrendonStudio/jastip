@php
    $dark = $dark ?? false;
    $home = $home ?? false;
    $active = $active ?? null;
    $cta = $cta ?? true;
    $minimal = $minimal ?? false;
    $fg = $dark ? '#E4E5EA' : '#15161C';
    $cur = fn ($k) => $active === $k;
    $u = auth()->user();
@endphp
<header style="border-bottom: 1px solid {{ $dark ? '#2A2C35' : '#E6E4DF' }}; background: {{ $dark ? '#15161C' : '#FFFFFF' }}">
<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap">
<a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; font-weight: 800; font-size: 20px; letter-spacing: -0.02em; min-height: 44px; color: {{ $dark ? '#FFFFFF' : '#15161C' }}">
@include('partials.logo', ['light' => $dark])
<span>{{ nama_jastip() }}</span>
</a>
<nav class="topnav" aria-label="Menu utama" style="display: flex; align-items: center; gap: 22px; flex-wrap: wrap; font-size: 15px; font-weight: 600">
@if ($home)
<a href="#cara-kerja" style="text-decoration: none">Cara kerja</a>
<a href="{{ route('war') }}" style="text-decoration: none; color: #B02A19">Jasa war</a>
<a href="#tarif" style="text-decoration: none">Tarif</a>
<a href="#faq" style="text-decoration: none">FAQ</a>
<a href="{{ route('track') }}" style="text-decoration: none">Lacak pesanan</a>
@else
<a href="{{ route('home') }}" style="text-decoration: none; color: {{ $fg }}">Beranda</a>
<a href="{{ route('war') }}" @if($cur('war')) aria-current="page" @endif style="text-decoration: {{ $cur('war') ? 'underline' : 'none' }}; text-underline-offset: 6px; color: {{ $dark ? '#FF8A78' : '#B02A19' }}">Jasa war</a>
<a href="{{ route('track') }}" @if($cur('lacak')) aria-current="page" @endif style="text-decoration: {{ $cur('lacak') ? 'underline' : 'none' }}; text-underline-offset: 6px; color: {{ $fg }}">Lacak pesanan</a>
@endif
@if (! $minimal)
@auth
<a href="{{ route('account') }}" @if($cur('akun')) aria-current="page" @endif style="text-decoration: {{ $cur('akun') ? 'underline' : 'none' }}; text-underline-offset: 6px; color: {{ $fg }}">Akun saya</a>
@if ($u->is_admin && $u->role)
<a href="{{ \App\Support\Akses::home($u) }}" style="text-decoration: none; color: {{ $fg }}">{{ $u->role === 'wh' ? 'Portal warehouse' : 'Admin' }}</a>
@endif
@else
<a href="{{ route('login') }}" style="text-decoration: none; color: {{ $fg }}">Masuk / Daftar</a>
@endauth
@if ($cta)
<a href="{{ route('order.create') }}" style="text-decoration: none; background: {{ $dark ? '#FFFFFF' : '#15161C' }}; color: {{ $dark ? '#15161C' : '#FFFFFF' }}; padding: 12px 18px; border-radius: 10px; font-weight: 700">Titip sekarang</a>
@endif
@endif
</nav>
</div>
</header>
