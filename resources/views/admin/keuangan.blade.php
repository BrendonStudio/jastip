@extends('layouts.admin')
@section('title', 'Admin · Keuangan')

@php
    $in = 'height: 46px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 15px; font-weight: 700; color: #15161C; min-width: 0; background: #FFFFFF';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $kotor = $k['kotor'];
    $mx = max($k['tKurs'], $k['tB'], $k['tP'], $k['tF'], 1);
    $pct = fn ($v) => $kotor > 0 ? round($v / $kotor * 100).'%' : '0%';
    $sumber = [
        ['Selisih kurs', $k['tKurs'], 'Harga barang × (kurs jual − kurs modal)'],
        ['Ongkir berat', $k['tB'], 'Berat bulat × (tarif jual − modal kargo)'],
        ['Packing', $k['tP'], 'Harga jual packing − modal packing'],
        ['Fee war', $k['tF'], 'Fee war dari pemenang + DP kalah yang dipotong fee'],
    ];
    $rows = collect($k['rows'])->filter(fn ($r) => $filter === 'semua' || $r['jenis'] === $filter);
    $dash = fn ($v) => abs($v) < 0.5 ? '—' : rp($v);
    $c = $k['cost'];
    $bulanLabel = fn ($m) => tgl($m.'-01', 'F Y');
@endphp

@section('main')
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Keuangan</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Profit dihitung otomatis dari selisih modal dan harga jual di tiap pesanan.</p>
</div>
<div style="display: flex; gap: 8px; flex-wrap: wrap">
<form method="GET" action="{{ route('admin.finance') }}" data-autosubmit style="margin: 0">
<label class="sr-only" for="bulan">Periode</label>
<select id="bulan" name="bulan" style="min-height: 44px; padding: 0 14px; border: 1.5px solid #CFCDC6; border-radius: 10px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px; color: #15161C">
@foreach ($months as $m)<option value="{{ $m }}" @selected($m === $month)>Periode: {{ $bulanLabel($m) }}</option>@endforeach
</select>
</form>
<a href="{{ route('admin.finance.download', ['bulan' => $month]) }}" style="min-height: 44px; padding: 0 16px; border-radius: 10px; background: #15161C; color: #FFFFFF; font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; text-decoration: none">Unduh laporan</a>
</div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 12px">
<div style="flex: 1 1 200px; background: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 6px"><span style="font-size: 13px; font-weight: 600; color: #4E515C">Omzet (uang masuk)</span><span style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em">{{ rp($k['omzet']) }}</span><span style="font-size: 12.5px; color: #4E515C">{{ count($k['rows']) }} pesanan</span></div>
<div style="flex: 1 1 200px; background: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 6px"><span style="font-size: 13px; font-weight: 600; color: #4E515C">Profit kotor</span><span style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em">{{ rp($kotor) }}</span><span style="font-size: 12.5px; color: #4E515C">Kurs + berat + packing + fee war</span></div>
<div style="flex: 1 1 200px; background: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 6px"><span style="font-size: 13px; font-weight: 600; color: #4E515C">Potongan &amp; biaya</span><span style="font-size: 24px; font-weight: 800; letter-spacing: -0.02em; color: #B02A19">{{ rp(-($k['pot'] + $k['ops'])) }}</span><span style="font-size: 12.5px; color: #4E515C">Shopee + operasional</span></div>
<div style="flex: 1 1 200px; background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 6px"><span style="font-size: 13px; font-weight: 600; color: #C9CAD1">Profit bersih</span><span style="font-size: 28px; font-weight: 800; letter-spacing: -0.02em">{{ rp($k['bersih']) }}</span><span style="font-size: 12.5px; color: #C9CAD1">Margin {{ str_replace('.', ',', number_format($k['margin'], 1)) }}% dari omzet</span></div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: stretch">
<section aria-labelledby="sumber-title" style="flex: 3 1 440px; background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 16px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px"><h2 id="sumber-title" style="margin: 0; font-size: 17px; font-weight: 800">Profit dari mana?</h2><span style="font-size: 13px; color: #4E515C">Total {{ rp($kotor) }}</span></div>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 14px">
@foreach ($sumber as [$label, $v, $tip])
<li title="{{ $label }}: {{ rp($v) }} · {{ $tip }}" style="display: grid; grid-template-columns: 110px minmax(0, 1fr) 150px; gap: 12px; align-items: center; font-size: 14px">
<span style="font-weight: 700">{{ $label }}</span>
<span style="height: 14px; border-radius: 4px; background: #F0EEE9; display: flex"><span style="width: {{ max(0, $v) / $mx * 100 }}%; background: #1D4E9E; border-radius: 4px"></span></span>
<span style="text-align: right; font-weight: 800">{{ rp($v) }} <span style="font-weight: 600; color: #4E515C">· {{ $pct($v) }}</span></span>
</li>
@endforeach
</ul>
<div style="border-top: 1px dashed #CFCDC6; padding-top: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><span style="color: #4E515C">Potongan Shopee ({{ angka($k['pct'], 1) }}% dari tahap 2)</span><strong style="color: #B02A19">{{ rp(-$k['pot']) }}</strong></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><span style="color: #4E515C">Biaya operasional</span><strong style="color: #B02A19">{{ rp(-$k['ops']) }}</strong></div>
<div style="display: flex; justify-content: space-between; font-size: 16px; padding-top: 4px"><span style="font-weight: 800">Profit bersih</span><strong>{{ rp($k['bersih']) }}</strong></div>
</div>
</section>

<section aria-labelledby="cair-title" style="flex: 2 1 300px; display: flex; flex-direction: column; gap: 16px">
<div style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px">
<h2 id="cair-title" style="margin: 0; font-size: 17px; font-weight: 800">Sudah cair vs belum</h2>
<div style="display: flex; justify-content: space-between; align-items: baseline"><span style="font-size: 14px; color: #4E515C">Sudah cair</span><span style="font-size: 20px; font-weight: 800; color: #16653F">{{ rp($k['cair']) }}</span></div>
<div style="display: flex; justify-content: space-between; align-items: baseline"><span style="font-size: 14px; color: #4E515C">Belum cair</span><span style="font-size: 20px; font-weight: 800">{{ rp($k['belum']) }}</span></div>
<p style="margin: 0; font-size: 12.5px; line-height: 1.5; color: #4E515C">Untung berat &amp; packing baru cair setelah penitip checkout di Shopee. Untung kurs dan fee war cair saat tahap 1 / pelunasan.</p>
</div>
<div style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px">
<h2 style="margin: 0; font-size: 17px; font-weight: 800">Per negara</h2>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<div style="border-top: 4px solid #C2311F; background: #F7F6F2; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 13px; font-weight: 700">Jepang</span><span style="font-size: 18px; font-weight: 800">{{ rp($k['profitJp']) }}</span><span style="font-size: 12px; color: #4E515C">{{ $k['nJp'] }} pesanan</span></div>
<div style="border-top: 4px solid #1D4E9E; background: #F7F6F2; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 4px"><span style="font-size: 13px; font-weight: 700">Korea</span><span style="font-size: 18px; font-weight: 800">{{ rp($k['profitKr']) }}</span><span style="font-size: 12px; color: #4E515C">{{ $k['nKr'] }} pesanan</span></div>
</div>
</div>
</section>
</div>

<form method="POST" action="{{ route('admin.finance.costs') }}" data-autosubmit aria-labelledby="ops-title" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
@csrf
<input type="hidden" name="bulan" value="{{ $month }}"><input type="hidden" name="jenis" value="{{ $filter }}">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap"><h2 id="ops-title" style="margin: 0; font-size: 17px; font-weight: 800">Biaya {{ $bulanLabel($month) }}</h2><span style="font-size: 13px; color: #4E515C">Isi manual, langsung mengurangi profit bersih (tersimpan otomatis)</span></div>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px">
<label style="{{ $lab }}">Potongan Shopee (%)<input type="number" name="shopee_pct" step="0.1" min="0" value="{{ $c->shopee_pct + 0 }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Sewa warehouse<input type="number" name="sewa" step="10000" min="0" placeholder="Rp 0" value="{{ $c->sewa ?: '' }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Iklan<input type="number" name="iklan" step="10000" min="0" placeholder="Rp 0" value="{{ $c->iklan ?: '' }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Gaji / admin<input type="number" name="gaji" step="10000" min="0" placeholder="Rp 0" value="{{ $c->gaji ?: '' }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Lain-lain<input type="number" name="lain" step="10000" min="0" placeholder="Rp 0" value="{{ $c->lain ?: '' }}" style="{{ $in }}"></label>
</div>
</form>

<section aria-labelledby="tabel-title" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap">
<h2 id="tabel-title" style="margin: 0; font-size: 17px; font-weight: 800">Profit per pesanan</h2>
<div role="group" aria-label="Filter jenis" style="display: flex; gap: 6px">
@foreach (['semua' => 'Semua', 'titip' => 'Titip', 'war' => 'War'] as $id => $label)
@php $on = $filter === $id; @endphp
<a href="{{ route('admin.finance', ['bulan' => $month, 'jenis' => $id]) }}" aria-pressed="{{ $on ? 'true' : 'false' }}" style="min-height: 40px; padding: 0 14px; border-radius: 999px; border: 1.5px solid {{ $on ? '#15161C' : '#CFCDC6' }}; background: {{ $on ? '#15161C' : '#FFFFFF' }}; color: {{ $on ? '#FFFFFF' : '#15161C' }}; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; text-decoration: none; box-sizing: border-box">{{ $label }}</a>
@endforeach
</div>
</div>
<div style="overflow-x: auto">
<table style="width: 100%; min-width: 920px; border-collapse: collapse; font-size: 13.5px">
<thead>
<tr style="text-align: left; color: #4E515C; font-size: 12px">
<th scope="col" style="padding: 8px 8px 8px 0; font-weight: 700; border-bottom: 1px solid #E6E4DF">Pesanan</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Jenis</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Status</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Kurs</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Berat</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Packing</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Fee war</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Pot. Shopee</th>
<th scope="col" style="padding: 8px 0 8px 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF; text-align: right">Profit</th>
</tr>
</thead>
<tbody>
@forelse ($rows as $r)
@php $sp = pill($r['color']); @endphp
<tr>
<td style="padding: 10px 8px 10px 0; border-bottom: 1px solid #F0EEE9">@if($r['order_id'])<a href="{{ route('admin.orders', ['id' => $r['order_id']]) }}" style="font-weight: 800; text-decoration: none">{{ $r['id'] }}</a>@else<span style="font-weight: 800">{{ $r['id'] }}</span>@endif <span style="font-size: 12px; font-weight: 700; color: {{ negara($r['neg'])['fg'] }}">{{ strtoupper($r['neg']) }}</span></td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9">{{ $r['jenis'] === 'war' ? 'War' : 'Titip' }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9"><span style="font-size: 12px; font-weight: 700; color: {{ $sp['fg'] }}; background: {{ $sp['bg'] }}; padding: 4px 8px; border-radius: 999px; white-space: nowrap">{{ $r['status'] }}</span></td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9; text-align: right">{{ $dash($r['kurs']) }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9; text-align: right">{{ $dash($r['b']) }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9; text-align: right">{{ $dash($r['p']) }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9; text-align: right">{{ $dash($r['fw']) }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9; text-align: right; color: #B02A19">{{ abs($r['pot']) < 0.5 ? '—' : rp(-$r['pot']) }}</td>
<td style="padding: 10px 0 10px 8px; border-bottom: 1px solid #F0EEE9; text-align: right; font-weight: 800">{{ rp($r['profit']) }}</td>
</tr>
@empty
<tr><td colspan="9" style="padding: 16px 0; color: #4E515C">Belum ada pesanan lunas di periode ini.</td></tr>
@endforelse
</tbody>
<tfoot>
<tr>
<th scope="row" colspan="8" style="padding: 12px 8px 0 0; text-align: right; font-size: 13px; color: #4E515C">Total {{ $filter === 'semua' ? 'semua pesanan' : ($filter === 'war' ? 'pesanan war' : 'pesanan titip') }}</th>
<td style="padding: 12px 0 0 8px; text-align: right; font-size: 15px; font-weight: 800">{{ rp($rows->sum('profit')) }}</td>
</tr>
</tfoot>
</table>
</div>
</section>
@endsection
