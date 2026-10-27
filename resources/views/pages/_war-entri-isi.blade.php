@php
    $sisaPct = 100 - $d->dp_pct;
    $icon = fn ($bg, $stroke, $path) => '<div style="width: '.($m ? '72px' : '80px').'; height: '.($m ? '72px' : '80px').'; border-radius: 50%; background: '.$bg.'; display: flex; align-items: center; justify-content: center"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="'.$stroke.'" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$path.'</svg></div>';
    $okIcon = $icon('#E6F4EC', '#1D7A4C', '<path d="M20 6 9 17l-5-5"></path>');
    $h1 = 'margin: 0; font-size: '.($m ? '24px' : '32px').'; font-weight: 800';
    $p = 'margin: 0; font-size: '.($m ? '15px' : '16px').'; line-height: 1.6; color: #4E515C';
    $btnDark = 'display: flex; align-items: center; justify-content: center; min-height: 52px; padding: 0 24px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 16px';
    $btnLine = 'display: flex; align-items: center; justify-content: center; min-height: 52px; padding: 0 24px; border-radius: 12px; border: 1.5px solid #15161C; color: #15161C; text-decoration: none; font-weight: 700; font-size: 16px';
@endphp
<div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: {{ $m ? '16px' : '18px' }}">

@if ($e->result === null && ! $e->dp_proof)
{!! $icon('#FBEDEA', '#C2311F', '<rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path><path d="m10 14 4 3m0-3-4 3"></path>') !!}
<h1 style="{{ $h1 }}">Bukti DP belum cocok</h1>
<p style="{{ $p }}">{{ ucfirst($e->dp_reject_reason ?: 'Bukti DP perlu dikirim ulang') }}. Upload ulang bukti DP {{ rp($e->dp_amount) }} supaya antrian #{{ $e->queue_no }} tetap aman.</p>
<a href="{{ $e->payUrl() }}" style="{{ $btnDark }}; width: 100%; max-width: 320px; box-sizing: border-box; background: #C2311F">Upload ulang bukti DP</a>

@elseif ($e->result === null)
{!! $okIcon !!}
<h1 style="{{ $h1 }}">Slot kamu aman</h1>
<p style="{{ $p }}; max-width: {{ $m ? '320px' : 'none' }}">{{ $e->dp_verified_at ? 'DP sudah dicek admin' : 'DP diterima dan sedang dicek admin' }}, nomor antrian <strong style="color: #15161C">#{{ $e->queue_no }}</strong>. Kami war pada {{ tgl($d->release_at, 'j M Y') }} · {{ $d->release_at->format('H:i') }} WIB. Kalau menang, tagihan sisa {{ $sisaPct }}% dikirim lewat email dan halaman ini.</p>

@elseif ($e->result === 'menang' && $e->order_id)
{!! $okIcon !!}
<h1 style="{{ $h1 }}">Lunas, pesanan dibuat</h1>
<p style="{{ $p }}">Pelunasan sudah dikonfirmasi. Barang war kamu sekarang dilacak sebagai pesanan <strong style="color: #15161C">#{{ $e->order?->code }}</strong>.</p>
<a href="{{ route('track', ['no' => $e->order?->code]) }}" style="{{ $btnDark }}; width: 100%; max-width: 320px; box-sizing: border-box">Lacak pesanan</a>

@elseif ($e->result === 'menang')
{!! $icon('#FBEDEA', '#C2311F', '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"></path><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"></path>') !!}
<h1 style="{{ $h1 }}">Kamu menang war!</h1>
@if ($e->settle_proof)
<p style="{{ $p }}">Bukti pelunasan terkirim {{ tgl($e->settle_submitted_at, 'j M H:i') }}. Admin akan konfirmasi lalu kami lanjut proses kirim.</p>
@else
<p style="{{ $p }}">Lunasi sisa {{ $sisaPct }}% sebelum <strong style="color: #15161C">{{ tgl($e->settle_deadline, 'j M Y H:i') }} WIB</strong>. Lewat dari itu DP hangus.</p>
@endif
@endif

@if ($e->result === 'kalah')
{!! $icon('#F0EEE9', '#4E515C', '<circle cx="12" cy="12" r="9"></circle><path d="M8 15s1.5-2 4-2 4 2 4 2M9 9h.01M15 9h.01"></path>') !!}
<h1 style="{{ $h1 }}">Belum beruntung</h1>
<p style="{{ $p }}">Slot war {{ $d->name }} tidak berhasil kami dapat. DP dikembalikan {{ rp($e->refund_amount) }}{{ $d->refund_mode === 'potong' ? ' (sudah dipotong fee war)' : '' }} ke rekening refund kamu.</p>
<p style="margin: 0; font-size: 14px; font-weight: 700; color: {{ $e->refunded_at ? '#16653F' : '#7A4B00' }}">{{ $e->refunded_at ? 'Sudah ditransfer '.tgl($e->refunded_at) : 'Menunggu transfer refund' }}</p>
@endif

{{-- Rincian --}}
<dl style="width: 100%; margin: 8px 0 0; text-align: left; background: #F7F6F2; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 8px; font-size: 14px; box-sizing: border-box">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Produk</dt><dd style="margin: 0; font-weight: 700; text-align: right">{{ $d->name }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Varian</dt><dd style="margin: 0; font-weight: 700; text-align: right">{{ $e->variant_main }}{{ $e->variant_backup ? ' / cadangan '.$e->variant_backup : '' }} × {{ $e->qty }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Total</dt><dd style="margin: 0; font-weight: 700">{{ rp($e->total) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">DP {{ $d->dp_pct }}% (dibayar)</dt><dd style="margin: 0; font-weight: 700">{{ rp($e->dp_amount) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 800">Sisa {{ $sisaPct }}%</dt><dd style="margin: 0; font-weight: 800">{{ rp($e->remaining()) }}</dd></div>
</dl>

@if ($e->result === 'menang' && ! $e->settle_proof && ! $e->order_id)
@if ($e->settle_reject_reason)<p style="margin: 0; width: 100%; text-align: left; font-size: 14px; line-height: 1.5; color: #8E2213; background: #FBEDEA; border-radius: 12px; padding: 12px 14px; box-sizing: border-box"><strong>Bukti pelunasan belum cocok:</strong> {{ $e->settle_reject_reason }}. Upload ulang bukti yang benar.</p>@endif
<a href="{{ $e->payUrl() }}" style="min-height: 52px; width: 100%; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none">{{ $e->settle_reject_reason ? 'Upload ulang bukti' : 'Lunasi '.rp($e->remaining()) }}</a>
@endif

<div style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; width: 100%; {{ $m ? 'flex-direction: column; align-items: center' : '' }}">
<a href="{{ route('account') }}" style="{{ $btnDark }}; {{ $m ? 'width: 100%; max-width: 320px; box-sizing: border-box' : '' }}">Lihat di akun saya</a>
<a href="{{ route('war') }}" style="{{ $m ? 'min-height: 48px; display: flex; align-items: center; font-weight: 700; font-size: 15px; color: #15161C' : $btnLine }}">Kembali ke jadwal war</a>
</div>
</div>
