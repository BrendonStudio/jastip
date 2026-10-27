@extends('layouts.site')
@section('title', 'Riwayat pembayaran & refund')
@section('body_class', 'bg-soft')

@php
    $ST = [
        'lunas' => ['Lunas', '#16653F', '#E6F4EC'],
        'verif' => ['Menunggu verifikasi', '#7A4B00', '#FFF0D1'],
        'shopee' => ['Menunggu checkout', '#B4410F', '#FDECE4'],
        'selesai' => ['Selesai', '#16653F', '#E6F4EC'],
        'proses' => ['Diproses', '#1A3F7F', '#EAF0FA'],
    ];
    $amtTxt = fn ($r) => $r['amt'] < 0 ? 'Lewat Shopee' : ($r['jenis'] === 'refund' ? '+' : '').rp($r['amt']);
@endphp

@section('content')
<div class="dsk">@include('partials.header', ['active' => 'akun'])</div>
<div class="mob">@include('partials.mob-header', ['title' => 'Riwayat pembayaran', 'back' => route('account')])</div>
@include('partials.flash')

<div class="pay-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; max-width: 1200px; margin: 0 auto; padding: 36px 24px 64px; display: flex; flex-direction: column; gap: 22px; box-sizing: border-box">
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div class="dsk"><div style="display: flex; flex-direction: column; gap: 6px">
<a href="{{ route('account') }}" style="font-size: 14px; font-weight: 700; color: #4E515C; text-decoration: none">← Akun saya</a>
<h1 style="margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -0.025em">Riwayat pembayaran &amp; refund</h1>
</div></div>
<div role="tablist" aria-label="Jenis transaksi" style="display: flex; background: #E9E7E1; border-radius: 12px; padding: 4px; gap: 4px">
@foreach (['semua' => 'Semua', 'bayar' => 'Pembayaran', 'refund' => 'Refund'] as $id => $l)
<a href="{{ route('history', $id === 'semua' ? [] : ['tab' => $id]) }}" role="tab" aria-selected="{{ $tab === $id ? 'true' : 'false' }}" class="seg {{ $tab === $id ? 'is-on' : '' }}" style="min-height: 40px; padding: 0 18px; display: inline-flex; align-items: center; text-decoration: none; box-sizing: border-box">{{ $l }}</a>
@endforeach
</div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px">
<div style="background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 13px; color: #C9CAD1; font-weight: 600">Sudah dibayar</span><span style="font-size: 26px; font-weight: 800">{{ rp($paid) }}</span></div>
<div style="background: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 13px; color: #4E515C; font-weight: 600">Menunggu verifikasi</span><span style="font-size: 26px; font-weight: 800; color: #7A4B00">{{ rp($verif) }}</span></div>
<div style="background: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 13px; color: #4E515C; font-weight: 600">Refund diterima</span><span style="font-size: 26px; font-weight: 800; color: #16653F">{{ rp($refunded) }}</span>@if($refundPending)<span style="font-size: 12.5px; color: #4E515C">+ {{ rp($refundPending) }} sedang diproses</span>@endif</div>
</div>

@if ($rows->isEmpty())
<section style="background: #FFFFFF; border-radius: 16px; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center">
<span aria-hidden="true" style="width: 56px; height: 56px; border-radius: 50%; background: #F0EEE9; color: #4E515C; display: flex; align-items: center; justify-content: center"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path></svg></span>
<strong style="font-size: 17px">Belum ada transaksi</strong>
<span style="font-size: 14px; color: #4E515C">Pembayaran dan refund akan tercatat di sini.</span>
</section>
@else
{{-- Desktop: tabel --}}
<section class="dsk" aria-label="Daftar transaksi" style="background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="overflow-x: auto">
<table style="width: 100%; min-width: 860px; border-collapse: collapse; font-size: 14px">
<thead><tr style="text-align: left; font-size: 12px; color: #4E515C; background: #FAFAF8">
<th scope="col" style="padding: 12px 20px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Tanggal</th>
<th scope="col" style="padding: 12px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Keterangan</th>
<th scope="col" style="padding: 12px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Pesanan</th>
<th scope="col" style="padding: 12px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Metode</th>
<th scope="col" style="padding: 12px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Status</th>
<th scope="col" style="padding: 12px 20px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Jumlah</th>
</tr></thead>
<tbody>
@foreach ($rows as $r)
@php $st = $ST[$r['st']]; @endphp
<tr>
<td style="padding: 14px 20px; border-bottom: 1px solid #F0EEE9; color: #4E515C; white-space: nowrap">{{ $r['d'] ? tgl($r['d'], 'j M Y') : '—' }}</td>
<td style="padding: 14px 12px; border-bottom: 1px solid #F0EEE9; font-weight: 700">@if($r['href'])<a href="{{ $r['href'] }}" style="text-decoration: none">{{ $r['l'] }}</a>@else{{ $r['l'] }}@endif</td>
<td style="padding: 14px 12px; border-bottom: 1px solid #F0EEE9; color: #4E515C">{{ \Illuminate\Support\Str::limit($r['ref'], 54) }}</td>
<td style="padding: 14px 12px; border-bottom: 1px solid #F0EEE9">{{ $r['m'] }}</td>
<td style="padding: 14px 12px; border-bottom: 1px solid #F0EEE9"><span style="font-size: 12px; font-weight: 700; color: {{ $st[1] }}; background: {{ $st[2] }}; padding: 4px 10px; border-radius: 999px; white-space: nowrap">{{ $st[0] }}</span></td>
<td style="padding: 14px 20px; border-bottom: 1px solid #F0EEE9; text-align: right; font-weight: 800; color: {{ $r['jenis'] === 'refund' ? '#16653F' : '#15161C' }}; white-space: nowrap">{{ $amtTxt($r) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</section>
{{-- HP: kartu --}}
<section class="mob" aria-label="Daftar transaksi">
<div style="display: flex; flex-direction: column; gap: 10px">
@foreach ($rows as $r)
@php $st = $ST[$r['st']]; @endphp
<a href="{{ $r['href'] ?? '#' }}" style="background: #FFFFFF; border-radius: 16px; padding: 14px; display: flex; flex-direction: column; gap: 6px; text-decoration: none; color: #15161C">
<span style="display: flex; justify-content: space-between; gap: 8px"><span style="font-size: 14.5px; font-weight: 800">{{ $r['l'] }}</span><span style="font-size: 14.5px; font-weight: 800; white-space: nowrap; color: {{ $r['jenis'] === 'refund' ? '#16653F' : '#15161C' }}">{{ $amtTxt($r) }}</span></span>
<span style="font-size: 12.5px; color: #4E515C">{{ \Illuminate\Support\Str::limit($r['ref'], 48) }}</span>
<span style="display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 12.5px; color: #4E515C"><span>{{ $r['d'] ? tgl($r['d'], 'j M Y') : '—' }} · {{ $r['m'] }}</span><span style="font-size: 11.5px; font-weight: 700; color: {{ $st[1] }}; background: {{ $st[2] }}; padding: 3px 9px; border-radius: 999px">{{ $st[0] }}</span></span>
</a>
@endforeach
</div>
</section>
@endif
<p style="margin: 0; font-size: 13.5px; color: #4E515C">Pembayaran tahap 2 tercatat di aplikasi Shopee. Refund dikirim ke {{ $u->refund_account ?: 'rekening refund di akunmu' }}. Ada yang tidak sesuai? <a href="https://wa.me/{{ preg_replace('/\D/', '', config('jastip.whatsapp')) }}" target="_blank" rel="noopener" style="font-weight: 700">Hubungi admin</a> · <a href="{{ route('terms') }}#k4" style="font-weight: 700">Aturan refund</a></p>
</div>
@endsection
