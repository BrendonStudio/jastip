@extends('layouts.admin')
@section('title', 'Admin · Jadwal kirim')

@php
    $in = 'height: 44px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 10px; font-family: inherit; font-size: 14px; color: #15161C; min-width: 0; width: 100%; background: #FFFFFF; box-sizing: border-box';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; font-weight: 600; color: #4E515C';
    $kg = fn ($v) => angka($v, 1).' kg';
    $prodOf = function ($o) {
        $i = $o->items->first();
        if (! $i) {
            return 'Pesanan';
        }
        $extra = $o->items->count() - 1;

        return $i->name.($i->qty > 1 ? ' ×'.$i->qty : '').($extra > 0 ? ' +'.$extra : '');
    };
    if ($batch) {
        $neg = negara($batch->country);
        $isi = $batch->orders;
        $berat = (float) $isi->sum('weight');
        $kap = max(1, $batch->capacity_kg);
        $bulatTotal = $isi->sum(fn ($o) => $o->billedKg() ?? 0);
        $kgKargo = $isi->count() ? kg_bulat($berat) : 0;
        $pendapatan = $isi->sum(fn ($o) => ($o->billedKg() ?? 0) * (int) ($o->tarif_jual ?: $h['tj']));
        $biaya = $kgKargo * $h['tm'];
        $locked = $batch->status > 0;
        $over = $berat > $kap;
        $belumLink = $batch->status >= 2 ? $isi->filter(fn ($x) => $x->stage < \App\Models\Order::SHOPEE)->count() : 0;
    }
@endphp

@section('main')
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Jadwal kirim</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Pilih batch, isi dengan paket yang sudah di gudang, lalu ikuti 4 langkahnya sampai upload Shopee.</p>
</div>
<form method="POST" action="{{ route('admin.batches.store') }}" style="display: flex; gap: 8px; align-items: center; margin: 0">
@csrf
<select name="country" aria-label="Asal batch baru" style="height: 46px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 10px; font-family: inherit; font-weight: 700; font-size: 14px; background: #FFFFFF; color: #15161C"><option value="jp">Jepang</option><option value="kr">Korea</option></select>
<button type="submit" style="min-height: 46px; padding: 0 20px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px; cursor: pointer">+ Buat batch baru</button>
</form>
</div>

@if ($cards->isNotEmpty())
<div role="group" aria-label="Pilih batch" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px">
@foreach ($cards as $b)
@php
    $on = $batch && $b->id === $batch->id;
    $bn = negara($b->country);
    $bs = $b->statusPill();
    $bw = $b->totalWeight();
    $bk = max(1, $b->capacity_kg);
@endphp
<a href="{{ route('admin.batches', ['batch' => $b->id]) }}" aria-pressed="{{ $on ? 'true' : 'false' }}" class="adm-card" style="text-align: left; border: 1.5px solid {{ $on ? '#1D4E9E' : '#E6E4DF' }}; background: {{ $on ? '#F3F6FC' : '#FFFFFF' }}; border-radius: 16px; padding: 16px; text-decoration: none; color: #15161C; display: flex; flex-direction: column; gap: 8px">
<span style="display: flex; justify-content: space-between; gap: 8px; align-items: center"><span style="font-size: 12px; font-weight: 700; color: {{ $bn['fg'] }}">{{ $bn['nama'] }} · {{ $b->routeLabel() }}</span><span style="font-size: 11.5px; font-weight: 700; color: {{ $bs['fg'] }}; background: {{ $bs['bg'] }}; padding: 3px 9px; border-radius: 999px; white-space: nowrap">{{ $bs['label'] }}</span></span>
<span style="font-size: 16px; font-weight: 800">{{ $b->label() }}</span>
<span style="display: flex; justify-content: space-between; font-size: 12.5px; color: #4E515C"><span>{{ angka($bw, 1) }} dari {{ angka($bk, 1) }} kg</span><span>{{ $b->orders->count() }} paket</span></span>
<span aria-hidden="true" style="height: 6px; border-radius: 3px; background: #E6E4DF; display: flex"><span style="width: {{ min(100, round($bw / $bk * 100, 1)) }}%; background: {{ $bw > $bk ? '#C2311F' : '#1D4E9E' }}; border-radius: 3px"></span></span>
</a>
@endforeach
</div>
@endif

@if (! $batch)
<div style="background: #FFFFFF; border-radius: 16px; padding: 24px; color: #4E515C">Belum ada batch. Buat batch baru untuk mulai mengumpulkan paket.</div>
@else
<section aria-labelledby="bt" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 18px">
<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 4px"><span style="font-size: 12px; font-weight: 700; color: {{ $neg['fg'] }}">{{ $neg['nama'] }} · {{ $batch->routeLabel() }}</span><h2 id="bt" style="margin: 0; font-size: 21px; font-weight: 800">{{ $batch->label() }}</h2></div>
@if ($batch->status < 3)
<form method="POST" action="{{ route('admin.batches.next', $batch) }}" style="margin: 0">@csrf<button type="submit" data-confirm="{{ \App\Models\Batch::NEXT_LABEL[$batch->status] }}? Penitip di batch ini akan dikabari lewat email." @disabled($batch->status === 0 && $isi->isEmpty()) title="{{ $batch->status === 0 && $isi->isEmpty() ? 'Masukkan paket dulu' : '' }}" style="min-height: 46px; padding: 0 18px; border: 0; border-radius: 12px; background: {{ $batch->status === 0 && $isi->isEmpty() ? '#E6E4DF' : '#C2311F' }}; color: {{ $batch->status === 0 && $isi->isEmpty() ? '#4E515C' : '#FFFFFF' }}; font-family: inherit; font-weight: 700; font-size: 14.5px; cursor: pointer">{{ \App\Models\Batch::NEXT_LABEL[$batch->status] }}</button></form>
@endif
</div>

<ol aria-label="Langkah batch" class="adm-steps4" style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px">
@foreach (\App\Models\Batch::STATUS as $i => $l)
@php
    $done = $i < $batch->status;
    $now = $i === $batch->status;
    $ask = $i > $batch->status
        ? 'Tandai batch sampai langkah "'.$l.'"? Penitip di batch ini akan dikabari lewat email.'
        : 'Kembalikan batch ke langkah "'.$l.'"? (untuk koreksi salah tandai, tanpa email)';
@endphp
<li style="display: flex">
<form method="POST" action="{{ route('admin.batches.status', $batch) }}" style="margin: 0; flex: 1; display: flex">@csrf<input type="hidden" name="status" value="{{ $i }}">
<button type="submit" @if($now) disabled aria-current="step" @endif data-confirm="{{ $ask }}" title="{{ $now ? 'Langkah sekarang' : ($i > $batch->status ? 'Klik untuk tandai sampai langkah ini' : 'Klik untuk kembali ke langkah ini') }}" class="bt-step" style="flex: 1; border: 1.5px solid {{ $now ? '#1D4E9E' : '#E6E4DF' }}; background: {{ $now ? '#EAF0FA' : '#FFFFFF' }}; border-radius: 12px; padding: 10px 12px; display: flex; gap: 10px; align-items: center; text-align: left; font-family: inherit; color: #15161C; cursor: {{ $now ? 'default' : 'pointer' }}">
<span style="width: 26px; height: 26px; border-radius: 50%; background: {{ $done ? '#1D7A4C' : ($now ? '#1D4E9E' : '#F0EEE9') }}; color: {{ ($done || $now) ? '#FFFFFF' : '#4E515C' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ $done ? '✓' : $i + 1 }}</span>
<span style="display: flex; flex-direction: column; gap: 1px"><span style="font-size: 13px; font-weight: {{ $now ? 800 : 600 }}">{{ $l }}</span><span style="font-size: 11.5px; font-weight: 500; color: #4E515C">{{ \App\Models\Batch::STATUS_SUB[$i] }}</span></span>
</button>
</form>
</li>
@endforeach
</ol>

<form method="POST" action="{{ route('admin.batches.update', $batch) }}" style="margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; align-items: end">
@csrf
<label style="{{ $lab }}">Batas paket masuk<input type="date" name="cutoff_date" value="{{ $batch->cutoff_date?->format('Y-m-d') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Tanggal kirim<input type="date" name="ship_date" value="{{ $batch->ship_date?->format('Y-m-d') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Estimasi tiba<input type="date" name="eta_date" value="{{ $batch->eta_date?->format('Y-m-d') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Kapasitas (kg)<input type="number" name="capacity_kg" min="1" step="0.5" value="{{ $batch->capacity_kg + 0 }}" style="{{ $in }}; font-weight: 700"></label>
<label style="{{ $lab }}">No. resi kargo<input type="text" name="resi" value="{{ $batch->resi }}" placeholder="Diisi saat dikirim" style="{{ $in }}"></label>
<label style="{{ $lab }}">Jalur<select name="route" style="{{ $in }}; font-weight: 700"><option value="udara" @selected($batch->route === 'udara')>Udara</option><option value="laut" @selected($batch->route === 'laut')>Laut</option></select></label>
<button type="submit" style="height: 44px; border: 1.5px solid #15161C; border-radius: 10px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px; color: #15161C; cursor: pointer">Simpan</button>
</form>

<div style="display: flex; flex-direction: column; gap: 6px">
<div style="display: flex; justify-content: space-between; gap: 8px; font-size: 13.5px"><span style="font-weight: 700">Terisi {{ angka($berat, 1) }} dari {{ angka($kap, 1) }} kg</span><span style="color: #4E515C">{{ $isi->count() }} paket · {{ $over ? 'lebih '.$kg($berat - $kap) : 'sisa '.$kg($kap - $berat) }}</span></div>
<div role="img" aria-label="Kapasitas terisi {{ round(min(100, $berat / $kap * 100)) }}%" style="height: 10px; border-radius: 5px; background: #F0EEE9; display: flex"><span style="width: {{ min(100, $berat / $kap * 100) }}%; background: {{ $over ? '#C2311F' : '#1D4E9E' }}; border-radius: 5px"></span></div>
</div>
<p style="margin: 0; font-size: 12.5px; color: #4E515C">Setiap pindah langkah, semua penitip di batch ini otomatis dikabari lewat email dan halaman lacak. Klik langkah untuk pindah langsung (maju, atau mundur kalau salah tandai).@if($batch->status >= 2 && $belumLink) Masih <strong style="color: #B02A19">{{ $belumLink }} paket</strong> belum diberi link Shopee.@endif</p>
</section>

<div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-start">
<section aria-labelledby="pool" style="flex: 1 1 380px; min-width: 0; background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="padding: 16px 20px; border-bottom: 1px solid #F0EEE9; display: flex; justify-content: space-between; align-items: baseline; gap: 8px"><h2 id="pool" style="margin: 0; font-size: 16px; font-weight: 800">Siap kirim di gudang {{ $neg['nama'] }}</h2><span style="font-size: 12.5px; color: #4E515C">Foto sudah disetujui</span></div>
@if ($pool->isEmpty())
<p style="margin: 0; padding: 28px 20px; text-align: center; font-size: 14px; color: #4E515C">{{ $locked ? 'Batch ini sudah dikirim, isinya terkunci.' : 'Semua paket siap kirim sudah masuk batch.' }}</p>
@endif
@foreach ($pool as $p)
@php $nm = $p->user->displayName(); @endphp
<div style="display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 14px; align-items: center; padding: 12px 20px; border-bottom: 1px solid #F0EEE9">
<span style="display: flex; gap: 10px; align-items: center; min-width: 0"><span aria-hidden="true" style="width: 32px; height: 32px; border-radius: 50%; background: {{ avatar_bg($nm) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ inisial($nm) }}</span><span style="display: flex; flex-direction: column; gap: 1px; min-width: 0"><span style="font-size: 14px; font-weight: 700">{{ $nm }}</span><span style="font-size: 12px; color: #4E515C; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $p->code }} · {{ $prodOf($p) }}</span></span></span>
<span style="font-size: 13.5px; font-weight: 700; white-space: nowrap">{{ $kg($p->weight) }}</span>
<form method="POST" action="{{ route('admin.batches.toggle', [$batch, $p]) }}" style="margin: 0">@csrf<button type="submit" @disabled($locked) aria-label="Masukkan {{ $p->code }} ke batch" style="min-height: 38px; padding: 0 12px; border-radius: 10px; border: 1.5px solid {{ $locked ? '#CFCDC6' : '#15161C' }}; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 13px; color: {{ $locked ? '#767884' : '#15161C' }}; cursor: pointer; white-space: nowrap">+ Masukkan</button></form>
</div>
@endforeach
</section>

<section aria-labelledby="isi" style="flex: 1 1 420px; min-width: 0; background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="padding: 16px 20px; border-bottom: 1px solid #F0EEE9; display: flex; justify-content: space-between; align-items: baseline; gap: 8px"><h2 id="isi" style="margin: 0; font-size: 16px; font-weight: 800">Isi batch ini</h2><span style="font-size: 12.5px; color: #4E515C">{{ $isi->count() }} paket</span></div>
@forelse ($isi as $p)
@php $nm = $p->user->displayName(); @endphp
<div style="border-bottom: 1px solid #F0EEE9; background: #FAFAF8">
<div style="display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 14px; align-items: center; padding: 12px 20px">
<span style="display: flex; gap: 10px; align-items: center; min-width: 0"><span aria-hidden="true" style="width: 32px; height: 32px; border-radius: 50%; background: {{ avatar_bg($nm) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ inisial($nm) }}</span><span style="display: flex; flex-direction: column; gap: 1px; min-width: 0"><span style="font-size: 14px; font-weight: 700">{{ $nm }}</span><a href="{{ route('admin.orders', ['id' => $p->id]) }}" style="font-size: 12px; color: #4E515C; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $p->code }} · {{ $prodOf($p) }}</a></span></span>
<span style="display: flex; flex-direction: column; align-items: flex-end; gap: 1px; white-space: nowrap"><span style="font-size: 13.5px; font-weight: 700">{{ $kg($p->weight) }}</span><span style="font-size: 11.5px; color: #4E515C">ditagih {{ $p->billedKg() }} kg</span></span>
@if (! $locked)
<form method="POST" action="{{ route('admin.batches.toggle', [$batch, $p]) }}" style="margin: 0">@csrf<button type="submit" aria-label="Keluarkan {{ $p->code }} dari batch" style="min-height: 38px; padding: 0 10px; border: 0; background: transparent; font-family: inherit; font-weight: 700; font-size: 13px; color: #B02A19; cursor: pointer">Keluarkan</button></form>
@elseif ($batch->status >= 2)
@php $sp = $p->stage >= \App\Models\Order::DONE ? pill('green') + ['l' => 'Selesai'] : ($p->stage === \App\Models\Order::SHOPEE ? ($p->shopee_reported_at ? pill('red') + ['l' => 'Lapor checkout'] : pill('blue') + ['l' => 'Link terkirim']) : pill('amber') + ['l' => 'Belum ada link']); @endphp
<span style="font-size: 11.5px; font-weight: 700; color: {{ $sp['fg'] }}; background: {{ $sp['bg'] }}; padding: 3px 8px; border-radius: 999px; white-space: nowrap">{{ $sp['l'] }}</span>
@else
<span style="font-size: 12px; font-weight: 700; color: #4E515C">Terkunci</span>
@endif
</div>
@if ($batch->status >= 2)
<div style="padding: 0 20px 12px 62px; display: flex; flex-direction: column; gap: 8px">
<span style="font-size: 12.5px; color: #4E515C">Tagih di Shopee: ongkir {{ $p->billedKg() }} kg × {{ rp($p->tarif_jual) }} + {{ \App\Support\Harga::PACK_LABEL[$p->pack_type] ?? 'packing' }} {{ rp($p->pack_jual) }} = <strong style="color: #15161C">{{ rp($p->tahap2()) }}</strong></span>
@if ($p->stage === \App\Models\Order::ARRIVED)
<form method="POST" action="{{ route('admin.orders.shopee', $p) }}" style="margin: 0; display: flex; gap: 8px">
@csrf<input type="hidden" name="kembali" value="jadwal">
<label style="flex: 1; display: flex"><span class="sr-only">Link Shopee {{ $p->code }}</span><input type="url" name="shopee_link" required placeholder="https://shopee.co.id/…" style="flex: 1; min-width: 0; height: 40px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14px; color: #15161C; background: #FFFFFF"></label>
<button type="submit" style="min-height: 40px; padding: 0 14px; border: 0; border-radius: 10px; background: #C2311F; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 13.5px; white-space: nowrap; cursor: pointer">Kirim link</button>
</form>
@elseif ($p->stage === \App\Models\Order::SHOPEE)
<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; font-size: 13px">
<span style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px">
<span style="font-weight: 700">Pembeli harus {{ $p->user->shopee_username ? '@'.$p->user->shopee_username : '—' }} · <a href="{{ $p->shopee_link }}" target="_blank" rel="noopener" style="color: #1D4E9E">link</a></span>
@if ($p->shopee_reported_at)
<span style="font-weight: 800; color: #B02A19">Lapor checkout: No. {{ $p->shopee_order_no }}</span>
@else
<span style="color: {{ $p->shopeeWaitDays() >= 3 ? '#7A4B00' : '#4E515C' }}">Belum lapor checkout{{ $p->shopeeWaitDays() ? ' · '.$p->shopeeWaitDays().' hari' : '' }}</span>
@endif
</span>
<form method="POST" action="{{ route('admin.orders.complete', $p) }}" style="margin: 0">@csrf<input type="hidden" name="kembali" value="jadwal">@if($p->shopee_order_no)<input type="hidden" name="shopee_order_no" value="{{ $p->shopee_order_no }}">@endif<button type="submit" data-confirm="{{ $p->shopee_reported_at ? 'Pesanan Shopee '.$p->shopee_order_no.' sudah dicek di Seller Centre dan sesuai?' : 'Penitip belum lapor. Yakin sudah ada checkout dari akun ini di Seller Centre?' }}" style="min-height: 36px; padding: 0 12px; border: {{ $p->shopee_reported_at ? '0' : '1.5px solid #15161C' }}; border-radius: 10px; background: {{ $p->shopee_reported_at ? '#15161C' : '#FFFFFF' }}; font-family: inherit; font-weight: 700; font-size: 13px; color: {{ $p->shopee_reported_at ? '#FFFFFF' : '#15161C' }}; cursor: pointer">Tandai selesai</button></form>
</div>
@elseif ($p->stage < \App\Models\Order::ARRIVED)
<span style="font-size: 12.5px; color: #7A4B00">Belum ditandai tiba. <a href="{{ route('admin.orders', ['id' => $p->id]) }}" style="font-weight: 700; color: #7A4B00">Buka pesanan</a></span>
@endif
</div>
@endif
</div>
@empty
<p style="margin: 0; padding: 28px 20px; text-align: center; font-size: 14px; color: #4E515C">Belum ada paket. Masukkan dari daftar siap kirim.</p>
@endforelse
<dl style="margin: 0; padding: 16px 20px; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Ongkir ditagih ke penitip · {{ $bulatTotal }} kg × {{ rp($h['tj']) }}</dt><dd style="margin: 0; font-weight: 700">{{ rp($pendapatan) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Biaya kargo · {{ $kgKargo }} kg × {{ rp($h['tm']) }}</dt><dd style="margin: 0; font-weight: 700; color: #B02A19">{{ rp(-$biaya) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px; border-top: 1px dashed #CFCDC6; padding-top: 10px; font-size: 15.5px"><dt style="font-weight: 800">Untung ongkir batch ini</dt><dd style="margin: 0; font-weight: 800; color: #16653F">{{ rp($pendapatan - $biaya) }}</dd></div>
</dl>
</section>
</div>
@endif
@endsection
