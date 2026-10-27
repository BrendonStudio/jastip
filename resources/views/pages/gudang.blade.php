@extends('layouts.site')
@section('title', 'Portal warehouse')
@section('body_class', 'bg-soft')

@php
    $tabs = ['tiba' => 'Menunggu tiba', 'foto' => 'Perlu foto', 'kirim' => 'Terkirim'];
    $negName = $u->wh_country ? negara($u->wh_country)['nama'] : 'Jepang & Korea';
@endphp

@section('content')
<div style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; max-width: 560px; margin: 0 auto; min-height: 100vh; background: #F4F3EF; display: flex; flex-direction: column">
<header style="background: #15161C; color: #FFFFFF; padding: 18px 16px 20px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px">
<div style="display: flex; flex-direction: column; gap: 2px"><span style="font-size: 12px; font-weight: 800; letter-spacing: 0.06em; color: #FF8A78">PORTAL WAREHOUSE</span><h1 style="margin: 0; font-size: 22px; font-weight: 800">Warehouse {{ $negName }}</h1></div>
<details class="rej-pop" style="position: relative">
<summary aria-label="Akun staf" style="width: 44px; height: 44px; border-radius: 50%; background: #2A2C35; color: #FFFFFF; font-weight: 800; font-size: 14px; display: flex; align-items: center; justify-content: center; cursor: pointer; list-style: none">{{ inisial($u->displayName()) }}</summary>
<div style="position: absolute; right: 0; top: 52px; z-index: 5; width: 220px; background: #FFFFFF; color: #15161C; border-radius: 12px; box-shadow: 0 12px 30px rgba(21,22,28,0.25); padding: 8px; display: flex; flex-direction: column">
<span style="padding: 8px 10px; font-size: 13px; color: #4E515C">{{ $u->displayName() }}<br>{{ $u->email }}</span>
@if (\App\Support\Akses::can($u, 'ringkasan'))<a href="{{ route('admin.dashboard') }}" style="min-height: 44px; display: flex; align-items: center; padding: 0 10px; text-decoration: none; font-weight: 700">Panel admin</a>@endif
<form method="POST" action="{{ route('admin.logout') }}" style="margin: 0">@csrf<button type="submit" style="width: 100%; min-height: 44px; text-align: left; padding: 0 10px; border: 0; background: transparent; font-family: inherit; font-weight: 700; font-size: 14px; color: #B02A19">Keluar</button></form>
</div>
</details>
</div>
<p style="margin: 0; font-size: 13.5px; line-height: 1.5; color: #C9CAD1">Foto yang kamu kirim dicek admin dulu sebelum penitip bisa melihatnya.</p>
</header>
@include('partials.flash')
<div style="padding: 14px 16px 0">
<div role="tablist" aria-label="Status paket" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); background: #E9E7E1; border-radius: 12px; padding: 4px; gap: 4px">
@foreach ($tabs as $id => $l)
<a href="{{ route('warehouse', ['tab' => $id]) }}" role="tab" aria-selected="{{ $tab === $id ? 'true' : 'false' }}" style="min-height: 46px; border-radius: 9px; background: {{ $tab === $id ? '#FFFFFF' : 'transparent' }}; color: #15161C; text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px"><span style="font-size: 16px; font-weight: 800">{{ $lists[$id]->count() }}</span><span style="font-size: 11.5px; font-weight: 700">{{ $l }}</span></a>
@endforeach
</div>
</div>
<main style="padding: 14px 16px 32px; display: flex; flex-direction: column; gap: 10px">
@forelse ($rows as $r)
<article style="background: #FFFFFF; border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 10px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px"><span style="font-size: 15px; font-weight: 800">{{ $r->code }}</span><span style="font-size: 12px; color: #4E515C">{{ $r->user?->displayName() }}{{ $u->wh_country ? '' : ' · '.$r->neg()['nama'] }}</span></div>
<span style="font-size: 14px; font-weight: 600">{{ $r->title() }}</span>
<dl style="margin: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; font-size: 12.5px">
<div style="background: #F7F6F2; border-radius: 10px; padding: 8px 10px"><dt style="color: #4E515C">No. order toko</dt><dd style="margin: 2px 0 0; font-weight: 700; overflow-wrap: anywhere">{{ $r->store_order_no ?: '—' }}</dd></div>
<div style="background: #F7F6F2; border-radius: 10px; padding: 8px 10px"><dt style="color: #4E515C">Resi kurir lokal</dt><dd style="margin: 2px 0 0; font-weight: 700; overflow-wrap: anywhere">{{ $r->local_resi ?: 'Belum ada' }}</dd></div>
</dl>
@if ($tab === 'tiba')
<form method="POST" action="{{ route('warehouse.arrived', $r) }}" style="margin: 0; display: flex">@csrf<button type="submit" style="flex: 1; min-height: 48px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px; color: #15161C">Tandai sudah tiba</button></form>
@elseif ($tab === 'foto')
<a href="{{ route('warehouse.show', $r) }}" style="min-height: 48px; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; text-decoration: none">Foto &amp; timbang · {{ count($r->photoList()) }}/4 foto</a>
@else
@php $ok = $r->stage >= \App\Models\Order::WAREHOUSE; @endphp
<span style="align-self: flex-start; font-size: 12.5px; font-weight: 700; color: {{ $ok ? '#16653F' : '#7A4B00' }}; background: {{ $ok ? '#E6F4EC' : '#FFF0D1' }}; padding: 5px 10px; border-radius: 999px">{{ $ok ? 'Disetujui admin' : 'Menunggu dicek admin' }}</span>
@endif
</article>
@empty
<p style="margin: 0; padding: 32px 16px; background: #FFFFFF; border-radius: 16px; text-align: center; font-size: 14px; color: #4E515C">Tidak ada paket di sini.</p>
@endforelse
</main>
</div>
@endsection
