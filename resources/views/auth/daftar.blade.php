@extends('layouts.site')
@section('title', $editing ? 'Ubah data akun' : 'Masuk atau daftar')

@php
    $bars = [0, 1, 2];
    $u = auth()->user();
    $inp = 'border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 16px; color: #15161C; background: #FFFFFF';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $btn = 'border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px; cursor: pointer';
    $check = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex: none; margin-top: 2px"><path d="M20 6 9 17l-5-5"></path></svg>';
@endphp

{{-- ======================= bagian-bagian form (dipakai di HP & desktop) ======================= --}}
@php
$devCode = session('dev_code');
@endphp

@section('content')
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%; min-height: 100vh; display: flex; flex-direction: column">
@include('partials.header', ['minimal' => true])
@include('partials.flash')

<main style="flex: 1; width: 100%; max-width: 1100px; margin: 0 auto; padding: 56px 24px; box-sizing: border-box; display: flex; flex-wrap: wrap; gap: 32px; align-items: stretch">
<section aria-labelledby="why-title" style="flex: 1 1 380px; background: #15161C; color: #FFFFFF; border-radius: 24px; padding: 40px; box-sizing: border-box; display: flex; flex-direction: column; gap: 24px">
<span style="font-size: 13px; font-weight: 800; letter-spacing: 0.06em; color: #FF8A78">AKUN PENITIP</span>
<h2 id="why-title" style="margin: 0; font-size: 34px; line-height: 1.15; font-weight: 800; letter-spacing: -0.02em">Satu akun untuk titip album, war merch, dan lacak semua pesanan.</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 16px; font-size: 16px; color: #E4E5EA">
@foreach (['Ikut war dan dapat nomor antrian', 'Lacak semua pesanan di satu tempat', 'Riwayat pembayaran, DP, dan refund tercatat', 'Data Shopee diisi sekali, dipakai seterusnya', 'Kabar pesanan, tagihan & war langsung ke email'] as $t)
<li style="display: flex; gap: 12px; color: #E4E5EA"><span style="color: #8FE0B5; display: flex">{!! $check !!}</span>{{ $t }}</li>
@endforeach
</ul>
<p style="margin: auto 0 0; font-size: 14px; color: #C9CAD1">Kalkulator dan jadwal war tetap bisa dilihat tanpa akun.</p>
</section>

<section aria-label="Form akun" style="flex: 1 1 420px; background: #FFFFFF; border-radius: 24px; padding: 40px; box-sizing: border-box; display: flex; flex-direction: column; gap: 22px">
@unless ($editing)
<div aria-hidden="true" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px">
@foreach ($bars as $i)<span style="height: 4px; border-radius: 4px; background: {{ $i <= min($step, 2) ? '#15161C' : '#E6E4DF' }}"></span>@endforeach
</div>
@endunless
@include('auth._steps', ['m' => false])
</section>
</main>
</div>

{{-- ======================= versi HP ======================= --}}
<div class="mob">
<div class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #FFFFFF">
<header style="padding: 14px 16px; display: flex; align-items: center; gap: 8px">
@php
    $backUrl = match (true) {
        $editing => route('account'),
        $step === 1 => route('login', ['ganti' => 1]),
        default => route('home'),
    };
@endphp
<a href="{{ $backUrl }}" aria-label="Kembali" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></a>
@unless ($editing)
<div aria-hidden="true" style="flex: 1; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; padding-right: 8px">
@foreach ($bars as $i)<span style="height: 4px; border-radius: 4px; background: {{ $i <= min($step, 2) ? '#15161C' : '#E6E4DF' }}"></span>@endforeach
</div>
@else
<h1 style="margin: 0; font-size: 18px; font-weight: 800">Ubah data akun</h1>
@endunless
</header>
@include('partials.flash', ['style' => 'padding: 0 24px'])
<main style="flex: 1; padding: 8px 24px 24px; display: flex; flex-direction: column; gap: 20px">
@include('auth._steps', ['m' => true])
</main>
</div>
</div>
@endsection
