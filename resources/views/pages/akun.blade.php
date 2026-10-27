@extends('layouts.site')
@section('title', 'Akun saya')
@section('body_class', 'bg-soft')

@section('content')
@php
    $joined = tgl($u->created_at);
    $shopee = $u->shopee_username ? '@'.$u->shopee_username : '—';
@endphp

{{-- ======================= DESKTOP ======================= --}}
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%">
@include('partials.header', ['active' => 'akun'])
@include('partials.flash')

<div style="max-width: 1200px; margin: 0 auto; padding: 40px 24px 64px; display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start">
<aside style="flex: 1 1 280px; max-width: 340px; display: flex; flex-direction: column; gap: 16px">
<section aria-labelledby="prof" style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px">
<div style="display: flex; align-items: center; gap: 14px">
<div aria-hidden="true" style="width: 56px; height: 56px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; flex: none">{{ inisial($u->displayName()) }}</div>
<div style="display: flex; flex-direction: column; gap: 2px; min-width: 0"><h1 id="prof" style="margin: 0; font-size: 19px; font-weight: 800">{{ $u->displayName() }}</h1><span style="font-size: 13px; color: #4E515C">Bergabung {{ $joined }}</span></div>
</div>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Email</dt><dd style="margin: 0; font-weight: 700; overflow-wrap: anywhere; text-align: right">{{ $u->email }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Shopee</dt><dd style="margin: 0; font-weight: 700">{{ $shopee }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Saldo refund</dt><dd style="margin: 0; font-weight: 700">{{ rp($saldoRefund) }}</dd></div>
</dl>
<a href="{{ route('account.edit') }}" style="display: flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 10px; border: 1.5px solid #CFCDC6; text-decoration: none; font-weight: 700; font-size: 14px">Ubah data</a>
</section>
<nav aria-label="Pengaturan akun" style="background: #FFFFFF; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden">
<a href="{{ route('history') }}" style="display: flex; justify-content: space-between; align-items: center; min-height: 52px; padding: 0 20px; text-decoration: none; font-size: 14.5px; font-weight: 600; border-bottom: 1px solid #F0EEE9">Riwayat pembayaran &amp; refund<span aria-hidden="true">›</span></a>
<form method="POST" action="{{ route('account.warinfo') }}" style="margin: 0; border-bottom: 1px solid #F0EEE9">@csrf<button type="submit" style="width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 8px; min-height: 52px; padding: 0 20px; border: 0; background: transparent; font-family: inherit; font-size: 14.5px; font-weight: 600; color: #15161C; cursor: pointer; text-align: left">Info war &amp; rilisan baru<span style="font-size: 12px; font-weight: 700; color: {{ $u->notify_war ? '#16653F' : '#4E515C' }}; background: {{ $u->notify_war ? '#E6F4EC' : '#F0EEE9' }}; padding: 4px 10px; border-radius: 999px">{{ $u->notify_war ? 'Aktif' : 'Mati' }}</span></button></form>
<a href="{{ route('home') }}#kontak" style="display: flex; justify-content: space-between; align-items: center; min-height: 52px; padding: 0 20px; text-decoration: none; font-size: 14.5px; font-weight: 600; border-bottom: 1px solid #F0EEE9">Hubungi admin<span aria-hidden="true">›</span></a>
<form method="POST" action="{{ route('logout') }}" style="margin: 0">@csrf<button type="submit" style="width: 100%; display: flex; align-items: center; min-height: 52px; padding: 0 20px; border: 0; background: transparent; font-family: inherit; font-size: 14.5px; font-weight: 700; color: #B02A19; cursor: pointer">Keluar</button></form>
</nav>
</aside>

<main style="flex: 999 1 560px; min-width: 0; display: flex; flex-direction: column; gap: 20px">
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px">
<div style="background: #FFFFFF; border-radius: 16px; padding: 18px; border-top: 4px solid #C2311F; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 28px; font-weight: 800">{{ $nBayar }}</span><span style="font-size: 14px; font-weight: 700">Perlu dibayar</span></div>
<div style="background: #FFFFFF; border-radius: 16px; padding: 18px; border-top: 4px solid #1D4E9E; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 28px; font-weight: 800">{{ $nProses }}</span><span style="font-size: 14px; font-weight: 700">Sedang diproses</span></div>
<div style="background: #FFFFFF; border-radius: 16px; padding: 18px; border-top: 4px solid #15161C; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 28px; font-weight: 800">{{ $nWar }}</span><span style="font-size: 14px; font-weight: 700">War aktif</span></div>
</div>

<section data-tabs aria-labelledby="pes-title" style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap">
<h2 id="pes-title" style="margin: 0; font-size: 20px; font-weight: 800">Pesanan saya</h2>
<div role="tablist" aria-label="Pesanan" style="display: flex; background: #F0EEE9; border-radius: 12px; padding: 4px; gap: 4px">
<button type="button" role="tab" data-tab="aktif" aria-selected="true" class="seg is-on" style="min-height: 40px; padding: 0 18px">Aktif ({{ $aktif->count() }})</button>
<button type="button" role="tab" data-tab="selesai" aria-selected="false" class="seg" style="min-height: 40px; padding: 0 18px">Selesai</button>
</div>
</div>
<div data-pane="aktif" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px">
@forelse ($aktif as $c)
@include('partials.order-card', ['c' => $c, 'm' => false])
@empty
@if ($selesai->isEmpty())
@include('partials.empty', ['bg' => '#F7F6F2', 'title' => 'Belum ada titipan', 'text' => 'Album atau merch incaranmu belum ada di sini. Kirim link produknya, kami hitungkan harganya.', 'cta' => 'Titip barang pertama', 'href' => route('order.create')])
@else
<p style="margin: 0; padding: 20px; border-radius: 14px; background: #F7F6F2; color: #4E515C; font-size: 14.5px">Belum ada pesanan aktif. <a href="{{ route('order.create') }}" style="font-weight: 700">Titip lagi</a></p>
@endif
@endforelse
</div>
<div data-pane="selesai" hidden style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px">
@forelse ($selesai as $c)
@include('partials.order-card', ['c' => $c, 'm' => false])
@empty
<p style="margin: 0; padding: 20px; border-radius: 14px; background: #F7F6F2; color: #4E515C; font-size: 14.5px">Belum ada pesanan selesai.</p>
@endforelse
</div>
</section>

</main>
</div>
</div>

{{-- ======================= HP ======================= --}}
<div class="mob">
<div class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF">
<header style="background: #FFFFFF; padding: 14px 16px 18px; display: flex; flex-direction: column; gap: 14px; border-bottom: 1px solid #E6E4DF">
<div style="display: flex; align-items: center; gap: 8px">
<a href="{{ route('home') }}" aria-label="Kembali ke beranda" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></a>
<h1 style="margin: 0; font-size: 18px; font-weight: 800">Akun saya</h1>
</div>
<div style="display: flex; align-items: center; gap: 12px">
<div aria-hidden="true" style="width: 52px; height: 52px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; flex: none">{{ inisial($u->displayName()) }}</div>
<div style="display: flex; flex-direction: column; gap: 2px; min-width: 0"><span style="font-size: 17px; font-weight: 800">{{ $u->displayName() }}</span><span style="font-size: 13px; color: #4E515C; overflow-wrap: anywhere">{{ $u->email }} · {{ $shopee }}</span></div>
</div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<a href="{{ route('order.create') }}" style="display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 14px">Titip barang</a>
<a href="{{ route('war') }}" style="display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 14px">Jadwal war</a>
</div>
</header>
@include('partials.flash', ['style' => 'padding: 12px 16px 0'])

<main data-tabs style="flex: 1; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<div role="tablist" aria-label="Pesanan" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); background: #E9E7E1; border-radius: 12px; padding: 4px; gap: 4px">
<button type="button" role="tab" data-tab="aktif" aria-selected="true" class="seg is-on" style="min-height: 42px">Aktif ({{ $aktif->count() }})</button>
<button type="button" role="tab" data-tab="selesai" aria-selected="false" class="seg" style="min-height: 42px">Selesai</button>
</div>
<div data-pane="aktif" style="display: flex; flex-direction: column; gap: 12px">
@forelse ($aktif as $c)
@include('partials.order-card', ['c' => $c, 'm' => true])
@empty
@if ($selesai->isEmpty())
@include('partials.empty', ['title' => 'Belum ada titipan', 'text' => 'Album atau merch incaranmu belum ada di sini. Kirim link produknya, kami hitungkan harganya.', 'cta' => 'Titip barang pertama', 'href' => route('order.create')])
@else
<p style="margin: 0; padding: 16px; border-radius: 14px; background: #FFFFFF; color: #4E515C; font-size: 14px">Belum ada pesanan aktif.</p>
@endif
@endforelse
</div>
<div data-pane="selesai" hidden style="display: flex; flex-direction: column; gap: 12px">
@forelse ($selesai as $c)
@include('partials.order-card', ['c' => $c, 'm' => true])
@empty
<p style="margin: 0; padding: 16px; border-radius: 14px; background: #FFFFFF; color: #4E515C; font-size: 14px">Belum ada pesanan selesai.</p>
@endforelse
</div>

<nav aria-label="Pengaturan akun" style="margin-top: 6px; background: #FFFFFF; border-radius: 16px; display: flex; flex-direction: column">
<a href="{{ route('history') }}" style="display: flex; justify-content: space-between; align-items: center; min-height: 52px; padding: 0 16px; text-decoration: none; font-size: 14.5px; font-weight: 600; border-bottom: 1px solid #F0EEE9">Riwayat pembayaran &amp; refund<span aria-hidden="true">›</span></a>
<a href="{{ route('account.edit') }}" style="display: flex; justify-content: space-between; align-items: center; min-height: 52px; padding: 0 16px; text-decoration: none; font-size: 14.5px; font-weight: 600; border-bottom: 1px solid #F0EEE9">Data Shopee &amp; rekening refund<span aria-hidden="true">›</span></a>
<form method="POST" action="{{ route('account.warinfo') }}" style="margin: 0; border-bottom: 1px solid #F0EEE9">@csrf<button type="submit" style="width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 8px; min-height: 52px; padding: 0 16px; border: 0; background: transparent; font-family: inherit; font-size: 14.5px; font-weight: 600; color: #15161C; cursor: pointer; text-align: left">Info war &amp; rilisan baru<span style="font-size: 12px; font-weight: 700; color: {{ $u->notify_war ? '#16653F' : '#4E515C' }}; background: {{ $u->notify_war ? '#E6F4EC' : '#F0EEE9' }}; padding: 4px 10px; border-radius: 999px">{{ $u->notify_war ? 'Aktif' : 'Mati' }}</span></button></form>
<a href="https://wa.me/{{ preg_replace('/\D/', '', config('jastip.whatsapp')) }}" target="_blank" rel="noopener" style="display: flex; justify-content: space-between; align-items: center; min-height: 52px; padding: 0 16px; text-decoration: none; font-size: 14.5px; font-weight: 600; border-bottom: 1px solid #F0EEE9">Hubungi admin<span aria-hidden="true">›</span></a>
<form method="POST" action="{{ route('logout') }}" style="margin: 0">@csrf<button type="submit" style="width: 100%; display: flex; align-items: center; min-height: 52px; padding: 0 16px; border: 0; background: transparent; font-family: inherit; font-size: 14.5px; font-weight: 700; color: #B02A19; cursor: pointer">Keluar</button></form>
</nav>

</main>
</div>
</div>
@endsection
