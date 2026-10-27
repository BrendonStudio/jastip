@extends('layouts.site')
@section('body_class', 'bg-soft')

@php
    $isOrder = $kind === 'order';
    if ($isOrder) {
        $neg = $o->neg();
        $sym = $neg['sym'];
        $ref = $o->code.' · '.$neg['nama'];
        $back = route('account');
        $titles = ['penawaran' => 'Penawaran harga', 'tolak' => 'Tolak penawaran', 'metode' => 'Pembayaran', 'upload' => 'Upload bukti transfer', 'menunggu' => 'Menunggu verifikasi', 'lunas' => 'Pembayaran lunas', 'hangus' => 'Penawaran hangus', 'tunggu' => 'Menunggu penawaran', 'batal' => 'Pesanan dibatalkan'];
        $reject = $o->payment_rejected_at ? $o->payment_reject_reason : null;
        $progL = ['Penawaran', 'Bayar', 'Verifikasi'];
        $method = $o->pay_method;
    } else {
        $d = $e->drop;
        $neg = $d->neg();
        $sym = $neg['sym'];
        $ref = $e->code().' · war '.$neg['nama'];
        $back = route('war.entry', $e);
        $isDp = $kind === 'dp';
        $titles = ['penawaran' => $isDp ? 'Bayar DP war' : 'Pelunasan war', 'metode' => 'Pembayaran', 'upload' => 'Upload bukti transfer', 'menunggu' => 'Menunggu verifikasi', 'lunas' => 'Pembayaran lunas', 'kalah' => 'Hasil war'];
        $reject = $isDp ? $e->dp_reject_reason : $e->settle_reject_reason;
        $progL = ['Tagihan', 'Bayar', 'Verifikasi'];
        $method = $isDp ? $e->dp_method : $e->settle_method;
    }
    $title = $titles[$state] ?? 'Pembayaran';
    $idx = ['penawaran' => 0, 'tolak' => 0, 'hangus' => 0, 'tunggu' => 0, 'batal' => 0, 'kalah' => 0, 'metode' => 1, 'upload' => 1, 'menunggu' => 2, 'lunas' => 3][$state] ?? 0;
    $live = in_array($state, ['penawaran', 'metode', 'upload', 'tolak'], true);
    $url = fn ($langkah, $extra = []) => $selfUrl.'?'.http_build_query(array_merge(['langkah' => $langkah], $extra));
    $card = 'background: #FFFFFF; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 18px';
    $btnRed = 'min-height: 52px; padding: 0 22px; border: 0; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; box-sizing: border-box';
    $btnInk = 'min-height: 52px; padding: 0 22px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; box-sizing: border-box';
    $btnLine = 'min-height: 52px; padding: 0 20px; border: 1.5px solid #CFCDC6; border-radius: 12px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px; color: #15161C; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; box-sizing: border-box';
    $in = 'height: 48px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF; min-width: 0; width: 100%; box-sizing: border-box';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $rek = config('jastip.rekening');
    $rekNo = preg_match('/\d[\d\s\-]{5,}\d/', $rek, $mm) ? preg_replace('/\D/', '', $mm[0]) : $rek;
@endphp
@section('title', $title)

@section('content')
<div class="dsk">@include('partials.header', ['active' => 'akun'])</div>
<div class="mob">@include('partials.mob-header', ['title' => $title, 'back' => $back])</div>
@include('partials.flash')

<div class="pay-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; max-width: 1100px; margin: 0 auto; padding: 36px 24px 64px; display: flex; flex-direction: column; gap: 22px; box-sizing: border-box">
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<a href="{{ $back }}" class="dsk" style="font-size: 14px; font-weight: 700; color: #4E515C; text-decoration: none">← {{ $isOrder ? 'Akun saya' : 'Status war' }}</a>
<h1 class="dsk" style="margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -0.025em">{{ $title }}</h1>
<span style="font-size: 14px; color: #4E515C">{{ $isOrder ? 'Pesanan' : 'Pendaftaran' }} {{ $ref }}</span>
</div>
<ol aria-label="Langkah pembayaran" style="list-style: none; margin: 0; padding: 0; display: flex; gap: 8px; flex-wrap: wrap">
@foreach ($progL as $i => $l)
@php $done = $i < $idx; $now = $i === $idx; @endphp
<li @if($now) aria-current="step" @endif style="display: flex; align-items: center; gap: 8px; min-height: 36px; padding: 0 14px 0 6px; border-radius: 999px; background: {{ $now ? '#FFFFFF' : 'transparent' }}; font-size: 13px; font-weight: 700; color: {{ $i <= $idx ? '#15161C' : '#767884' }}"><span style="width: 24px; height: 24px; border-radius: 50%; background: {{ $done ? '#1D7A4C' : ($now ? '#15161C' : '#E6E4DF') }}; color: {{ $i <= $idx ? '#FFFFFF' : '#4E515C' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800">{{ $done ? '✓' : $i + 1 }}</span>{{ $l }}</li>
@endforeach
</ol>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start">
<main class="pay-main" style="flex: 999 1 520px; min-width: 0; {{ $card }}">

@if ($reject && $live)
<div role="alert" style="display: flex; gap: 12px; align-items: flex-start; background: #FBEDEA; border-radius: 14px; padding: 14px 16px; color: #8E2213">
<span aria-hidden="true" style="width: 36px; height: 36px; border-radius: 50%; background: #FFFFFF; display: flex; align-items: center; justify-content: center; flex: none"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path><path d="m10 14 4 3m0-3-4 3"></path></svg></span>
<span style="display: flex; flex-direction: column; gap: 2px; font-size: 14px; line-height: 1.5"><strong style="font-size: 15px">Bukti transfer belum cocok</strong>{{ ucfirst($reject) }}. Upload ulang bukti yang benar atau <a href="https://wa.me/{{ preg_replace('/\D/', '', config('jastip.whatsapp')) }}" target="_blank" rel="noopener" style="color: #8E2213; font-weight: 700">hubungi admin</a>.</span>
</div>
@endif

{{-- ---------- Rincian penawaran / tagihan ---------- --}}
@if ($state === 'penawaran')
<h2 style="margin: 0; font-size: 20px; font-weight: 800">{{ $isOrder ? 'Rincian penawaran' : 'Rincian tagihan' }}</h2>
<div style="display: flex; flex-direction: column">
@if ($isOrder)
@foreach ($o->items as $it)
@php $ini = mb_strtoupper(mb_substr(preg_replace('/[^\pL\pN]/u', '', $it->name), 0, 2)); @endphp
<div style="display: flex; justify-content: space-between; gap: 12px; padding: 14px 0; border-bottom: 1px solid #F0EEE9">
<div style="display: flex; gap: 14px; align-items: center; min-width: 0">
@if ($it->screenshot)<img src="{{ upload_url($it->screenshot) }}" alt="" class="ph" style="width: 52px; height: 52px; border-radius: 12px; flex: none">@else<span aria-hidden="true" style="width: 52px; height: 52px; border-radius: 12px; background: #FCE4EC; color: #A3154A; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none">{{ $ini }}</span>@endif
<span style="display: flex; flex-direction: column; gap: 3px; min-width: 0"><span style="font-size: 15px; font-weight: 700">{{ $it->name }}</span><span style="font-size: 13px; color: #4E515C">{{ collect([$it->variant, '× '.$it->qty])->filter()->implode(' · ') }} · {{ $sym }}{{ angka(($it->web_price ?? 0) * $it->qty) }}</span></span>
</div>
<span style="font-size: 15px; font-weight: 800; white-space: nowrap">{{ rp(($it->web_price ?? 0) * $it->qty * $o->kurs_jual) }}</span>
</div>
@endforeach
@else
<div style="display: flex; justify-content: space-between; gap: 12px; padding: 14px 0; border-bottom: 1px solid #F0EEE9">
<div style="display: flex; gap: 14px; align-items: center; min-width: 0">
@if ($d->image)<img src="{{ upload_url($d->image) }}" alt="" class="ph" style="width: 52px; height: 52px; border-radius: 12px; flex: none">@else<span aria-hidden="true" style="width: 52px; height: 52px; border-radius: 12px; background: #FCE4EC; color: #A3154A; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex: none">{{ mb_strtoupper(mb_substr($d->group_name ?: $d->name, 0, 2)) }}</span>@endif
<span style="display: flex; flex-direction: column; gap: 3px; min-width: 0"><span style="font-size: 15px; font-weight: 700">{{ $d->name }}</span><span style="font-size: 13px; color: #4E515C">{{ $e->variant_main }} × {{ $e->qty }} · antrian #{{ $e->queue_no }}</span></span>
</div>
<span style="font-size: 15px; font-weight: 800; white-space: nowrap">{{ rp($e->total) }}</span>
</div>
@endif
</div>
<div style="background: #EAF0FA; border-radius: 14px; padding: 14px 16px; font-size: 14px; line-height: 1.5; color: #1A3F7F">Ongkir &amp; packing <strong>belum</strong> termasuk. Keduanya dibayar nanti lewat Shopee setelah barang ditimbang dan tiba di Indonesia.</div>
<div style="display: flex; gap: 10px; flex-wrap: wrap">
<a href="{{ $url('metode') }}" style="{{ $btnRed }}; flex: 1 1 220px">{{ $isOrder ? 'Setujui & bayar' : 'Bayar '.rp($amount) }}</a>
@if ($isOrder)<a href="{{ $url('tolak') }}" style="{{ $btnLine }}; flex: 0 1 200px">Tolak penawaran</a>@endif
</div>
@endif

{{-- ---------- Pilih cara bayar ---------- --}}
@if ($state === 'metode')
<h2 style="margin: 0; font-size: 20px; font-weight: 800">Pilih cara bayar</h2>
<div role="radiogroup" aria-label="Metode pembayaran" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px">
@foreach (array_filter(['transfer' => ['Transfer bank', 'Upload bukti, diverifikasi admin'], 'qris' => $qris ? ['QRIS', 'Semua bank & e-wallet'] : null]) as $id => [$l, $sub])
@php $on = $metode === $id; @endphp
<a href="{{ $url('metode', ['metode' => $id]) }}" role="radio" aria-checked="{{ $on ? 'true' : 'false' }}" style="text-align: left; border: 2px solid {{ $on ? '#1D4E9E' : '#E6E4DF' }}; background: #FFFFFF; border-radius: 14px; padding: 16px; color: #15161C; text-decoration: none; display: flex; gap: 12px; align-items: center">
<span aria-hidden="true" style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid {{ $on ? '#1D4E9E' : '#E6E4DF' }}; box-sizing: border-box; display: flex; align-items: center; justify-content: center; flex: none"><span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $on ? '#1D4E9E' : 'transparent' }}"></span></span>
<span style="display: flex; flex-direction: column; gap: 2px"><span style="font-size: 15.5px; font-weight: 800">{{ $l }}</span><span style="font-size: 13px; color: #4E515C">{{ $sub }}</span></span>
</a>
@endforeach
</div>
@if ($metode === 'transfer')
<div style="border: 1px solid #E6E4DF; border-radius: 14px; padding: 18px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 3px; min-width: 0"><span style="font-size: 12.5px; font-weight: 700; color: #4E515C">Transfer ke</span><span style="font-size: 19px; font-weight: 800; overflow-wrap: anywhere">{{ $rek }}</span><span style="font-size: 13.5px; color: #4E515C">Transfer tepat {{ rp($amount) }}</span></div>
<button type="button" data-copy="{{ $rekNo }}" style="min-height: 44px; padding: 0 18px; border: 1.5px solid #15161C; border-radius: 10px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px; cursor: pointer">Salin nomor</button>
</div>
@else
<div style="border: 1px solid #E6E4DF; border-radius: 14px; padding: 18px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap">
<img src="{{ $qris }}" alt="Kode QRIS {{ nama_jastip() }}" style="width: 180px; height: 180px; object-fit: contain; border-radius: 12px; background: #FFFFFF; border: 1px solid #E6E4DF; flex: none">
<p style="margin: 0; flex: 1 1 200px; font-size: 14px; line-height: 1.6; color: #4E515C">Scan dari aplikasi bank atau e-wallet apa pun dan bayar tepat <strong style="color: #15161C">{{ rp($amount) }}</strong>. Setelah itu upload screenshot bukti pembayarannya supaya cepat kami cocokkan.</p>
</div>
@endif
<div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center">
<a href="{{ $url('upload', ['metode' => $metode]) }}" style="{{ $btnInk }}; flex: 1 1 260px">{{ $metode === 'transfer' ? 'Saya sudah transfer' : 'Saya sudah bayar via QRIS' }}</a>
<a href="{{ $url('penawaran') }}" style="min-height: 52px; padding: 0 16px; display: inline-flex; align-items: center; font-weight: 700; font-size: 14px; color: #4E515C; text-decoration: none">← Kembali</a>
</div>
@endif

{{-- ---------- Upload bukti ---------- --}}
@if ($state === 'upload')
<form method="POST" action="{{ $submitUrl }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px; margin: 0">
@csrf
<input type="hidden" name="pay_method" value="{{ $metode }}">
<h2 style="margin: 0; font-size: 20px; font-weight: 800">{{ $metode === 'qris' ? 'Upload bukti pembayaran QRIS' : 'Upload bukti transfer' }}</h2>
<div style="display: flex; gap: 16px; flex-wrap: wrap">
<label class="drop" style="flex: 1 1 260px; min-height: 180px; border: 1.5px dashed #CFCDC6; border-radius: 14px; background: #FAFAF8; color: #4E515C; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 12px; box-sizing: border-box; text-align: center; cursor: pointer">
<input type="file" name="proof" data-file accept="image/*" required>
<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4"></path><path d="m7 9 5-5 5 5"></path><path d="M4 20h16"></path></svg>
<span style="font-size: 15px; font-weight: 700" data-file-name data-empty="Tarik file ke sini atau klik">Tarik file ke sini atau klik</span>
<span style="font-size: 12.5px">JPG atau PNG, maksimal 5 MB</span>
</label>
<div style="flex: 1 1 240px; display: flex; flex-direction: column; gap: 12px">
<label style="{{ $lab }}">Nama pengirim<input type="text" name="payer_name" maxlength="120" value="{{ old('payer_name', auth()->user()->name) }}" style="{{ $in }}"></label>
@if ($metode === 'transfer')
<label style="{{ $lab }}">Bank pengirim<input type="text" name="payer_bank" maxlength="60" value="{{ old('payer_bank') }}" placeholder="Mis. BCA, Mandiri, BRI" style="{{ $in }}"></label>
@endif
<p style="margin: 0; font-size: 13px; line-height: 1.5; color: #4E515C">Jumlah: <strong style="color: #15161C">{{ rp($amount) }}</strong>. Nama &amp; bank pengirim membantu admin mencocokkan mutasi lebih cepat.</p>
</div>
</div>
<div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center">
<button type="submit" style="{{ $btnInk }}; flex: 1 1 260px">{{ $metode === 'qris' ? 'Kirim bukti pembayaran' : 'Kirim bukti transfer' }}</button>
<a href="{{ $url('metode', ['metode' => $metode]) }}" style="min-height: 52px; padding: 0 16px; display: inline-flex; align-items: center; font-weight: 700; font-size: 14px; color: #4E515C; text-decoration: none">← Kembali</a>
</div>
</form>
@endif

{{-- ---------- Tolak penawaran ---------- --}}
@if ($state === 'tolak' && $isOrder)
<form method="POST" action="{{ route('pay.reject', $o) }}" style="display: flex; flex-direction: column; gap: 14px; margin: 0">
@csrf
<h2 style="margin: 0; font-size: 20px; font-weight: 800">Tolak penawaran?</h2>
<p style="margin: 0; font-size: 14.5px; line-height: 1.55; color: #4E515C">Pesanan dibatalkan dan tidak ada yang perlu dibayar. Ceritakan alasannya supaya kami bisa bantu:</p>
<div role="radiogroup" aria-label="Alasan" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px">
@foreach (['Harga terlalu tinggi', 'Sudah beli di tempat lain', 'Mau ganti versi / barang'] as $a)
<label style="display: flex; gap: 10px; align-items: center; min-height: 48px; padding: 0 14px; border: 1.5px solid #CFCDC6; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer"><input type="radio" name="alasan" value="{{ $a }}" required style="width: 18px; height: 18px; margin: 0; accent-color: #15161C">{{ $a }}</label>
@endforeach
</div>
<div style="display: flex; gap: 10px; flex-wrap: wrap"><button type="submit" style="{{ $btnInk }}">Ya, tolak penawaran</button><a href="{{ $url('penawaran') }}" style="min-height: 52px; padding: 0 16px; display: inline-flex; align-items: center; font-weight: 700; font-size: 14px; color: #4E515C; text-decoration: none">Batal</a></div>
</form>
@endif

{{-- ---------- Status ---------- --}}
@if (in_array($state, ['menunggu', 'lunas', 'hangus', 'tunggu', 'batal', 'kalah'], true))
@php
    $S = [
        'menunggu' => ['#FFF0D1', '#7A4B00', '<path d="M6 2h12M6 22h12"></path><path d="M7 2c0 5 10 5 10 10S7 17 7 22"></path><path d="M17 2c0 5-10 5-10 10s10 5 10 10"></path>', 'Bukti diterima', 'Admin sedang memverifikasi pembayaranmu. Kami kabari lewat email begitu lunas.'],
        'lunas' => ['#E6F4EC', '#1D7A4C', '<path d="M20 6 9 17l-5-5"></path>', 'Pembayaran lunas', 'Dibayar '.rp($amount).' lewat '.(($method ?? 'transfer') === 'qris' ? 'QRIS' : 'transfer bank').'. '.($isOrder ? 'Kami segera checkout di toko resminya dan mengirim bukti checkout ke emailmu.' : ($kind === 'dp' ? 'Slot war kamu aman. Kami kabari hasilnya setelah rilis.' : 'Pesananmu masuk proses checkout seperti titip biasa.'))],
        'hangus' => ['#F0EEE9', '#4E515C', '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>', 'Penawaran sudah hangus', 'Kurs hanya dikunci '.config('jastip.kurs_dikunci_jam').' jam. Minta penawaran baru dengan kurs hari ini, barangnya tetap sama.'],
        'tunggu' => ['#EAF0FA', '#1A3F7F', '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>', 'Admin sedang menyiapkan penawaran', 'Kami cek stok dan harga di toko dulu. Penawaran dikirim ke emailmu dan muncul di sini.'],
        'batal' => ['#F0EEE9', '#4E515C', '<path d="M6 6l12 12M18 6 6 18"></path>', 'Pesanan dibatalkan', ($isOrder ? ($o->cancel_reason ?: 'Pesanan ini dibatalkan.') : '').($isOrder && $o->refund_amount ? ' Refund '.rp($o->refund_amount).($o->refunded_at ? ' sudah ditransfer.' : ' sedang diproses.') : '')],
        'kalah' => ['#F0EEE9', '#4E515C', '<path d="M6 6l12 12M18 6 6 18"></path>', 'Kali ini belum beruntung', $isOrder ? '' : 'DP dikembalikan '.rp($e->refund_amount).($e->refunded_at ? ' (sudah ditransfer).' : ' ke rekening refund kamu.')],
    ][$state];
@endphp
<div style="padding: 24px 0; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 14px">
<span style="width: 80px; height: 80px; border-radius: 50%; background: {{ $S[0] }}; color: {{ $S[1] }}; display: flex; align-items: center; justify-content: center"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $S[2] !!}</svg></span>
<h2 style="margin: 0; font-size: 24px; font-weight: 800">{{ $S[3] }}</h2>
<p style="margin: 0; max-width: 440px; font-size: 15px; line-height: 1.6; color: #4E515C">{{ $S[4] }}</p>
<div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center">
@if ($state === 'hangus')
<form method="POST" action="{{ route('pay.renew', $o) }}" style="margin: 0">@csrf<button type="submit" style="{{ $btnInk }}">Minta penawaran baru</button></form>
@elseif ($isOrder)
<a href="{{ route('track', ['no' => $o->code]) }}" style="{{ $state === 'lunas' ? $btnInk : $btnLine }}; min-height: 48px">Lacak pesanan</a>
<a href="{{ route('history') }}" style="{{ $btnLine }}; min-height: 48px">Riwayat pembayaran</a>
@else
<a href="{{ route('war.entry', $e) }}" style="{{ $btnInk }}; min-height: 48px">Lihat status war</a>
<a href="{{ route('history') }}" style="{{ $btnLine }}; min-height: 48px">Riwayat pembayaran</a>
@endif
</div>
</div>
@endif
</main>

<aside class="pay-aside" style="flex: 1 1 300px; max-width: 360px; display: flex; flex-direction: column; gap: 14px">
@if ($live && $deadline && $deadline->isFuture())
<div style="background: #15161C; color: #FFFFFF; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; gap: 14px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #FF8A78">{{ $isOrder ? 'KURS DIKUNCI · '.$sym.'1 = '.mb_strtoupper(rpk($o->kurs_jual)) : ($kind === 'dp' ? 'PENDAFTARAN TUTUP DALAM' : 'BATAS PELUNASAN') }}</span>
<span data-countdown="{{ $deadline->toIso8601String() }}" data-countdown-reload style="font-size: 30px; font-weight: 800; font-variant-numeric: tabular-nums">--:--:--</span>
<span style="font-size: 13px; color: #C9CAD1">{{ $isOrder ? 'Lewat dari ini, penawaran hangus dan kurs dihitung ulang.' : ($kind === 'dp' ? 'Upload bukti DP sebelum pendaftaran tutup.' : 'Lewat dari ini, DP hangus sesuai ketentuan.') }} ({{ tgl($deadline, 'j M H:i') }} WIB)</span>
</div>
@endif
<div style="background: #FFFFFF; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; gap: 10px">
<h2 style="margin: 0 0 4px; font-size: 16px; font-weight: 800">Ringkasan</h2>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
@if ($isOrder)
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Total di toko</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}{{ angka($o->store_price) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Ongkir dalam {{ $neg['nama'] }}</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}{{ angka($o->domestic_shipping) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Kurs jastip</dt><dd style="margin: 0; font-weight: 700">{{ $o->kurs_jual ? rpk($o->kurs_jual) : '—' }}</dd></div>
@else
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Harga · {{ $sym }}{{ angka($e->price) }} × {{ $e->qty }}</dt><dd style="margin: 0; font-weight: 700">{{ rp($e->total - $e->fee * $e->qty) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Fee war</dt><dd style="margin: 0; font-weight: 700">{{ rp($e->fee * $e->qty) }}</dd></div>
@if ($kind === 'settle')<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">DP {{ $d->dp_pct }}% (sudah dibayar)</dt><dd style="margin: 0; font-weight: 700; white-space: nowrap">− {{ rp($e->dp_amount) }}</dd></div>@endif
@endif
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; border-top: 1px dashed #CFCDC6; padding-top: 10px"><dt style="font-weight: 800">{{ $state === 'lunas' ? 'Sudah dibayar' : 'Bayar sekarang' }}</dt><dd style="margin: 0; font-size: 24px; font-weight: 800">{{ $amount ? rp($amount) : '—' }}</dd></div>
</dl>
<p style="margin: 4px 0 0; font-size: 12.5px; color: #4E515C">Tahap 2 (ongkir &amp; packing) dibayar lewat Shopee. <a href="{{ route('terms') }}" style="font-weight: 700">Baca ketentuan</a></p>
</div>
</aside>
</div>
</div>
@endsection
