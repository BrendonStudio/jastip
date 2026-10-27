@extends('layouts.site')
@section('body_class', 'bg-soft')

@section('content')
@php
    $me = auth()->user();
    $tasks = \App\Support\Alur::tasks();
    $badge = [
        'pesanan' => [$tasks->where('group', '!=', 'bayar')->count(), '#C2311F', '#FFFFFF'],
        'pembayaran' => [\App\Support\Alur::verifCount(), '#D99A1E', '#15161C'],
    ];
    $menu = collect(\App\Support\Akses::MENUS)->except('gudang')->filter(fn ($m, $k) => \App\Support\Akses::can($me, $k));
    $full = trim($__env->yieldContent('full')) !== '';
    $tab = [
        'ringkasan' => ['Tugas', '<path d="M9 11l3 3 8-8"></path><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"></path>'],
        'pesanan' => ['Pesanan', '<path d="M6 8h12l-1 12H7Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path>'],
        'pembayaran' => ['Bayar', '<rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path>'],
        'war' => ['War', '<path d="M13 2 4 14h7l-1 8 9-12h-7Z"></path>'],
        'jadwal' => ['Kirim', '<path d="M22 2 11 13"></path><path d="M22 2 15 22l-4-9-9-4Z"></path>'],
    ];
@endphp
<div class="{{ $full ? 'adm-full' : '' }}" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%; min-height: 100vh; display: flex; flex-wrap: wrap; align-items: stretch">

<aside class="adm-aside" style="flex: 1 1 220px; max-width: 240px; background: #15161C; color: #FFFFFF; padding: 20px 14px; box-sizing: border-box; display: flex; flex-direction: column; gap: 24px">
<a href="{{ \App\Support\Akses::home($me) }}" style="display: flex; align-items: center; gap: 10px; padding: 0 8px; font-weight: 800; font-size: 17px; color: #FFFFFF; text-decoration: none">
@include('partials.logo', ['light' => true, 'w' => 30, 'h' => 20])
<span>Admin</span>
</a>
<nav class="adm-nav" aria-label="Menu admin" style="display: flex; flex-direction: column; gap: 4px; font-size: 15px; font-weight: 600">
@foreach ($menu as $k => [$label, $r])
<a href="{{ route($r) }}" @if(request()->routeIs($r.'*')) aria-current="page" @endif style="display: flex; justify-content: space-between; align-items: center; gap: 8px">{{ $label }}@if (($badge[$k][0] ?? 0) > 0)<span class="adm-badge" style="font-size: 12px; font-weight: 800; background: {{ $badge[$k][1] }}; color: {{ $badge[$k][2] }}; padding: 2px 8px; border-radius: 999px">{{ $badge[$k][0] }}</span>@endif</a>
@endforeach
</nav>
<div style="margin-top: auto; display: flex; flex-direction: column; gap: 4px; font-size: 14px; font-weight: 600">
<span style="padding: 0 12px 6px; font-size: 12.5px; font-weight: 600; color: #8C8E98">{{ $me->displayName() }} · {{ \App\Support\Akses::roleLabel($me) }}</span>
<a href="{{ route('home') }}" target="_blank" style="color: #C9CAD1; text-decoration: none; padding: 10px 12px">Lihat website ↗</a>
<form method="POST" action="{{ route('admin.logout') }}" style="margin: 0">@csrf<button type="submit" style="border: 0; background: transparent; color: #FF8A78; font-family: inherit; font-weight: 600; font-size: 14px; padding: 10px 12px; cursor: pointer">Keluar</button></form>
</div>
</aside>

@if ($full)
<main class="adm-main adm-main-full" style="flex: 999 1 560px; min-width: 0; height: 100vh; padding: 28px 32px; box-sizing: border-box; display: flex; flex-direction: column; gap: 18px; position: relative">
@else
<main class="adm-main" style="flex: 999 1 560px; min-width: 0; padding: 32px 32px 48px; box-sizing: border-box; display: flex; flex-direction: column; gap: 20px">
@endif
@include('partials.flash', ['style' => 'padding: 0; max-width: none; margin: 0'])
@yield('main')
</main>
</div>

{{-- ===== Menu bawah versi HP ===== --}}
<nav class="adm-tabbar" aria-label="Menu admin (HP)">
@foreach ($tab as $k => [$l, $ic])
@continue(! \App\Support\Akses::can($me, $k))
@php $r = \App\Support\Akses::MENUS[$k][1]; $on = request()->routeIs($r.'*'); @endphp
<a href="{{ route($r) }}" @if($on) aria-current="page" @endif><span style="position: relative; display: flex"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ic !!}</svg>@if (($badge[$k][0] ?? 0) > 0)<span style="position: absolute; top: -6px; right: -10px; font-size: 10px; font-weight: 800; background: {{ $badge[$k][1] }}; color: {{ $badge[$k][2] }}; padding: 1px 5px; border-radius: 999px">{{ $badge[$k][0] }}</span>@endif</span>{{ $l }}</a>
@endforeach
<button type="button" data-sheet-open="adm-more"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>Menu</button>
</nav>
<dialog id="adm-more" data-sheet class="sheet" aria-label="Semua menu admin">
<div style="display: flex; flex-direction: column; gap: 4px">
<span aria-hidden="true" style="align-self: center; width: 40px; height: 4px; border-radius: 2px; background: #D9D6CF; margin-bottom: 10px"></span>
<span style="font-size: 13px; color: #4E515C; padding: 0 4px 8px">{{ $me->displayName() }} · {{ \App\Support\Akses::roleLabel($me) }}</span>
@foreach ($menu as $k => [$label, $r])
<a href="{{ route($r) }}" style="min-height: 48px; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; border-radius: 12px; text-decoration: none; font-weight: 700; background: {{ request()->routeIs($r.'*') ? '#F4F3EF' : 'transparent' }}">{{ $label }}<span aria-hidden="true">›</span></a>
@endforeach
<a href="{{ route('home') }}" style="min-height: 48px; display: flex; align-items: center; padding: 0 12px; text-decoration: none; font-weight: 600; color: #4E515C">Lihat website ↗</a>
<form method="POST" action="{{ route('admin.logout') }}" style="margin: 0">@csrf<button type="submit" style="width: 100%; min-height: 48px; text-align: left; padding: 0 12px; border: 0; background: transparent; font-family: inherit; font-weight: 700; font-size: 15px; color: #B02A19">Keluar</button></form>
<button type="button" data-sheet-close style="margin-top: 6px; min-height: 46px; border: 1px solid #E6E4DF; border-radius: 12px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px">Tutup</button>
</div>
</dialog>
@endsection
