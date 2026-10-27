@extends('layouts.site')
@section('title', 'Jasa war')
@section('body_class', 'bg-soft')

@php
    $card = function ($d) {
        $neg = $d->neg();
        [$stLabel, $stC] = $d->statusPill();
        $open = $d->isOpen();
        $full = ! $open && (int) $d->step === 0 && now()->lt($d->closesAt()) && $d->slotsLeft() === 0;
        if ($full) {
            [$stLabel, $stC] = ['Slot penuh', 'red'];
        }
        return [
            'd' => $d, 'neg' => $neg, 'st' => pill($stC) + ['label' => $stLabel], 'open' => $open, 'full' => $full,
            'infoLabel' => $open ? 'Tutup daftar' : 'Status',
            'infoVal' => $open ? $d->timeLeftLabel() : ($d->release_at->isPast() ? 'Sudah rilis' : 'Menunggu rilis'),
            'img' => upload_url($d->image),
            'harga' => $neg['sym'].angka($d->price),
        ];
    };
    $cards = $drops->map($card);
@endphp

@section('content')
{{-- ======================= DESKTOP ======================= --}}
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%">
@include('partials.header', ['dark' => true, 'active' => 'war'])
@include('partials.flash', ['style' => 'background: #15161C; padding-bottom: 12px; max-width: none'])

<section style="background: #15161C; color: #FFFFFF">
<div style="max-width: 1200px; margin: 0 auto; padding: 48px 24px 56px; display: flex; flex-wrap: wrap; gap: 40px; align-items: flex-end">
<div style="flex: 1 1 460px; display: flex; flex-direction: column; gap: 14px">
<span style="font-size: 13px; font-weight: 800; letter-spacing: 0.06em; color: #FF8A78">JASA WAR · EDISI TERBATAS</span>
<h1 style="margin: 0; font-size: 46px; line-height: 1.08; font-weight: 800; letter-spacing: -0.03em">Pop-up, MD konser, album limited? Biar kami yang war.</h1>
<p style="margin: 0; font-size: 17px; line-height: 1.6; color: #C9CAD1; max-width: 540px">Daftar dan bayar DP {{ $dp }}% sebelum rilis. Kami checkout di detik pertama barang dibuka.</p>
</div>
<ol style="flex: 1 1 420px; list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px">
<li style="background: #22242C; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 8px"><span style="font-size: 13px; font-weight: 800; color: #FF8A78">01</span><span style="font-size: 15px; font-weight: 800">Daftar &amp; DP {{ $dp }}%</span><span style="font-size: 13px; color: #C9CAD1">Sebelum pendaftaran tutup</span></li>
<li style="background: #22242C; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 8px"><span style="font-size: 13px; font-weight: 800; color: #FF8A78">02</span><span style="font-size: 15px; font-weight: 800">Kami war</span><span style="font-size: 13px; color: #C9CAD1">Tepat saat rilis, sesuai urutan</span></li>
<li style="background: #22242C; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 8px"><span style="font-size: 13px; font-weight: 800; color: #FF8A78">03</span><span style="font-size: 15px; font-weight: 800">Menang / kalah</span><span style="font-size: 13px; color: #C9CAD1">Lunasi {{ 100 - $dp }}% · atau DP kembali</span></li>
</ol>
</div>
</section>

<section aria-labelledby="jadwal" style="max-width: 1200px; margin: 0 auto; padding: 40px 24px 64px; display: flex; flex-direction: column; gap: 20px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px"><h2 id="jadwal" style="margin: 0; font-size: 26px; font-weight: 800">Jadwal war</h2><span style="font-size: 14px; font-weight: 600; color: #4E515C">Semua waktu dalam WIB</span></div>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px">
@forelse ($cards as $c)
<article style="background: #FFFFFF; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
@if ($c['img'])
<img src="{{ $c['img'] }}" alt="Foto {{ $c['d']->name }}" class="ph" style="width: 100%; aspect-ratio: 16 / 10; border-radius: 14px">
@else
<div role="img" aria-label="Foto produk" style="aspect-ratio: 16 / 10; border-radius: 14px; background: #ECEAE4"></div>
@endif
<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px">
<span style="font-size: 12px; font-weight: 700; color: {{ $c['neg']['fg'] }}; background: {{ $c['neg']['bg'] }}; padding: 5px 10px; border-radius: 999px">{{ $c['neg']['nama'] }}</span>
<span style="font-size: 12px; font-weight: 700; color: {{ $c['st']['fg'] }}; background: {{ $c['st']['bg'] }}; padding: 5px 10px; border-radius: 999px">{{ $c['st']['label'] }}</span>
</div>
<div style="display: flex; flex-direction: column; gap: 4px"><h3 style="margin: 0; font-size: 17px; font-weight: 800">{{ $c['d']->name }}</h3><span style="font-size: 13.5px; color: #4E515C">{{ $c['d']->store_name ?: 'Toko resmi' }} · {{ $c['harga'] }}</span></div>
<dl style="margin: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; font-size: 13px">
<div style="background: #F7F6F2; border-radius: 10px; padding: 10px"><dt style="color: #4E515C">Rilis</dt><dd style="margin: 2px 0 0; font-weight: 800">{{ tgl($c['d']->release_at, 'j M') }} · {{ $c['d']->release_at->format('H:i') }} WIB</dd><dd style="margin: 2px 0 0; font-size: 12px; color: #4E515C">{{ $c['d']->localRelease()->format('H:i') }} {{ $c['neg']['zona'] }}</dd></div>
<div style="background: #F7F6F2; border-radius: 10px; padding: 10px"><dt style="color: #4E515C">{{ $c['infoLabel'] }}</dt><dd style="margin: 2px 0 0; font-weight: 800">{{ $c['infoVal'] }}</dd><dd style="margin: 2px 0 0; font-size: 12px; color: #4E515C">{{ $c['d']->slotsLeft() }} slot tersisa</dd></div>
</dl>
@if ($c['open'])
<a href="{{ route('war.register', $c['d']) }}" style="min-height: 50px; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; text-decoration: none">Daftar war</a>
@elseif ($c['full'] && in_array($c['d']->id, $waiting ?? [], true))
<span style="min-height: 50px; border-radius: 12px; background: #EAF0FA; color: #1A3F7F; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 0 12px">Kamu di daftar tunggu</span>
@elseif ($c['full'])
<p style="margin: 0; font-size: 13px; line-height: 1.5; color: #4E515C">Semua {{ $c['d']->quota }} slot sudah terisi. Masuk daftar tunggu, kami kabari kalau ada slot kosong.</p>
<form method="POST" action="{{ route('war.waitlist', $c['d']) }}" style="margin: 0; display: flex">@csrf<button type="submit" style="flex: 1; min-height: 50px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; color: #15161C; font-family: inherit; font-weight: 700; font-size: 15px; cursor: pointer">Masuk daftar tunggu</button></form>
@else
<button type="button" disabled style="min-height: 50px; border: 0; border-radius: 12px; background: #E6E4DF; color: #4E515C; font-family: inherit; font-weight: 700; font-size: 15px">Pendaftaran tutup</button>
@endif
</article>
@empty
<p style="margin: 0; padding: 24px; background: #FFFFFF; border-radius: 20px; color: #4E515C">Belum ada jadwal war. Isi grup favorit di akunmu supaya kami kabari saat ada war baru.</p>
@endforelse
</div>
</section>
</div>

{{-- ======================= HP ======================= --}}
<div class="mob">
<div class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF">
<header style="background: #15161C; color: #FFFFFF; padding: 14px 16px 24px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; align-items: center; gap: 8px">
<a href="{{ route('home') }}" aria-label="Kembali ke beranda" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none; color: #FFFFFF"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></a>
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.06em; color: #FF8A78">JASA WAR</span>
</div>
<h1 style="margin: 0; font-size: 28px; line-height: 1.15; font-weight: 800; letter-spacing: -0.02em">Pop-up, MD konser, album limited? Biar kami yang war.</h1>
<p style="margin: 0; font-size: 14.5px; line-height: 1.55; color: #C9CAD1">Daftar dan bayar DP {{ $dp }}% sebelum rilis. Kami checkout di detik pertama barang dibuka.</p>
</header>
@include('partials.flash', ['style' => 'padding: 12px 16px 0'])

<main style="padding: 16px; display: flex; flex-direction: column; gap: 14px">
<ol style="list-style: none; margin: 0; padding: 14px; background: #FFFFFF; border-radius: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 13.5px; line-height: 1.45">
<li style="display: flex; gap: 10px"><span style="width: 22px; height: 22px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">1</span><span><strong>Daftar &amp; bayar DP {{ $dp }}%</strong> sebelum pendaftaran tutup</span></li>
<li style="display: flex; gap: 10px"><span style="width: 22px; height: 22px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">2</span><span><strong>Kami war</strong> tepat saat rilis, sesuai urutan daftar</span></li>
<li style="display: flex; gap: 10px"><span style="width: 22px; height: 22px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">3</span><span><strong>Menang</strong> lunasi sisa {{ 100 - $dp }}% · <strong>kalah</strong> DP dikembalikan</span></li>
</ol>

<div style="display: flex; justify-content: space-between; align-items: baseline">
<h2 style="margin: 0; font-size: 17px; font-weight: 800">Jadwal war</h2>
<span style="font-size: 12.5px; font-weight: 600; color: #4E515C">Waktu dalam WIB</span>
</div>

@forelse ($cards as $c)
<article style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px">
<span style="font-size: 12px; font-weight: 700; color: {{ $c['neg']['fg'] }}; background: {{ $c['neg']['bg'] }}; padding: 5px 10px; border-radius: 999px">{{ $c['neg']['nama'] }}</span>
<span style="font-size: 12px; font-weight: 700; color: {{ $c['st']['fg'] }}; background: {{ $c['st']['bg'] }}; padding: 5px 10px; border-radius: 999px">{{ $c['st']['label'] }}</span>
</div>
<div style="display: flex; gap: 12px">
@if ($c['img'])
<img src="{{ $c['img'] }}" alt="" class="ph" style="width: 72px; height: 72px; border-radius: 12px; flex: none">
@else
<div role="img" aria-label="Foto produk" style="width: 72px; height: 72px; border-radius: 12px; background: #ECEAE4; flex: none"></div>
@endif
<div style="display: flex; flex-direction: column; gap: 4px; min-width: 0">
<h3 style="margin: 0; font-size: 15.5px; font-weight: 800">{{ $c['d']->name }}</h3>
<span style="font-size: 13px; color: #4E515C">{{ $c['d']->store_name ?: 'Toko resmi' }} · {{ $c['harga'] }}</span>
</div>
</div>
<dl style="margin: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; font-size: 13px">
<div style="background: #F7F6F2; border-radius: 10px; padding: 10px"><dt style="color: #4E515C">Rilis</dt><dd style="margin: 2px 0 0; font-weight: 800">{{ tgl($c['d']->release_at, 'j M') }} · {{ $c['d']->release_at->format('H:i') }} WIB</dd><dd style="margin: 2px 0 0; font-size: 12px; color: #4E515C">{{ $c['d']->localRelease()->format('H:i') }} {{ $c['neg']['zona'] }}</dd></div>
<div style="background: #F7F6F2; border-radius: 10px; padding: 10px"><dt style="color: #4E515C">{{ $c['infoLabel'] }}</dt><dd style="margin: 2px 0 0; font-weight: 800">{{ $c['infoVal'] }}</dd><dd style="margin: 2px 0 0; font-size: 12px; color: #4E515C">{{ $c['d']->slotsLeft() }} slot tersisa</dd></div>
</dl>
@if ($c['open'])
<a href="{{ route('war.register', $c['d']) }}" style="min-height: 48px; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; text-decoration: none">Daftar war</a>
@elseif ($c['full'] && in_array($c['d']->id, $waiting ?? [], true))
<span style="min-height: 48px; border-radius: 12px; background: #EAF0FA; color: #1A3F7F; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 0 12px">Kamu di daftar tunggu</span>
@elseif ($c['full'])
<p style="margin: 0; font-size: 13px; line-height: 1.5; color: #4E515C">Semua {{ $c['d']->quota }} slot sudah terisi. Masuk daftar tunggu, kami kabari kalau ada slot kosong.</p>
<form method="POST" action="{{ route('war.waitlist', $c['d']) }}" style="margin: 0; display: flex">@csrf<button type="submit" style="flex: 1; min-height: 48px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; color: #15161C; font-family: inherit; font-weight: 700; font-size: 15px; cursor: pointer">Masuk daftar tunggu</button></form>
@else
<button type="button" disabled style="min-height: 48px; border: 0; border-radius: 12px; background: #E6E4DF; color: #4E515C; font-family: inherit; font-weight: 700; font-size: 15px">Pendaftaran tutup</button>
@endif
</article>
@empty
<p style="margin: 0; padding: 16px; background: #FFFFFF; border-radius: 16px; color: #4E515C; font-size: 14px">Belum ada jadwal war.</p>
@endforelse
</main>
</div>
</div>
@endsection
