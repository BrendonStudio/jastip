@extends('layouts.admin')
@section('title', 'Admin · Pengaturan harga')

@php
    $in = 'height: 46px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 16px; font-weight: 700; color: #15161C; background: #FFFFFF; min-width: 0';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $neg = request('neg') === 'kr' ? 'kr' : 'jp';
    $val = fn ($k) => old($k, $a[$k] + 0);
    $labels = \App\Support\Harga::PACK_LABEL;
@endphp

@section('main')
<form method="POST" action="{{ route('admin.pricing.save') }}" data-pricing style="display: flex; flex-direction: column; gap: 24px">
@csrf
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Pengaturan harga</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Atur kurs jual, tarif per kg, dan packing. Untung dihitung otomatis dari selisih modal dan harga jual.</p>
</div>
<button type="submit" style="min-height: 48px; padding: 0 20px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px">Simpan &amp; tampilkan di web</button>
</div>

<div role="radiogroup" aria-label="Pilih negara" style="display: flex; gap: 8px; flex-wrap: wrap">
<label class="opt opt-jp" style="min-height: 44px; padding: 0 18px; border-radius: 999px; font-size: 15px"><input type="radio" name="tab_neg" value="jp" @checked($neg === 'jp')>Jepang (¥)</label>
<label class="opt opt-kr" style="min-height: 44px; padding: 0 18px; border-radius: 999px; font-size: 15px"><input type="radio" name="tab_neg" value="kr" @checked($neg === 'kr')>Korea (₩)</label>
</div>

@foreach (['jp' => ['Jepang', '¥'], 'kr' => ['Korea', '₩']] as $c => [$nama, $sym])
<div data-neg-panel="{{ $c }}" @if($c !== $neg) hidden @endif style="display: flex; flex-wrap: wrap; gap: 16px">
<section aria-labelledby="k-{{ $c }}" style="flex: 1 1 320px; background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; justify-content: space-between; align-items: baseline"><h2 id="k-{{ $c }}" style="margin: 0; font-size: 17px; font-weight: 800">Kurs {{ $nama }}</h2><span style="font-size: 12px; font-weight: 700; color: #B02A19">Untung dari kurs</span></div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Kurs modal ({{ $sym }}1)<input type="number" inputmode="decimal" step="0.01" min="0" name="{{ $c }}_kurs_modal" value="{{ $val($c.'_kurs_modal') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Kurs jual ({{ $sym }}1)<input type="number" inputmode="decimal" step="0.01" min="0" name="{{ $c }}_kurs_jual" value="{{ $val($c.'_kurs_jual') }}" style="{{ $in }}"></label>
</div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px; font-size: 14px; display: flex; justify-content: space-between; gap: 8px"><span style="color: #4E515C">Untung per {{ $sym }}1</span><strong data-o="{{ $c }}_kursUntung">—</strong></div>
<p style="margin: 0; font-size: 12.5px; color: #4E515C">Kurs modal = biaya kamu per {{ $sym }}1 (kartu / money changer / rekening valas).</p>
</section>
<section aria-labelledby="o-{{ $c }}" style="flex: 1 1 320px; background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; justify-content: space-between; align-items: baseline"><h2 id="o-{{ $c }}" style="margin: 0; font-size: 17px; font-weight: 800">Ongkir per kg</h2><span style="font-size: 12px; font-weight: 700; color: #1D4E9E">Untung dari berat</span></div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Modal kargo / kg<input type="number" inputmode="numeric" step="1000" min="0" name="{{ $c }}_tarif_modal" value="{{ $val($c.'_tarif_modal') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Tarif jual / kg<input type="number" inputmode="numeric" step="1000" min="0" name="{{ $c }}_tarif_jual" value="{{ $val($c.'_tarif_jual') }}" style="{{ $in }}"></label>
</div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px; font-size: 14px; display: flex; justify-content: space-between; gap: 8px"><span style="color: #4E515C">Untung per kg</span><strong data-o="{{ $c }}_ongkirUntung">—</strong></div>
<p style="margin: 0; font-size: 12.5px; color: #4E515C">Pembulatan: ke atas per 1 kg, minimal 1 kg</p>
</section>
</div>
@endforeach

<section aria-labelledby="p-title" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; flex-wrap: wrap"><h2 id="p-title" style="margin: 0; font-size: 17px; font-weight: 800">Biaya packing</h2><span style="font-size: 12px; font-weight: 700; color: #4E515C">Untung dari packing · berlaku semua negara</span></div>
<div style="overflow-x: auto">
<div style="min-width: 520px; display: flex; flex-direction: column">
<div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; padding: 8px 0; font-size: 12px; font-weight: 700; color: #4E515C; border-bottom: 1px solid #E6E4DF"><span>Jenis</span><span>Modal</span><span>Harga jual</span><span>Untung</span></div>
@foreach ($labels as $id => $label)
<div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid #F0EEE9">
<span style="font-size: 15px; font-weight: 700">{{ $label }}</span>
<label style="display: flex"><span class="sr-only">Modal {{ $label }}</span><input type="number" inputmode="numeric" step="1000" min="0" name="pack_{{ $id }}_modal" value="{{ $val('pack_'.$id.'_modal') }}" style="width: 100%; height: 44px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 10px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF; min-width: 0; box-sizing: border-box"></label>
<label style="display: flex"><span class="sr-only">Harga jual {{ $label }}</span><input type="number" inputmode="numeric" step="1000" min="0" name="pack_{{ $id }}_jual" value="{{ $val('pack_'.$id.'_jual') }}" style="width: 100%; height: 44px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 10px; font-family: inherit; font-size: 15px; font-weight: 700; color: #15161C; background: #FFFFFF; min-width: 0; box-sizing: border-box"></label>
<span style="font-size: 15px; font-weight: 800; color: #16653F" data-o="pk_{{ $id }}">—</span>
</div>
@endforeach
</div>
</div>
</section>

<section aria-labelledby="s-title" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-wrap: wrap; gap: 24px">
<div style="flex: 1 1 280px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; flex-direction: column; gap: 4px"><h2 id="s-title" style="margin: 0; font-size: 17px; font-weight: 800">Simulasi untung per pesanan</h2><p style="margin: 0; font-size: 13.5px; color: #4E515C">Untuk <span data-o="negaraNama">Jepang</span>, pakai angka di atas (belum perlu disimpan).</p></div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}"><span>Harga barang (<span data-o="sym">¥</span>)</span><input type="number" inputmode="decimal" value="10000" data-sim="harga" style="{{ $in }}"></label>
<label style="{{ $lab }}">Berat timbang (kg)<input type="number" inputmode="decimal" step="0.1" value="1.5" data-sim="berat" style="{{ $in }}"></label>
</div>
<div role="radiogroup" aria-label="Packing simulasi" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px">
@foreach ($labels as $id => $label)
<label class="opt opt-neutral" style="min-height: 44px; border-radius: 10px; font-size: 13.5px"><input type="radio" name="sim_pack" value="{{ $id }}" @checked($id === 'bubble')>{{ $label }}</label>
@endforeach
</div>
<label style="{{ $lab }}">Potongan Shopee (% dari tahap 2)<input type="number" inputmode="decimal" step="0.1" min="0" value="{{ $shopeePct + 0 }}" data-sim="fee" style="{{ $in }}"></label>
<div style="display: flex; flex-direction: column; gap: 6px; font-size: 14px; padding-top: 4px">
<div style="display: flex; justify-content: space-between"><span style="color: #4E515C">Tagihan tahap 1 (web)</span><strong data-o="tahap1">—</strong></div>
<div style="display: flex; justify-content: space-between"><span style="color: #4E515C">Tagihan tahap 2 (Shopee)</span><strong data-o="tahap2">—</strong></div>
</div>
</div>

<div style="flex: 1 1 320px; display: flex; flex-direction: column; gap: 14px; background: #F7F6F2; border-radius: 14px; padding: 18px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px"><span style="font-size: 14px; font-weight: 700; color: #4E515C">Total untung</span><span style="font-size: 30px; font-weight: 800; letter-spacing: -0.02em; color: #16653F" data-o="untungTotal">—</span></div>
<div aria-hidden="true" style="display: flex; height: 14px; border-radius: 7px; overflow: hidden; background: #E6E4DF; gap: 2px">
<span data-bar="kurs" style="width: 0; background: #C2311F"></span>
<span data-bar="berat" style="width: 0; background: #7FA3DB"></span>
<span data-bar="pack" style="width: 0; background: #15161C"></span>
</div>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="display: flex; align-items: center; gap: 8px"><span aria-hidden="true" style="width: 10px; height: 10px; border-radius: 3px; background: #C2311F; flex: none"></span><span>Dari kurs · <span data-o="simHargaFmt"></span> × <span data-o="kursSelisih"></span></span></dt><dd style="margin: 0; font-weight: 700" data-o="uKurs">—</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="display: flex; align-items: center; gap: 8px"><span aria-hidden="true" style="width: 10px; height: 10px; border-radius: 3px; background: #7FA3DB; flex: none"></span><span>Dari berat · <span data-o="beratBulat"></span> kg × <span data-o="ongkirUntung"></span></span></dt><dd style="margin: 0; font-weight: 700" data-o="uBerat">—</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="display: flex; align-items: center; gap: 8px"><span aria-hidden="true" style="width: 10px; height: 10px; border-radius: 3px; background: #15161C; flex: none"></span><span>Dari packing · <span data-o="simPackLabel"></span></span></dt><dd style="margin: 0; font-weight: 700" data-o="uPack">—</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="display: flex; align-items: center; gap: 8px"><span aria-hidden="true" style="width: 10px; height: 10px; border-radius: 3px; border: 1.5px solid #B02A19; box-sizing: border-box; flex: none"></span><span>Potongan Shopee · <span data-o="feePct"></span></span></dt><dd style="margin: 0; font-weight: 700; color: #B02A19" data-o="uShopee">—</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px; border-top: 1px dashed #CFCDC6; padding-top: 10px"><dt style="color: #4E515C">Margin dari total tagihan</dt><dd style="margin: 0; font-weight: 800" data-o="marginPct">—</dd></div>
</dl>
</div>
</section>
</form>

<section aria-labelledby="qris-t" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center">
@php $qris = \App\Http\Controllers\PayController::qris(); @endphp
@if ($qris)<img src="{{ $qris }}" alt="QRIS saat ini" style="width: 120px; height: 120px; object-fit: contain; border: 1px solid #E6E4DF; border-radius: 12px; flex: none">@endif
<div style="flex: 1 1 280px; display: flex; flex-direction: column; gap: 6px">
<h2 id="qris-t" style="margin: 0; font-size: 17px; font-weight: 800">QRIS untuk pembayaran</h2>
<p style="margin: 0; font-size: 13.5px; line-height: 1.5; color: #4E515C">{{ $qris ? 'Penitip bisa memilih bayar via QRIS di halaman pembayaran. Mereka tetap upload screenshot untuk kamu cocokkan di menu Pembayaran.' : 'Upload gambar QRIS toko supaya penitip bisa bayar via QRIS selain transfer bank ('.config('jastip.rekening').').' }}</p>
</div>
<form method="POST" action="{{ route('admin.pricing.qris') }}" enctype="multipart/form-data" style="margin: 0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center">@csrf
<label class="drop" style="min-height: 44px; padding: 0 16px; border: 1.5px dashed #CFCDC6; border-radius: 12px; display: inline-flex; align-items: center; font-weight: 700; font-size: 14px; color: #4E515C"><input type="file" name="qris" accept="image/*" data-file required><span data-file-name data-empty="Pilih gambar QRIS">Pilih gambar QRIS</span></label>
<button type="submit" style="min-height: 44px; padding: 0 16px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px">{{ $qris ? 'Ganti QRIS' : 'Simpan QRIS' }}</button>
</form>
@if ($qris)
<form method="POST" action="{{ route('admin.pricing.qris') }}" style="margin: 0">@csrf<input type="hidden" name="hapus" value="1"><button type="submit" data-confirm="Matikan pembayaran QRIS?" style="min-height: 44px; padding: 0 12px; border: 0; background: transparent; font-family: inherit; font-weight: 700; font-size: 13.5px; color: #B02A19">Hapus</button></form>
@endif
</section>
@endsection
