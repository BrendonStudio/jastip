@extends('layouts.site')
@section('title', 'Daftar war · '.$drop->name)
@section('body_class', 'bg-soft')

@php
    $neg = $drop->neg();
    $vars = $drop->variantList();
    $oldMain = old('variant_main', $vars[0]);
    $oldBackup = old('variant_backup', '');
    $sisa = 100 - $drop->dp_pct;
    $qty0 = (int) old('qty', 1);
    $total0 = (int) round($drop->price * $qty0 * $kurs + $drop->fee * $qty0);
    $dp0 = (int) round($total0 * $drop->dp_pct / 100);
    $img = upload_url($drop->image);
    $attrs = 'data-war-form data-price="'.$drop->price.'" data-kurs="'.$kurs.'" data-fee="'.$drop->fee.'" data-dp="'.$drop->dp_pct.'"';
    $agreeText = 'Saya paham war tidak dijamin menang. Jika kalah, DP dikembalikan '.($drop->refund_mode === 'penuh' ? 'penuh' : 'dipotong fee war').'. Jika menang, sisa '.$sisa.'% dilunasi maksimal '.$drop->settle_hours.' jam; lewat dari itu DP hangus.';
@endphp

@section('content')
{{-- ======================= DESKTOP ======================= --}}
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%">
@include('partials.header', ['dark' => true, 'active' => 'war'])
@include('partials.flash')

<form method="POST" action="{{ route('war.store', $drop) }}" enctype="multipart/form-data" {!! $attrs !!} style="max-width: 1200px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 20px">
@csrf
<a href="{{ route('war') }}" style="align-self: flex-start; min-height: 40px; display: inline-flex; align-items: center; font-weight: 700; font-size: 14px; color: #4E515C; text-decoration: none">← Kembali ke jadwal war</a>
<div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start">
<section style="flex: 1 1 380px; background: #15161C; color: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 14px">
@if ($img)
<img src="{{ $img }}" alt="Foto {{ $drop->name }}" class="ph" style="width: 100%; aspect-ratio: 4 / 3; border-radius: 14px">
@else
<div role="img" aria-label="Foto produk" style="aspect-ratio: 4 / 3; border-radius: 14px; background: #2A2C35"></div>
@endif
<span style="align-self: flex-start; font-size: 12px; font-weight: 700; color: #15161C; background: #FFFFFF; padding: 4px 10px; border-radius: 999px">{{ $neg['nama'] }}</span>
<h1 style="margin: 0; font-size: 24px; font-weight: 800">{{ $drop->name }}</h1>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px; color: #C9CAD1">
<div style="display: flex; justify-content: space-between"><dt>Rilis</dt><dd style="margin: 0; color: #FFFFFF; font-weight: 700">{{ tgl($drop->release_at, 'j M Y') }} · {{ $drop->release_at->format('H:i') }} WIB</dd></div>
<div style="display: flex; justify-content: space-between"><dt>Pendaftaran tutup</dt><dd style="margin: 0; color: #FFFFFF; font-weight: 700">{{ $drop->close_hours }} jam sebelum rilis</dd></div>
<div style="display: flex; justify-content: space-between"><dt>Slot tersisa</dt><dd style="margin: 0; color: #FFFFFF; font-weight: 700">{{ $drop->slotsLeft() }}</dd></div>
<div style="display: flex; justify-content: space-between"><dt>Harga di toko</dt><dd style="margin: 0; color: #FFFFFF; font-weight: 700">{{ $neg['sym'] }}{{ angka($drop->price) }}</dd></div>
</dl>
@if ($drop->release_url)
<a href="{{ $drop->release_url }}" target="_blank" rel="noopener" style="color: #FFFFFF; font-size: 14px; font-weight: 700">Lihat halaman rilis ↗</a>
@endif
</section>

<section aria-label="Form daftar war" style="flex: 2 1 520px; display: flex; flex-direction: column; gap: 16px">
<div style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 18px">
<div role="radiogroup" aria-labelledby="v1l" style="display: flex; flex-direction: column; gap: 8px">
<h2 id="v1l" style="margin: 0; font-size: 15px; font-weight: 800">Varian pilihan utama</h2>
<div style="display: flex; flex-wrap: wrap; gap: 8px">
@foreach ($vars as $v)
<label class="opt opt-neutral" style="min-height: 44px; padding: 0 16px; border-radius: 10px; font-size: 14px"><input type="radio" name="variant_main" value="{{ $v }}" @checked($oldMain === $v)>{{ $v }}</label>
@endforeach
</div>
</div>
<div role="radiogroup" aria-labelledby="v2l" style="display: flex; flex-direction: column; gap: 8px">
<h2 id="v2l" style="margin: 0; font-size: 15px; font-weight: 800">Cadangan kalau pilihan utama habis</h2>
<div style="display: flex; flex-wrap: wrap; gap: 8px">
@foreach ($vars as $v)
<label class="opt opt-neutral" style="min-height: 44px; padding: 0 16px; border-radius: 10px; font-size: 14px"><input type="radio" name="variant_backup" value="{{ $v }}" @checked($oldBackup === $v)>{{ $v }}</label>
@endforeach
<label class="opt opt-neutral" style="min-height: 44px; padding: 0 16px; border-radius: 10px; font-size: 14px"><input type="radio" name="variant_backup" value="" @checked($oldBackup === '')>Tidak perlu</label>
</div>
</div>
<div data-stepper style="display: flex; align-items: center; justify-content: space-between">
<div style="display: flex; flex-direction: column; gap: 2px"><span style="font-size: 15px; font-weight: 800">Jumlah</span><span style="font-size: 12.5px; color: #4E515C">Maks {{ $drop->max_per_person }} per orang</span></div>
<div style="display: flex; align-items: center; gap: 4px; border: 1px solid #CFCDC6; border-radius: 12px">
<button type="button" data-step="-1" aria-label="Kurangi jumlah" style="width: 44px; height: 44px; border: 0; background: transparent; font-family: inherit; font-size: 20px; font-weight: 700; color: #15161C; cursor: pointer">−</button>
<span data-step-show style="min-width: 28px; text-align: center; font-size: 16px; font-weight: 800">{{ $qty0 }}</span>
<input type="hidden" name="qty" value="{{ $qty0 }}" min="1" max="{{ $drop->max_per_person }}">
<button type="button" data-step="1" aria-label="Tambah jumlah" style="width: 44px; height: 44px; border: 0; background: transparent; font-family: inherit; font-size: 20px; font-weight: 700; color: #15161C; cursor: pointer">+</button>
</div>
</div>
</div>

<div style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-wrap: wrap; gap: 20px">
<dl style="flex: 1 1 240px; margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Harga barang × <span data-o="qty">{{ $qty0 }}</span> × kurs</dt><dd style="margin: 0; font-weight: 700" data-o="barang">{{ rp($drop->price * $qty0 * $kurs) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Fee war × <span data-o="qty">{{ $qty0 }}</span></dt><dd style="margin: 0; font-weight: 700" data-o="fee">{{ rp($drop->fee * $qty0) }}</dd></div>
<div style="display: flex; justify-content: space-between; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 700">Total</dt><dd style="margin: 0; font-weight: 700" data-o="total">{{ rp($total0) }}</dd></div>
<p style="margin: 4px 0 0; font-size: 12.5px; color: #4E515C">Kurs {{ $neg['sym'] }}1 = {{ rpk($kurs) }} · ongkir &amp; packing tetap dibayar nanti lewat Shopee.</p>
</dl>
<div style="flex: 1 1 260px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; align-content: start">
<div style="background: #FBEDEA; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 2px"><span style="font-size: 11.5px; font-weight: 800; letter-spacing: 0.04em; color: #B02A19">BAYAR SEKARANG</span><span style="font-size: 20px; font-weight: 800">DP {{ $drop->dp_pct }}%</span><span style="font-size: 14px; font-weight: 700" data-o="dp">{{ rp($dp0) }}</span></div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 2px"><span style="font-size: 11.5px; font-weight: 800; letter-spacing: 0.04em; color: #4E515C">SETELAH MENANG</span><span style="font-size: 20px; font-weight: 800">Sisa {{ $sisa }}%</span><span style="font-size: 14px; font-weight: 700" data-o="sisa">{{ rp($total0 - $dp0) }}</span></div>
<label class="drop" style="grid-column: 1 / -1; min-height: 64px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; padding: 8px; box-sizing: border-box; text-align: center">
<input type="file" name="dp_proof" data-file accept="image/*" required>
<span style="font-size: 13.5px; font-weight: 700" data-file-name data-empty="Upload bukti transfer DP">Upload bukti transfer DP</span>
<span style="font-size: 12px">Transfer ke {{ config('jastip.rekening') }}</span>
</label>
</div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center">
<label style="flex: 1 1 380px; display: flex; gap: 12px; align-items: flex-start; background: #FFF6E5; border-radius: 14px; padding: 14px; font-size: 13.5px; line-height: 1.5; color: #5C3900; cursor: pointer">
<input type="checkbox" name="agree" value="1" data-agree @checked(old('agree')) style="width: 22px; height: 22px; margin: 0; flex: none">
<span>{{ $agreeText }}</span>
</label>
<button type="submit" data-submit disabled class="btn-war" style="flex: 0 1 300px; min-height: 56px; border: 0; border-radius: 12px; font-family: inherit; font-weight: 700; font-size: 16px">Bayar DP {{ $drop->dp_pct }}% &amp; amankan slot</button>
</div>
</section>
</div>
</form>
</div>

{{-- ======================= HP ======================= --}}
<div class="mob">
<form method="POST" action="{{ route('war.store', $drop) }}" enctype="multipart/form-data" {!! $attrs !!} class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF">
@csrf
@include('partials.mob-header', ['title' => 'Daftar war', 'back' => route('war')])
@include('partials.flash', ['style' => 'padding: 12px 16px 0'])
<main style="flex: 1; padding: 16px; display: flex; flex-direction: column; gap: 14px">
<section style="background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 10px">
<span style="align-self: flex-start; font-size: 12px; font-weight: 700; color: #15161C; background: #FFFFFF; padding: 4px 10px; border-radius: 999px">{{ $neg['nama'] }}</span>
<h2 style="margin: 0; font-size: 17px; font-weight: 800">{{ $drop->name }}</h2>
<div style="display: flex; justify-content: space-between; gap: 8px; font-size: 13.5px; color: #C9CAD1"><span>Rilis {{ tgl($drop->release_at, 'j M') }} · {{ $drop->release_at->format('H:i') }} WIB</span><span>Tutup {{ $drop->close_hours }} jam sebelum</span></div>
</section>

<section aria-labelledby="var1" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 14px">
<div role="radiogroup" aria-labelledby="var1" style="display: flex; flex-direction: column; gap: 8px">
<h3 id="var1" style="margin: 0; font-size: 14px; font-weight: 800">Varian pilihan utama</h3>
<div style="display: flex; flex-wrap: wrap; gap: 8px">
@foreach ($vars as $v)
<label class="opt opt-neutral" style="min-height: 44px; padding: 0 14px; border-radius: 10px; font-size: 14px"><input type="radio" name="variant_main" value="{{ $v }}" @checked($oldMain === $v)>{{ $v }}</label>
@endforeach
</div>
</div>
<div role="radiogroup" aria-labelledby="var2" style="display: flex; flex-direction: column; gap: 8px">
<h3 id="var2" style="margin: 0; font-size: 14px; font-weight: 800">Cadangan kalau pilihan utama habis</h3>
<div style="display: flex; flex-wrap: wrap; gap: 8px">
@foreach ($vars as $v)
<label class="opt opt-neutral" style="min-height: 44px; padding: 0 14px; border-radius: 10px; font-size: 14px"><input type="radio" name="variant_backup" value="{{ $v }}" @checked($oldBackup === $v)>{{ $v }}</label>
@endforeach
<label class="opt opt-neutral" style="min-height: 44px; padding: 0 14px; border-radius: 10px; font-size: 14px"><input type="radio" name="variant_backup" value="" @checked($oldBackup === '')>Tidak perlu</label>
</div>
</div>
<div data-stepper style="display: flex; align-items: center; justify-content: space-between">
<div style="display: flex; flex-direction: column; gap: 2px"><span style="font-size: 14px; font-weight: 800">Jumlah</span><span style="font-size: 12px; color: #4E515C">Maks {{ $drop->max_per_person }} per orang</span></div>
<div style="display: flex; align-items: center; gap: 4px; border: 1px solid #CFCDC6; border-radius: 12px">
<button type="button" data-step="-1" aria-label="Kurangi jumlah" style="width: 44px; height: 44px; border: 0; background: transparent; font-family: inherit; font-size: 20px; font-weight: 700; color: #15161C">−</button>
<span data-step-show style="min-width: 28px; text-align: center; font-size: 16px; font-weight: 800">{{ $qty0 }}</span>
<input type="hidden" name="qty" value="{{ $qty0 }}" min="1" max="{{ $drop->max_per_person }}">
<button type="button" data-step="1" aria-label="Tambah jumlah" style="width: 44px; height: 44px; border: 0; background: transparent; font-family: inherit; font-size: 20px; font-weight: 700; color: #15161C">+</button>
</div>
</div>
</section>

<section aria-labelledby="bayar-war" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 10px">
<h3 id="bayar-war" style="margin: 0; font-size: 14px; font-weight: 800">Rincian pembayaran</h3>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Harga barang × <span data-o="qty">{{ $qty0 }}</span> × kurs</dt><dd style="margin: 0; font-weight: 700" data-o="barang">{{ rp($drop->price * $qty0 * $kurs) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Fee war × <span data-o="qty">{{ $qty0 }}</span></dt><dd style="margin: 0; font-weight: 700" data-o="fee">{{ rp($drop->fee * $qty0) }}</dd></div>
<div style="display: flex; justify-content: space-between; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 700">Total</dt><dd style="margin: 0; font-weight: 700" data-o="total">{{ rp($total0) }}</dd></div>
</dl>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<div style="background: #FBEDEA; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 2px">
<span style="font-size: 11.5px; font-weight: 800; letter-spacing: 0.04em; color: #B02A19">BAYAR SEKARANG</span>
<span style="font-size: 18px; font-weight: 800">DP {{ $drop->dp_pct }}%</span>
<span style="font-size: 14px; font-weight: 700" data-o="dp">{{ rp($dp0) }}</span>
</div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 2px">
<span style="font-size: 11.5px; font-weight: 800; letter-spacing: 0.04em; color: #4E515C">SETELAH MENANG</span>
<span style="font-size: 18px; font-weight: 800">Sisa {{ $sisa }}%</span>
<span style="font-size: 14px; font-weight: 700" data-o="sisa">{{ rp($total0 - $dp0) }}</span>
</div>
</div>
<label class="drop" style="min-height: 64px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; padding: 8px; box-sizing: border-box; text-align: center">
<input type="file" name="dp_proof" data-file accept="image/*" required>
<span style="font-size: 13.5px; font-weight: 700" data-file-name data-empty="Upload bukti transfer DP">Upload bukti transfer DP</span>
<span style="font-size: 12px">Transfer ke {{ config('jastip.rekening') }}</span>
</label>
<p style="margin: 0; font-size: 12.5px; color: #4E515C">Ongkir &amp; packing tetap dibayar nanti lewat Shopee.</p>
</section>

<label style="display: flex; gap: 12px; align-items: flex-start; background: #FFF6E5; border-radius: 14px; padding: 14px; font-size: 13.5px; line-height: 1.5; color: #5C3900; cursor: pointer">
<input type="checkbox" name="agree" value="1" data-agree @checked(old('agree')) style="width: 22px; height: 22px; margin: 0; flex: none">
<span>{{ $agreeText }}</span>
</label>
</main>
<footer style="background: #FFFFFF; border-top: 1px solid #E6E4DF; padding: 14px 16px 22px">
<button type="submit" data-submit disabled class="btn-war" style="width: 100%; min-height: 52px; border: 0; border-radius: 12px; font-family: inherit; font-weight: 700; font-size: 16px">Bayar DP {{ $drop->dp_pct }}% &amp; amankan slot</button>
</footer>
</form>
</div>
@endsection
