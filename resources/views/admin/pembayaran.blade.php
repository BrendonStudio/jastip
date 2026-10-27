@extends('layouts.admin')
@section('title', 'Admin · Pembayaran & refund')

@php
    $ST = [
        'verif' => ['Menunggu verifikasi', '#7A4B00', '#FFF0D1'],
        'lunas' => ['Lunas', '#16653F', '#E6F4EC'],
        'tolak' => ['Ditolak · minta ulang', '#B02A19', '#FBEDEA'],
    ];
    $colsIn = 'minmax(90px, 0.6fr) minmax(170px, 1.2fr) minmax(190px, 1.4fr) minmax(120px, 0.9fr) 72px minmax(150px, 1fr) minmax(190px, 1.2fr)';
    $colsRf = 'minmax(170px, 1.2fr) minmax(200px, 1.4fr) minmax(200px, 1.4fr) minmax(120px, 0.8fr) minmax(230px, 1.3fr)';
    $btnS = 'min-height: 38px; padding: 0 14px; border-radius: 10px; font-family: inherit; font-weight: 700; font-size: 13px; cursor: pointer';
@endphp

@section('main')
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Pembayaran &amp; refund</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Cocokkan transfer yang masuk dengan mutasi rekening, lalu catat refund yang sudah dikirim.</p>
</div>
<div role="tablist" aria-label="Jenis" style="display: flex; background: #E9E7E1; border-radius: 12px; padding: 4px; gap: 4px">
@foreach (['masuk' => 'Pembayaran masuk · '.$nVerif, 'refund' => 'Refund · '.$nRefund] as $id => $l)
<a href="{{ route('admin.payments', $id === 'refund' ? ['tab' => 'refund'] : []) }}" role="tab" aria-selected="{{ $tab === $id ? 'true' : 'false' }}" class="seg {{ $tab === $id ? 'is-on' : '' }}" style="min-height: 40px; padding: 0 18px; display: inline-flex; align-items: center; text-decoration: none; box-sizing: border-box">{{ $l }}</a>
@endforeach
</div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px">
<a href="{{ route('admin.payments') }}" style="background: #FFFFFF; border: 1.5px solid #F2D9A8; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; gap: 4px; text-decoration: none"><span style="font-size: 13px; font-weight: 700; color: #7A4B00">Menunggu verifikasi</span><span style="font-size: 26px; font-weight: 800">{{ $nVerif }} transfer</span><span style="font-size: 13px; color: #4E515C">{{ rp($sumVerif) }}</span></a>
<a href="{{ route('admin.payments', ['tab' => 'refund']) }}" style="background: #FFFFFF; border: 1.5px solid #F3D3CD; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; gap: 4px; text-decoration: none"><span style="font-size: 13px; font-weight: 700; color: #B02A19">Refund perlu dikirim</span><span style="font-size: 26px; font-weight: 800">{{ $nRefund }} refund</span><span style="font-size: 13px; color: #4E515C">{{ rp($sumRefund) }}</span></a>
<div style="background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 16px 18px; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 13px; font-weight: 700; color: #C9CAD1">Uang masuk hari ini</span><span style="font-size: 26px; font-weight: 800">{{ rp($sumMasuk) }}</span><span style="font-size: 13px; color: #C9CAD1">Sudah terverifikasi</span></div>
</div>

@if ($tab === 'masuk')
<section aria-label="Pembayaran masuk" style="background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="overflow-x: auto"><div style="min-width: 1000px">
<div style="background: #FAFAF8; border-bottom: 1px solid #E6E4DF; display: grid; grid-template-columns: {{ $colsIn }}; gap: 14px; padding: 12px 20px; font-size: 12px; font-weight: 700; color: #4E515C"><span>Waktu</span><span>Penitip</span><span>Untuk</span><span>Jumlah</span><span>Bukti</span><span>Status</span><span>Aksi</span></div>
@forelse ($masuk as $r)
@php $st = $ST[$r['st']]; @endphp
<div style="display: grid; grid-template-columns: {{ $colsIn }}; gap: 14px; align-items: center; padding: 12px 20px; border-bottom: 1px solid #F0EEE9; font-size: 14px; background: {{ $r['st'] === 'verif' ? '#FFFDF7' : '#FFFFFF' }}">
<span style="color: #4E515C; font-size: 13px">{{ yg_lalu($r['at']) }}</span>
<span style="display: flex; gap: 10px; align-items: center; min-width: 0"><span aria-hidden="true" style="width: 32px; height: 32px; border-radius: 50%; background: {{ avatar_bg($r['nama']) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ inisial($r['nama']) }}</span><span style="display: flex; flex-direction: column; min-width: 0"><span style="font-weight: 700">{{ $r['nama'] }}</span>@if($r['payer'])<span style="font-size: 12px; color: #4E515C; overflow: hidden; text-overflow: ellipsis; white-space: nowrap">a.n. {{ $r['payer'] }}</span>@endif</span></span>
<span style="display: flex; flex-direction: column; gap: 2px"><span style="font-weight: 700">{{ $r['untuk'] }}</span><span style="font-size: 12px; color: #4E515C"><a href="{{ $r['kind'] === 'order' ? route('admin.orders', ['id' => $r['model']->id]) : route('admin.orders', ['tab' => 'war', 'war' => $r['model']->id]) }}" style="color: #4E515C">{{ $r['id'] }}</a> · {{ $r['m'] }}</span></span>
<span style="font-weight: 800">{{ rp($r['amt']) }}</span>
<span>@if ($r['bukti'])<a href="{{ upload_url($r['bukti']) }}" target="_blank" aria-label="Lihat bukti {{ $r['id'] }}" style="display: block; width: 48px; height: 48px; border-radius: 8px; border: 1px solid #E6E4DF; overflow: hidden; background: #ECEAE4"><img src="{{ upload_url($r['bukti']) }}" alt="" style="width: 100%; height: 100%; object-fit: cover"></a>@else<span style="font-size: 12px; color: #767884">—</span>@endif</span>
<span><span style="font-size: 12px; font-weight: 700; color: {{ $st[1] }}; background: {{ $st[2] }}; padding: 4px 10px; border-radius: 999px; white-space: nowrap">{{ $st[0] }}</span></span>
<span style="display: flex; gap: 6px; align-items: flex-start; flex-wrap: wrap">
@if ($r['st'] === 'verif')
@php
    $okUrl = match ($r['kind']) { 'order' => route('admin.orders.paid', $r['model']), 'dp' => route('admin.payments.dp', $r['model']), default => route('admin.war.settled', $r['model']) };
    $noUrl = match ($r['kind']) { 'order' => route('admin.orders.reject', $r['model']), 'dp' => route('admin.payments.dp.reject', $r['model']), default => route('admin.payments.settle.reject', $r['model']) };
@endphp
<form method="POST" action="{{ $okUrl }}" style="margin: 0">@csrf<input type="hidden" name="kembali" value="bayar"><button type="submit" data-confirm="{{ $r['id'] }}: {{ rp($r['amt']) }} sudah masuk di mutasi rekening?" data-confirm-ok="Ya, sudah masuk" style="{{ $btnS }}; border: 0; background: #1D7A4C; color: #FFFFFF">Lunas</button></form>
<details class="rej-pop" style="position: relative">
<summary style="{{ $btnS }}; border: 1.5px solid #CFCDC6; background: #FFFFFF; color: #15161C; display: inline-flex; align-items: center; list-style: none">Tolak</summary>
<form method="POST" action="{{ $noUrl }}" style="position: absolute; right: 0; top: 44px; z-index: 5; width: 280px; background: #FFFFFF; border: 1px solid #E6E4DF; border-radius: 12px; box-shadow: 0 12px 30px rgba(21,22,28,0.15); padding: 12px; display: flex; flex-direction: column; gap: 8px">@csrf<input type="hidden" name="kembali" value="bayar">
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; font-weight: 600; color: #4E515C">Alasan (dikirim ke penitip)<input type="text" name="reason" required maxlength="180" list="alasan-tolak" placeholder="Mis. nominal belum sesuai" style="height: 40px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 10px; font-family: inherit; font-size: 14px"></label>
<button type="submit" style="{{ $btnS }}; border: 0; background: #15161C; color: #FFFFFF">Tolak &amp; minta upload ulang</button>
</form>
</details>
@else
<span style="font-size: 12.5px; color: #4E515C">{{ $r['st'] === 'lunas' ? ($r['done_at'] ? 'Diverifikasi '.tgl($r['done_at'], 'j M H:i') : 'Diverifikasi') : ($r['reason'] ?: 'Penitip diminta upload ulang') }}</span>
@endif
</span>
</div>
@empty
@include('partials.empty', ['icon' => 'card', 'title' => 'Belum ada pembayaran masuk', 'text' => 'Bukti transfer dari penitip akan muncul di sini untuk kamu cocokkan.'])
@endforelse
</div></div>
</section>
<datalist id="alasan-tolak"><option value="Nominal di bukti belum sesuai tagihan"><option value="Transfer belum masuk ke rekening kami"><option value="Bukti tidak terbaca, upload yang lebih jelas"></datalist>
<p style="margin: 0; font-size: 13px; color: #4E515C">Saat ditandai lunas, pesanan otomatis pindah ke langkah berikutnya dan penitip dapat email. Saat ditolak, penitip diminta upload ulang bukti beserta alasannya.</p>
@else
<section aria-label="Refund" style="background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="overflow-x: auto"><div style="min-width: 960px">
<div style="background: #FAFAF8; border-bottom: 1px solid #E6E4DF; display: grid; grid-template-columns: {{ $colsRf }}; gap: 14px; padding: 12px 20px; font-size: 12px; font-weight: 700; color: #4E515C"><span>Penitip</span><span>Alasan</span><span>Rekening tujuan</span><span>Jumlah</span><span>Aksi</span></div>
@forelse ($refunds as $r)
<div style="display: grid; grid-template-columns: {{ $colsRf }}; gap: 14px; align-items: center; padding: 14px 20px; border-bottom: 1px solid #F0EEE9; font-size: 14px">
<span style="display: flex; gap: 10px; align-items: center"><span aria-hidden="true" style="width: 32px; height: 32px; border-radius: 50%; background: {{ avatar_bg($r['nama']) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ inisial($r['nama']) }}</span><span style="font-weight: 700">{{ $r['nama'] }}</span></span>
<span style="display: flex; flex-direction: column; gap: 2px"><span style="font-weight: 700">{{ $r['alasan'] }}</span><span style="font-size: 12px; color: #4E515C">{{ $r['id'] }} · {{ $r['rinci'] }}</span></span>
<span style="display: flex; flex-direction: column; gap: 2px">@if ($r['user']?->refund_account)<span style="font-weight: 700">{{ $r['user']->refund_account }}</span><span style="font-size: 12px; color: #4E515C">a.n. {{ $r['nama'] }}</span>@else<span style="font-weight: 700; color: #B02A19">Belum diisi</span><span style="font-size: 12px; color: #4E515C">Minta penitip isi di Akun saya{{ $r['user']?->whatsapp ? ' · WA '.$r['user']->whatsapp : '' }}</span>@endif</span>
<span style="font-weight: 800">{{ rp($r['amt']) }}</span>
<span>
@if (! $r['done'])
<form method="POST" action="{{ route('admin.payments.refund', [$r['kind'], $r['model']->id]) }}" enctype="multipart/form-data" style="margin: 0; display: flex; gap: 6px; align-items: center; flex-wrap: wrap">@csrf
<button type="submit" data-confirm="Refund {{ rp($r['amt']) }} ke {{ $r['nama'] }} sudah ditransfer?" data-confirm-ok="Ya, sudah ditransfer" style="{{ $btnS }}; border: 0; background: #15161C; color: #FFFFFF">Tandai sudah ditransfer</button>
<label class="drop" style="min-height: 38px; padding: 0 10px; display: inline-flex; align-items: center; font-weight: 700; font-size: 13px; color: #1D4E9E; cursor: pointer"><input type="file" name="proof" data-file accept="image/*"><span data-file-name data-empty="+ Bukti">+ Bukti</span></label>
</form>
@else
<span style="display: inline-flex; gap: 8px; align-items: center; flex-wrap: wrap"><span style="font-size: 12px; font-weight: 700; color: #16653F; background: #E6F4EC; padding: 4px 10px; border-radius: 999px">Selesai · penitip dikabari</span>@if($r['proof'])<a href="{{ upload_url($r['proof']) }}" target="_blank" style="font-size: 12.5px; font-weight: 700">bukti</a>@endif</span>
@endif
</span>
</div>
@empty
@include('partials.empty', ['icon' => 'card', 'title' => 'Tidak ada refund', 'text' => 'Refund DP war yang kalah dan refund pesanan akan muncul di sini.'])
@endforelse
</div></div>
</section>
@endif
@endsection
