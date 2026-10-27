@extends('layouts.site')
@section('title', 'Titip barang')
@section('body_class', 'bg-soft')

@php
    $inp = 'border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $neg = old('country', 'jp');
    $shopee = $u->shopee_username ? '@'.$u->shopee_username : '—';
    $imgIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><path d="m21 16-5-5-8 8"></path></svg>';
    $infoIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D4E9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex: none; margin-top: 1px"><circle cx="12" cy="12" r="9"></circle><path d="M12 8h.01M11 12h1v5h1"></path></svg>';
    $steps = ['Bayar harga barang × kurs jastip sekarang', 'Admin cek stok & checkout, bukti dikirim ke kamu', 'Ongkir & packing nanti lewat Shopee'];
@endphp

@section('content')
{{-- ======================= DESKTOP ======================= --}}
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%">
@include('partials.header', ['cta' => false])
@include('partials.flash')

<form method="POST" action="{{ route('order.store') }}" enctype="multipart/form-data" data-order-form style="max-width: 1200px; margin: 0 auto; padding: 40px 24px 64px; display: flex; flex-direction: column; gap: 24px">
@csrf
<div style="display: flex; flex-direction: column; gap: 8px">
<a href="{{ route('home') }}" style="font-size: 14px; font-weight: 700; color: #4E515C; text-decoration: none">← Beranda</a>
<h1 style="margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.025em">Titip barang</h1>
<p style="margin: 0; font-size: 16px; color: #4E515C">Kirim link album atau merch incaranmu dan bayar harga barangnya dengan kurs jastip hari ini. Ongkir &amp; packing dibayar nanti lewat Shopee.</p>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start">
<main style="flex: 999 1 560px; min-width: 0; display: flex; flex-direction: column; gap: 16px">
<div role="radiogroup" aria-label="Negara asal" style="display: flex; gap: 8px">
<label class="opt opt-jp" style="min-height: 48px; padding: 0 24px; border-radius: 12px; font-size: 15px"><input type="radio" name="country" value="jp" @checked($neg === 'jp')>Jepang (¥)</label>
<label class="opt opt-kr" style="min-height: 48px; padding: 0 24px; border-radius: 12px; font-size: 15px"><input type="radio" name="country" value="kr" @checked($neg === 'kr')>Korea (₩)</label>
</div>

<div data-items style="display: flex; flex-direction: column; gap: 16px">
@foreach (old('items', [[]]) as $it)
@include('pages._titip-item-dsk', ['it' => $it])
@endforeach
</div>
<template data-item-tpl>@include('pages._titip-item-dsk', ['it' => []])</template>

<button type="button" data-add-item style="min-height: 54px; border: 1.5px solid #15161C; border-radius: 14px; background: transparent; font-family: inherit; font-size: 15px; font-weight: 700; color: #15161C; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Tambah barang lain</button>
</main>

<aside style="flex: 1 1 320px; max-width: 400px; display: flex; flex-direction: column; gap: 16px">
<section aria-labelledby="ringkas" style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 18px">
<h2 id="ringkas" style="margin: 0; font-size: 17px; font-weight: 800">Ringkasan request</h2>
<div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #F7F6F2; border-radius: 12px">
<div aria-hidden="true" style="width: 40px; height: 40px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; flex: none">{{ inisial($u->displayName()) }}</div>
<div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px"><span style="font-size: 14px; font-weight: 800">{{ $u->displayName() }}</span><span style="font-size: 12.5px; color: #4E515C; overflow-wrap: anywhere">{{ $u->email }} · {{ $shopee }}</span></div>
<a href="{{ route('account.edit') }}" style="font-size: 13px; font-weight: 700">Ubah</a>
</div>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
<div style="display: flex; justify-content: space-between"><dt style="color: #4E515C">Negara</dt><dd style="margin: 0; font-weight: 700" data-o="negNama">Jepang</dd></div>
<div style="display: flex; justify-content: space-between"><dt style="color: #4E515C">Jumlah barang</dt><dd style="margin: 0; font-weight: 700"><span data-o="nItem">1</span> link</dd></div>
</dl>
@include('pages._titip-bayar', ['m' => false])
<ol style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14px; color: #2C2E36; border-top: 1px solid #E6E4DF; padding-top: 16px">
@foreach ($steps as $i => $t)
<li style="display: flex; gap: 10px; align-items: center"><span style="width: 24px; height: 24px; border-radius: 50%; background: #F4F3EF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ $i + 1 }}</span>{{ $t }}</li>
@endforeach
</ol>
<button type="submit" style="min-height: 54px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px; cursor: pointer">Kirim request &amp; bukti bayar</button>
</section>
<div style="background: #EAF0FA; border-radius: 16px; padding: 16px; display: flex; gap: 10px; align-items: flex-start">
{!! $infoIcon !!}
<p style="margin: 0; font-size: 14px; line-height: 1.55; color: #1A3F7F">Mau hitung kira-kira dulu? Pakai <a href="{{ route('home') }}#kalkulator" style="color: #1A3F7F; font-weight: 700">kalkulator estimasi</a> di beranda.</p>
</div>
</aside>
</div>
</form>
</div>

{{-- ======================= HP ======================= --}}
<div class="mob">
<form method="POST" action="{{ route('order.store') }}" enctype="multipart/form-data" data-order-form class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF">
@csrf
@include('partials.mob-header', ['title' => 'Titip barang'])
@include('partials.flash', ['style' => 'padding: 12px 16px 0'])

<main style="flex: 1; padding: 16px; display: flex; flex-direction: column; gap: 14px">
<div role="radiogroup" aria-label="Negara asal" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<label class="opt opt-jp" style="min-height: 48px; border-radius: 12px; font-size: 15px"><input type="radio" name="country" value="jp" @checked($neg === 'jp')>Jepang (¥)</label>
<label class="opt opt-kr" style="min-height: 48px; border-radius: 12px; font-size: 15px"><input type="radio" name="country" value="kr" @checked($neg === 'kr')>Korea (₩)</label>
</div>

<div data-items style="display: flex; flex-direction: column; gap: 14px">
@foreach (old('items', [[]]) as $it)
@include('pages._titip-item-mob', ['it' => $it])
@endforeach
</div>
<template data-item-tpl>@include('pages._titip-item-mob', ['it' => []])</template>

<button type="button" data-add-item style="min-height: 52px; border: 1.5px solid #15161C; border-radius: 12px; background: transparent; font-family: inherit; font-size: 15px; font-weight: 700; color: #15161C; display: flex; align-items: center; justify-content: center; gap: 8px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Tambah barang lain</button>

<section aria-labelledby="kontak-m" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; align-items: center; gap: 12px">
<div aria-hidden="true" style="width: 44px; height: 44px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; flex: none">{{ inisial($u->displayName()) }}</div>
<div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px">
<h2 id="kontak-m" style="margin: 0; font-size: 15px; font-weight: 800">{{ $u->displayName() }}</h2>
<span style="font-size: 13px; color: #4E515C; overflow-wrap: anywhere">{{ $u->email }} · Shopee {{ $shopee }}</span>
</div>
<a href="{{ route('account.edit') }}" style="font-size: 14px; font-weight: 700; min-height: 44px; display: flex; align-items: center">Ubah</a>
</section>

<section aria-label="Pembayaran" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<h2 style="margin: 0; font-size: 16px; font-weight: 800">Bayar sekarang</h2>
@include('pages._titip-bayar', ['m' => true])
</section>
</main>

<footer style="background: #FFFFFF; border-top: 1px solid #E6E4DF; padding: 14px 16px 22px; display: flex; flex-direction: column; gap: 12px">
<ol style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 13.5px; color: #2C2E36">
@foreach ($steps as $i => $t)
<li style="display: flex; gap: 10px; align-items: center"><span style="width: 22px; height: 22px; border-radius: 50%; background: #F4F3EF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ $i + 1 }}</span>{{ $t }}</li>
@endforeach
</ol>
<button type="submit" style="min-height: 52px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px">Kirim request &amp; bukti bayar</button>
</footer>
</form>
</div>
<script>window.HARGA = @json(\App\Support\Harga::forJs());</script>
@endsection
