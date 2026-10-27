@extends('layouts.admin')
@section('title', 'Admin · Jasa war')

@php
    $in = 'height: 44px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 10px; font-family: inherit; font-size: 15px; font-weight: 700; color: #15161C; background: #FFFFFF; min-width: 0; width: 100%; box-sizing: border-box';
    $inTxt = 'height: 44px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14.5px; color: #15161C; background: #FFFFFF; min-width: 0; width: 100%; box-sizing: border-box';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 12.5px; font-weight: 600; color: #4E515C';
    $d = $isNew ? null : $drop;
    $v = fn ($k, $def = '') => old($k, $d ? $d->{$k} : $def);
    $negSel = old('country', $d->country ?? 'jp');
    $local = $d ? $d->localRelease() : now()->addDays(7)->setTime(12, 0);
    $refund = old('refund_mode', $d->refund_mode ?? 'potong');
    $zona = negara($negSel)['zona'];
    $cols = '52px minmax(130px, 1.3fr) minmax(100px, 1fr) minmax(110px, 1fr) minmax(150px, 1fr) minmax(120px, 1.1fr)';
    if ($d) {
        $step = $d->step();
        $ents = $d->entries;
        $ng = $d->neg();
        $menang = $ents->where('result', 'menang')->count();
        $kalah = $ents->where('result', 'kalah')->count();
        $belum = $ents->count() - $menang - $kalah;
        $feeTotal = $ents->filter(fn ($e) => $e->result === 'menang' || ($e->result === 'kalah' && $d->refund_mode === 'potong'))->sum(fn ($e) => $e->fee * $e->qty);
        $dpTotal = $ents->filter(fn ($e) => $e->dp_proof)->sum('dp_amount');
        $slotPct = $d->quota ? min(100, round($ents->count() / $d->quota * 100)) : 0;
    }
@endphp

@section('main')
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Jasa war</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Pilih drop, lalu ikuti 4 langkahnya: pendaftaran → hari war → input hasil → pelunasan &amp; refund.</p>
</div>
<a href="{{ route('admin.war', ['baru' => 1]) }}" style="min-height: 46px; padding: 0 20px; border-radius: 12px; background: #15161C; color: #FFFFFF; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; text-decoration: none">+ Buat drop baru</a>
</div>

@if ($drops->isNotEmpty())
<div role="group" aria-label="Pilih drop" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px">
@foreach ($drops as $x)
@php
    $on = $d && $x->id === $d->id;
    [$stl, $stc] = \App\Models\WarDrop::STEP_STATUS[$x->step()];
    $sp = pill($stc);
    $xn = $x->neg();
@endphp
<a href="{{ route('admin.war', ['drop' => $x->id]) }}" aria-pressed="{{ $on ? 'true' : 'false' }}" class="adm-card" style="text-align: left; border: 1.5px solid {{ $on ? '#1D4E9E' : '#E6E4DF' }}; background: {{ $on ? '#F3F6FC' : '#FFFFFF' }}; border-radius: 16px; padding: 16px; text-decoration: none; color: #15161C; display: flex; flex-direction: column; gap: 8px">
<span style="display: flex; justify-content: space-between; gap: 8px; align-items: center"><span style="font-size: 12px; font-weight: 700; color: {{ $xn['fg'] }}">{{ $x->group_name ?: 'War' }} · {{ $xn['nama'] }}</span><span style="font-size: 11.5px; font-weight: 700; color: {{ $sp['fg'] }}; background: {{ $sp['bg'] }}; padding: 3px 9px; border-radius: 999px; white-space: nowrap">{{ $stl }}</span></span>
<span style="font-size: 15.5px; font-weight: 800">{{ $x->name }}</span>
<span style="font-size: 13px; color: #4E515C">Rilis {{ tgl($x->release_at, 'j M') }} · {{ $x->release_at->format('H:i') }} WIB · {{ $x->entries_count }} dari {{ $x->quota }} slot</span>
</a>
@endforeach
</div>
@endif

<div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-start">
<div style="flex: 3 1 560px; min-width: 0; display: flex; flex-direction: column; gap: 16px">

@if ($d)
<section aria-labelledby="dtitle" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 18px">
<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 4px"><span style="font-size: 12px; font-weight: 700; color: {{ $ng['fg'] }}">{{ $d->group_name ?: 'War' }} · {{ $ng['nama'] }}</span><h2 id="dtitle" style="margin: 0; font-size: 21px; font-weight: 800">{{ $d->name }}</h2></div>
@if ($step < 2)
<form method="POST" action="{{ route('admin.war.step', $d) }}" style="margin: 0">@csrf<input type="hidden" name="step" value="{{ $step + 1 }}"><button type="submit" data-confirm="{{ \App\Models\WarDrop::NEXT_LABEL[$step] }}?" style="min-height: 46px; padding: 0 18px; border: 0; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14.5px; cursor: pointer">{{ \App\Models\WarDrop::NEXT_LABEL[$step] }}</button></form>
@elseif ($step === 2)
<form method="POST" action="{{ route('admin.war.results', $d) }}" style="margin: 0">@csrf<button type="submit" @disabled($menang + $kalah === 0) data-confirm="Kirim email hasil ke {{ $menang + $kalah }} peserta?" title="{{ $menang + $kalah ? '' : 'Isi hasil war dulu' }}" style="min-height: 46px; padding: 0 18px; border: 0; border-radius: 12px; background: {{ $menang + $kalah ? '#C2311F' : '#E6E4DF' }}; color: {{ $menang + $kalah ? '#FFFFFF' : '#4E515C' }}; font-family: inherit; font-weight: 700; font-size: 14.5px; cursor: pointer">Kirim hasil &amp; tagihan</button></form>
@else
<form method="POST" action="{{ route('admin.war.results', $d) }}" style="margin: 0">@csrf<button type="submit" data-confirm="Kirim ulang email hasil ke peserta yang belum lunas / belum direfund?" style="min-height: 46px; padding: 0 18px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; color: #15161C; font-family: inherit; font-weight: 700; font-size: 14px; cursor: pointer">Kirim ulang hasil</button></form>
@endif
</div>
<ol aria-label="Langkah drop" class="adm-steps4" style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px">
@foreach (\App\Models\WarDrop::STEPS as $i => $l)
@php $done = $i < $step; $now = $i === $step; @endphp
<li @if($now) aria-current="step" @endif style="border: 1.5px solid {{ $now ? '#1D4E9E' : '#E6E4DF' }}; background: {{ $now ? '#EAF0FA' : '#FFFFFF' }}; border-radius: 12px; padding: 10px 12px; display: flex; gap: 10px; align-items: center">
<span style="width: 26px; height: 26px; border-radius: 50%; background: {{ $done ? '#1D7A4C' : ($now ? '#1D4E9E' : '#F0EEE9') }}; color: {{ ($done || $now) ? '#FFFFFF' : '#4E515C' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ $done ? '✓' : $i + 1 }}</span>
<span style="font-size: 13px; font-weight: {{ $now ? 800 : 600 }}; line-height: 1.25">{{ $l }}</span>
</li>
@endforeach
</ol>
<dl style="margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px">
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px 14px"><dt style="font-size: 12.5px; color: #4E515C; font-weight: 600">Rilis {{ tgl($d->release_at, 'j M') }}</dt><dd style="margin: 2px 0 0; font-size: 17px; font-weight: 800">{{ $d->release_at->format('H:i') }} WIB</dd><dd style="margin: 0; font-size: 12px; color: #4E515C">{{ $d->localRelease()->format('H:i') }} {{ $ng['zona'] }}</dd></div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px 14px"><dt style="font-size: 12.5px; color: #4E515C; font-weight: 600">Slot terisi</dt><dd style="margin: 2px 0 6px; font-size: 17px; font-weight: 800">{{ $ents->count() }} / {{ $d->quota }}</dd><dd aria-hidden="true" style="margin: 0; height: 6px; border-radius: 3px; background: #E6E4DF; display: flex"><span style="width: {{ $slotPct }}%; background: #1D4E9E; border-radius: 3px"></span></dd></div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px 14px"><dt style="font-size: 12.5px; color: #4E515C; font-weight: 600">DP {{ $d->dp_pct }}% terkumpul</dt><dd style="margin: 2px 0 0; font-size: 17px; font-weight: 800">{{ rp($dpTotal) }}</dd></div>
<div style="background: #E6F4EC; border-radius: 12px; padding: 12px 14px"><dt style="font-size: 12.5px; color: #16653F; font-weight: 700">Fee war didapat</dt><dd style="margin: 2px 0 0; font-size: 17px; font-weight: 800; color: #16653F">{{ rp($feeTotal) }}</dd></div>
</dl>
@if ($d->results_sent_at)<p style="margin: -6px 0 0; font-size: 12.5px; color: #4E515C">Hasil dikirim {{ tgl($d->results_sent_at, 'j M H:i') }}. Yang menang dapat tagihan sisa {{ 100 - $d->dp_pct }}%, yang kalah masuk daftar refund DP.</p>@endif
</section>

<section aria-labelledby="pes" style="background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="padding: 16px 20px; border-bottom: 1px solid #F0EEE9; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap">
<h2 id="pes" style="margin: 0; font-size: 17px; font-weight: 800">Peserta ({{ $ents->count() }})</h2>
<div style="display: flex; gap: 8px; font-size: 12.5px; font-weight: 700">
<span style="color: #16653F; background: #E6F4EC; padding: 4px 10px; border-radius: 999px">Menang {{ $menang }}</span>
<span style="color: #B02A19; background: #FBEDEA; padding: 4px 10px; border-radius: 999px">Kalah {{ $kalah }}</span>
<span style="color: #4E515C; background: #F0EEE9; padding: 4px 10px; border-radius: 999px">Belum {{ $belum }}</span>
</div>
</div>
<div style="overflow-x: auto">
<div style="min-width: 750px">
<div style="background: #FAFAF8; border-bottom: 1px solid #E6E4DF; display: grid; grid-template-columns: {{ $cols }}; gap: 12px; padding: 10px 16px; font-size: 12px; font-weight: 700; color: #4E515C"><span>Antrian</span><span>Penitip</span><span>Varian</span><span>Pembayaran</span><span>Hasil war</span><span>Langkah berikutnya</span></div>
@forelse ($ents as $e)
@php
    $nm = $e->user->displayName();
    $win = $e->result === 'menang';
    $lose = $e->result === 'kalah';
    $final = $e->order_id || $e->refunded_at || $e->settle_proof;
    $ws = \App\Support\Alur::warStep($e);
    if ($step < 2 && ! $e->result) {
        $act = [$step === 1 ? 'Sedang di-war' : 'Menunggu hari war', 'wait'];
    } elseif (! $e->result) {
        $act = ['Isi hasil war', 'need'];
    } else {
        $act = [$ws['t'], $ws['kind']];
    }
    $kc = \App\Support\Alur::kindColor($act[1]);
    $hl = (int) request('entry') === $e->id;
@endphp
<div id="entri-{{ $e->id }}" style="display: grid; grid-template-columns: {{ $cols }}; gap: 12px; align-items: center; padding: 12px 16px; border-bottom: 1px solid #F0EEE9; font-size: 14px; background: {{ $hl ? '#F3F6FC' : 'transparent' }}; box-shadow: {{ $hl ? 'inset 3px 0 0 #1D4E9E' : 'none' }}">
<span style="font-size: 18px; font-weight: 800">#{{ $e->queue_no }}</span>
<span style="display: flex; gap: 10px; align-items: center; min-width: 0"><span aria-hidden="true" style="width: 32px; height: 32px; border-radius: 50%; background: {{ avatar_bg($nm) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex: none">{{ inisial($nm) }}</span><span style="display: flex; flex-direction: column; gap: 1px; min-width: 0"><a href="{{ route('admin.customers', ['id' => $e->user_id]) }}" style="font-weight: 700; text-decoration: none">{{ $nm }}</a><span style="font-size: 12px; color: {{ $e->user->is_flagged ? '#B02A19' : '#4E515C' }}">WAR-{{ str_pad((string) $e->id, 4, '0', STR_PAD_LEFT) }}{{ $e->user->is_flagged ? ' · bermasalah' : '' }}</span></span></span>
<span style="display: flex; flex-direction: column; gap: 1px"><span style="font-weight: 700">{{ $e->variant_main }}{{ $e->qty > 1 ? ' × '.$e->qty : '' }}</span><span style="font-size: 12px; color: #4E515C">Cadangan: {{ $e->variant_backup ?: 'Tidak ada' }}</span></span>
<span style="display: flex; flex-direction: column; gap: 5px"><span style="font-size: 13px; font-weight: 700">{{ $ws['payLabel'] }}</span><span aria-hidden="true" style="height: 5px; border-radius: 3px; background: #E6E4DF; display: flex; width: 100px"><span style="width: {{ $ws['pay'] }}%; background: #1D4E9E; border-radius: 3px"></span></span>
@if ($e->dp_proof && ! $e->result)<a href="{{ upload_url($e->dp_proof) }}" target="_blank" style="font-size: 12px; font-weight: 600; color: #4E515C">lihat bukti DP</a>@endif
@if ($e->settle_proof && ! $e->order_id)<a href="{{ upload_url($e->settle_proof) }}" target="_blank" style="font-size: 12px; font-weight: 600; color: #4E515C">lihat bukti pelunasan</a>@endif
</span>
<span>
@if ($step >= 2 && ! $final)
<span role="group" aria-label="Hasil war {{ $nm }}" style="display: flex; gap: 6px">
<form method="POST" action="{{ route('admin.war.result', $e) }}" style="margin: 0">@csrf<input type="hidden" name="result" value="menang"><button type="submit" aria-pressed="{{ $win ? 'true' : 'false' }}" style="min-height: 38px; padding: 0 12px; border-radius: 10px; border: 1.5px solid {{ $win ? '#1D7A4C' : '#CFCDC6' }}; background: {{ $win ? '#1D7A4C' : '#FFFFFF' }}; color: {{ $win ? '#FFFFFF' : '#15161C' }}; font-family: inherit; font-weight: 700; font-size: 13px; cursor: pointer">Menang</button></form>
<form method="POST" action="{{ route('admin.war.result', $e) }}" style="margin: 0">@csrf<input type="hidden" name="result" value="kalah"><button type="submit" aria-pressed="{{ $lose ? 'true' : 'false' }}" style="min-height: 38px; padding: 0 12px; border-radius: 10px; border: 1.5px solid {{ $lose ? '#C2311F' : '#CFCDC6' }}; background: {{ $lose ? '#C2311F' : '#FFFFFF' }}; color: {{ $lose ? '#FFFFFF' : '#15161C' }}; font-family: inherit; font-weight: 700; font-size: 13px; cursor: pointer">Kalah</button></form>
</span>
@elseif ($e->result)
@php $rp = pill($win ? 'green' : 'red'); @endphp
<span style="font-size: 12px; font-weight: 700; color: {{ $rp['fg'] }}; background: {{ $rp['bg'] }}; padding: 4px 10px; border-radius: 999px">{{ $win ? 'Menang' : 'Kalah' }}</span>
@else
<span style="font-size: 13px; color: #4E515C">Belum war</span>
@endif
</span>
<span style="display: flex; flex-direction: column; align-items: flex-start; gap: 6px">
<span style="display: flex; gap: 8px; align-items: center; font-size: 13.5px; font-weight: 700; color: {{ $kc['fg'] }}"><span aria-hidden="true" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $kc['dot'] }}; flex: none"></span>{{ $act[0] }}</span>
@if ($win && ! $e->order_id && ($step >= 3 || $e->settle_proof))
<form method="POST" action="{{ route('admin.war.settled', $e) }}" style="margin: 0">@csrf<button type="submit" data-confirm="Konfirmasi pelunasan {{ rp($e->remaining()) }} dan buat pesanan?" style="min-height: 34px; padding: 0 12px; border-radius: 9px; border: 0; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 12.5px; cursor: pointer">Konfirmasi lunas</button></form>
@elseif ($lose && ! $e->refunded_at && $step >= 3)
<form method="POST" action="{{ route('admin.war.refunded', $e) }}" style="margin: 0">@csrf<button type="submit" data-confirm="Tandai refund {{ rp($e->refund_amount) }} sudah ditransfer ke {{ $e->user->refund_account ?: 'penitip' }}?" style="min-height: 34px; padding: 0 12px; border-radius: 9px; border: 1.5px solid #15161C; background: #FFFFFF; color: #15161C; font-family: inherit; font-weight: 700; font-size: 12.5px; cursor: pointer">Refund ditransfer</button></form>
@elseif ($e->order_id)
<a href="{{ route('admin.orders', ['id' => $e->order_id]) }}" style="font-size: 12.5px; font-weight: 700">Buka {{ $e->order?->code }} →</a>
@endif
</span>
</div>
@empty
<p style="margin: 0; padding: 24px 20px; color: #4E515C; font-size: 14px; text-align: center">Belum ada peserta.</p>
@endforelse
</div>
</div>
</section>
@else
<div style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 6px">
<h2 style="margin: 0; font-size: 21px; font-weight: 800">Drop baru</h2>
<p style="margin: 0; font-size: 14px; color: #4E515C">Isi pengaturan drop di sebelah kanan, lalu klik Buat drop. Setelah dibuat, drop tampil di halaman Jasa war penitip.</p>
</div>
@endif
</div>

<div style="flex: 1 1 300px; min-width: 0; display: flex; flex-direction: column; gap: 16px">
<form method="POST" action="{{ $d ? route('admin.war.update', $d) : route('admin.war.store') }}" enctype="multipart/form-data" style="margin: 0">
@csrf
<section aria-labelledby="set" style="background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
<h2 id="set" style="margin: 0; font-size: 16px; font-weight: 800">{{ $d ? 'Pengaturan drop' : 'Drop baru' }}</h2>

<details @if(! $d || $errors->any()) open @endif style="border: 1px solid #E6E4DF; border-radius: 12px; padding: 0 12px">
<summary style="min-height: 44px; display: flex; align-items: center; justify-content: space-between; font-size: 13.5px; font-weight: 700; cursor: pointer; list-style: none">Produk &amp; rilis</summary>
<div style="display: flex; flex-direction: column; gap: 10px; padding-bottom: 12px">
<label style="{{ $lab }}">Nama produk<input type="text" name="name" required value="{{ $v('name') }}" placeholder="Nama album / MD limited" style="{{ $inTxt }}"></label>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Grup / artis<input type="text" name="group_name" value="{{ $v('group_name') }}" placeholder="Nama grup" style="{{ $inTxt }}"></label>
<label style="{{ $lab }}">Nama toko<input type="text" name="store_name" value="{{ $v('store_name') }}" placeholder="Toko resmi" style="{{ $inTxt }}"></label>
</div>
<label style="{{ $lab }}">Link halaman rilis<input type="url" name="release_url" value="{{ $v('release_url') }}" placeholder="https://…" style="{{ $inTxt }}"></label>
<div role="radiogroup" aria-label="Negara" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<label class="opt opt-jp" style="min-height: 42px; border-radius: 10px; font-size: 14px"><input type="radio" name="country" value="jp" @checked($negSel === 'jp')>Jepang</label>
<label class="opt opt-kr" style="min-height: 42px; border-radius: 10px; font-size: 14px"><input type="radio" name="country" value="kr" @checked($negSel === 'kr')>Korea</label>
</div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Tanggal rilis<input type="date" name="release_date" required value="{{ old('release_date', $local->format('Y-m-d')) }}" style="{{ $inTxt }}; padding: 0 8px"></label>
<label style="{{ $lab }}">Harga / barang (¥/₩)<input type="number" name="price" step="any" min="0" required value="{{ $v('price', '') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Maks / orang<input type="number" name="max_per_person" min="1" required value="{{ $v('max_per_person', 2) }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Tutup (jam sblm rilis)<input type="number" name="close_hours" min="0" required value="{{ $v('close_hours', 6) }}" style="{{ $in }}"></label>
</div>
<label style="{{ $lab }}">Varian (pisahkan dengan koma)<input type="text" name="variants" value="{{ old('variants', $d ? implode(', ', (array) $d->variants) : '') }}" placeholder="Ver. A, Ver. B, Ver. C" style="{{ $inTxt }}"></label>
<label class="drop" style="min-height: 56px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 8px; box-sizing: border-box">
@if ($d && $d->image)<img src="{{ upload_url($d->image) }}" alt="" class="ph" style="width: 40px; height: 40px; border-radius: 8px">@endif
<input type="file" name="image" data-file accept="image/*"><span data-file-name data-empty="{{ $d && $d->image ? 'Ganti foto produk' : 'Upload foto produk' }}">{{ $d && $d->image ? 'Ganti foto produk' : 'Upload foto produk' }}</span>
</label>
</div>
</details>

<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Jam rilis ({{ $zona }})<input type="time" id="jam-lokal" name="release_time" required value="{{ old('release_time', $local->format('H:i')) }}" style="{{ $in }}"></label>
<div style="{{ $lab }}" data-wib-from="jam-lokal">Tampil ke penitip<span style="height: 44px; display: flex; align-items: center; padding: 0 10px; border-radius: 10px; background: #EAF0FA; font-size: 15px; font-weight: 800; color: #1A3F7F; box-sizing: border-box"><span data-wib-out>{{ $local->copy()->subHours(2)->format('H:i') }}</span>&nbsp;WIB</span></div>
<label style="{{ $lab }}">Fee war (Rp)<input type="number" name="fee" step="1000" min="0" required value="{{ $v('fee', 25000) }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">DP (%)<input type="number" name="dp_pct" min="1" max="100" required value="{{ $v('dp_pct', 30) }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Kuota slot<input type="number" name="quota" min="1" required value="{{ $v('quota', 10) }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Batas lunas (jam)<input type="number" name="settle_hours" min="1" required value="{{ $v('settle_hours', 24) }}" style="{{ $in }}"></label>
</div>
<div role="radiogroup" aria-labelledby="ref" style="display: flex; flex-direction: column; gap: 6px">
<span id="ref" style="font-size: 12.5px; font-weight: 600; color: #4E515C">Kalah war, DP dikembalikan…</span>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px">
<label class="opt opt-neutral" style="min-height: 40px; border-radius: 10px; font-size: 13px"><input type="radio" name="refund_mode" value="potong" @checked($refund === 'potong')>Dipotong fee war</label>
<label class="opt opt-neutral" style="min-height: 40px; border-radius: 10px; font-size: 13px"><input type="radio" name="refund_mode" value="penuh" @checked($refund === 'penuh')>Penuh</label>
</div>
</div>
<button type="submit" style="min-height: 46px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14.5px; cursor: pointer">{{ $d ? 'Simpan pengaturan' : 'Buat drop' }}</button>
@if ($d && $d->group_name)
<button type="submit" form="notif-form" data-confirm="Kirim email ke {{ $fans }} penitip yang suka {{ $d->group_name }}?" style="min-height: 44px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px; color: #15161C; cursor: pointer">Kabari fans {{ $d->group_name }} lewat email ({{ $fans }})</button>
@endif
@if ($d)
<a href="{{ route('war.register', $d) }}" target="_blank" style="font-size: 13px; font-weight: 700; color: #4E515C; text-align: center">Lihat halaman daftar penitip ↗</a>
@endif
</section>
</form>
@if ($d && $d->group_name)
<form id="notif-form" method="POST" action="{{ route('admin.war.notify', $d) }}" hidden>@csrf</form>
@endif

@if (! $d || $step <= 1)
<section aria-labelledby="cek" style="background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 10px">
<h2 id="cek" style="margin: 0; font-size: 16px; font-weight: 800">Checklist sebelum rilis</h2>
<p style="margin: 0; font-size: 13px; color: #C9CAD1">Siapkan 15 menit sebelum <span data-wib-out>{{ $local->copy()->subHours(2)->format('H:i') }}</span> WIB.</p>
@foreach (['Akun toko sudah login', 'Alamat warehouse tersimpan', 'Saldo cukup untuk semua slot', 'Urutan varian per peserta dicek'] as $i => $t)
<label style="display: flex; gap: 10px; align-items: center; min-height: 36px; font-size: 14px"><input type="checkbox" data-no-auto data-remember="cek-{{ $d->id ?? 'baru' }}-{{ $i }}" style="width: 18px; height: 18px; margin: 0">{{ $t }}</label>
@endforeach
</section>
@endif
</div>
</div>
@endsection
