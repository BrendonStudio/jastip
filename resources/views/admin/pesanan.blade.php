@extends('layouts.admin')
@section('title', 'Admin · Pesanan')
@section('full', '1')

@php
    $in = 'height: 46px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF; min-width: 0; width: 100%; box-sizing: border-box';
    $box = 'height: 46px; display: flex; align-items: center; padding: 0 12px; border-radius: 10px; background: #F4F3EF; font-size: 15px; font-weight: 800; color: #15161C; box-sizing: border-box';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $btn = 'width: 100%; min-height: 50px; padding: 0 18px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none; box-sizing: border-box';
    $btnLine = 'width: 100%; min-height: 46px; padding: 0 18px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px; color: #15161C; cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none; box-sizing: border-box';
    $kicker = 'font-size: 12px; font-weight: 800; letter-spacing: 0.04em; color: #1D4E9E';
    $h3 = 'margin: 0; font-size: 19px; font-weight: 800';
    $desc = 'margin: 0; font-size: 14px; line-height: 1.55; color: #4E515C';
    $dl = 'margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px; padding: 14px; background: #F7F6F2; border-radius: 12px';
    $cols = 'minmax(90px, 0.6fr) minmax(180px, 1.2fr) minmax(200px, 1.5fr) minmax(250px, 1.5fr) minmax(170px, 1.1fr) minmax(110px, 0.7fr)';
    $isWar = $tab === 'war';
    $keep = fn ($extra = []) => array_filter(array_merge(['tab' => $isWar ? 'war' : null, 'pos' => $pos, 'q' => $q ?: null], $extra), fn ($v) => $v !== null && $v !== '');
    $warNo = fn ($e) => 'WAR-'.str_pad((string) $e->id, 4, '0', STR_PAD_LEFT);
    $o = $order;
    if ($o) {
        $neg = $o->neg();
        $sym = $neg['sym'];
        $suggest = $o->items->sum(fn ($i) => ($i->web_price ?? 0) * $i->qty);
        $totalAsing = $o->stage >= 1 ? $o->foreignTotal() : $suggest;
        $oPos = \App\Support\Alur::pos($o);
        $allDone = $o->stage >= \App\Models\Order::DONE;
    }
@endphp

@section('main')
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Pesanan</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Lihat posisi tiap barang dan langkah berikutnya dalam satu tabel.</p>
</div>
<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap">
<div role="tablist" aria-label="Jenis pesanan" style="display: flex; background: #E9E7E1; border-radius: 12px; padding: 4px; gap: 4px">
@foreach (['titip' => 'Titip · '.$nTitip, 'war' => 'Pendaftar war · '.$nWar] as $id => $label)
<a href="{{ route('admin.orders', array_filter(['tab' => $id === 'war' ? 'war' : null, 'q' => $q ?: null])) }}" role="tab" aria-selected="{{ $tab === $id ? 'true' : 'false' }}" class="seg {{ $tab === $id ? 'is-on' : '' }}" style="min-height: 38px; padding: 0 16px; display: inline-flex; align-items: center; text-decoration: none; white-space: nowrap; box-sizing: border-box">{{ $label }}</a>
@endforeach
</div>
<form method="GET" action="{{ route('admin.orders') }}" style="display: flex; width: 280px; max-width: 100%; margin: 0">
@if ($isWar)<input type="hidden" name="tab" value="war">@endif
<label style="display: flex; flex: 1"><span class="sr-only">Cari pesanan</span><input type="search" name="q" value="{{ $q }}" placeholder="Cari kode, nama, atau produk" style="flex: 1; min-width: 0; height: 46px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 14px; color: #15161C; background: #FFFFFF"></label>
</form>
</div>
</div>

<div role="group" aria-label="Filter posisi" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; flex: none">
@foreach ([-1 => ['t' => 'Semua', 'sub' => $isWar ? 'Semua pendaftar' : 'Semua pesanan']] + $groups as $i => $g)
@php
    $on = ($i === -1 && $pos === null) || ($i >= 0 && $pos === $i);
    $n = $i === -1 ? $nAll : $counts[$i];
@endphp
<a href="{{ route('admin.orders', $keep(['pos' => $i >= 0 ? $i : null])) }}" aria-pressed="{{ $on ? 'true' : 'false' }}" class="ord-pos" style="text-align: left; border: 1.5px solid {{ $on ? '#15161C' : '#E6E4DF' }}; background: {{ $on ? '#15161C' : '#FFFFFF' }}; color: {{ $on ? '#FFFFFF' : '#15161C' }}; border-radius: 14px; padding: 12px 14px; text-decoration: none; display: flex; flex-direction: column; gap: 4px">
<span style="font-size: 22px; font-weight: 800; line-height: 1.1">{{ $n }}</span>
<span style="font-size: 13px; font-weight: 700">{{ $g['t'] }}</span>
<span style="font-size: 11.5px; font-weight: 500; color: {{ $on ? '#C9CAD1' : '#4E515C' }}">{{ $g['sub'] }}</span>
</a>
@endforeach
</div>

<section aria-label="Daftar pesanan" class="ord-table" style="flex: 1; min-height: 0; background: #FFFFFF; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden">
<div style="flex: 1; min-height: 0; overflow: auto">
<div style="min-width: 1080px">
<div style="position: sticky; top: 0; background: #FAFAF8; border-bottom: 1px solid #E6E4DF; display: grid; grid-template-columns: {{ $cols }}; gap: 16px; padding: 12px 20px; font-size: 12px; font-weight: 700; color: #4E515C; z-index: 1">
<span>Pesanan</span><span>Penitip</span><span>{{ $isWar ? 'Drop' : 'Barang' }}</span><span>Posisi</span><span>Langkah berikutnya</span><span style="text-align: right">{{ $isWar ? 'DP' : 'Tahap 1' }}</span>
</div>
@if ($q && $list->isNotEmpty())
<div style="padding: 10px 20px; border-bottom: 1px solid #F0EEE9; font-size: 13px; color: #4E515C">Hasil pencarian "{{ $q }}" · <a href="{{ route('admin.orders', array_filter(['tab' => $isWar ? 'war' : null])) }}" style="font-weight: 700">hapus pencarian</a></div>
@endif
@if ($list->isEmpty() && $q)
<div style="padding: 48px 20px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px">
<span aria-hidden="true" style="width: 60px; height: 60px; border-radius: 50%; background: #F0EEE9; color: #4E515C; display: flex; align-items: center; justify-content: center"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg></span>
<h2 style="margin: 4px 0 0; font-size: 18px; font-weight: 800">Tidak ada yang cocok dengan "{{ $q }}"</h2>
<p style="margin: 0; font-size: 14.5px; color: #4E515C">Coba cari pakai nama penitip, email, atau nama produk.</p>
<a href="{{ route('admin.orders', array_filter(['tab' => $isWar ? 'war' : null])) }}" style="margin-top: 6px; display: inline-flex; align-items: center; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 14px">Hapus pencarian</a>
</div>
@elseif ($list->isEmpty())
<p style="margin: 0; padding: 40px 20px; text-align: center; font-size: 14px; color: #4E515C">{{ $isWar ? 'Tidak ada pendaftar di posisi ini.' : 'Tidak ada pesanan di posisi ini.' }}</p>
@endif

@foreach ($list as $x)
@php
    if ($isWar) {
        $on = $entry && $entry->id === $x->id;
        $g = \App\Support\Alur::warIndex($x);
        $st = \App\Support\Alur::warStep($x);
        $ng = $x->drop->neg();
        $rid = $warNo($x);
        $href = route('admin.orders', $keep(['war' => $x->id]));
        $prod = $x->drop->name.' · '.$x->variant_main;
        $grp = $x->drop->group_name ?: 'War';
        $total = 'DP '.rp($x->dp_amount);
    } else {
        $on = $o && $o->id === $x->id;
        $g = \App\Support\Alur::pos($x);
        $st = \App\Support\Alur::step($x);
        $ng = $x->neg();
        $rid = $x->code;
        $href = route('admin.orders', $keep(['id' => $x->id]));
        $prod = $x->title();
        $grp = $x->type === 'war' ? 'War'.($x->warEntry?->drop?->group_name ? ' '.$x->warEntry->drop->group_name : '') : 'Titip';
        $total = $x->tahap1() ? rp($x->tahap1()) : '—';
    }
    $kc = \App\Support\Alur::kindColor($st['kind']);
    $nm = $x->user?->displayName();
    $pcol = ($isWar ? \App\Support\Alur::WAR_COLOR : \App\Support\Alur::POS_COLOR)[$g];
    $posSub = (! $isWar && $g === 2) ? 'Gudang '.$ng['nama'] : $groups[$g]['sub'].' · '.lalu($x->updated_at);
@endphp
<a href="{{ $href }}" aria-current="{{ $on ? 'true' : 'false' }}" class="ord-tr" style="border-bottom: 1px solid #F0EEE9; border-left: 3px solid {{ $on ? '#1D4E9E' : 'transparent' }}; background: {{ $on ? '#F3F6FC' : '#FFFFFF' }}; display: grid; grid-template-columns: {{ $cols }}; gap: 16px; align-items: center; padding: 14px 20px 14px 17px; color: #15161C; text-decoration: none">
<span style="display: flex; flex-direction: column; gap: 3px"><span style="font-size: 14px; font-weight: 800">{{ $rid }}</span><span style="font-size: 12px; color: #4E515C">{{ yg_lalu($x->updated_at) }}</span></span>
<span style="display: flex; gap: 10px; align-items: center; min-width: 0"><span aria-hidden="true" style="width: 34px; height: 34px; border-radius: 50%; background: {{ avatar_bg($nm) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800; flex: none">{{ inisial($nm) }}</span><span style="display: flex; flex-direction: column; gap: 2px; min-width: 0"><span style="font-size: 14px; font-weight: 700">{{ $nm }}</span><span style="font-size: 12px; color: #4E515C; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $x->user?->email }}</span></span></span>
<span style="display: flex; flex-direction: column; gap: 3px; min-width: 0"><span style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $prod }}</span><span style="font-size: 12px; color: #4E515C">{{ $grp }} · <span style="color: {{ $ng['fg'] }}; font-weight: 700">{{ $ng['nama'] }}</span></span></span>
<span style="display: flex; flex-direction: column; gap: 8px; min-width: 0">
<span style="display: flex; align-items: baseline; gap: 6px; white-space: nowrap; overflow: hidden"><span style="font-size: 14px; font-weight: 800; color: {{ $pcol['fg'] }}">{{ $groups[$g]['t'] }}</span><span style="font-size: 12px; color: #4E515C; overflow: hidden; text-overflow: ellipsis">· {{ $posSub }}</span></span>
@include('admin._pos-track', ['cur' => $g, 'war' => $isWar])
</span>
<span style="display: flex; gap: 8px; align-items: center; font-size: 13.5px; font-weight: 700; color: {{ $kc['fg'] }}"><span aria-hidden="true" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $kc['dot'] }}; flex: none"></span>{{ $st['t'] }}</span>
<span style="text-align: right; font-size: 14px; font-weight: 800">{{ $total }}</span>
</a>
@endforeach
</div>
</div>
</section>

{{-- ======================= DRAWER: PENDAFTAR WAR ======================= --}}
@if ($isWar && $entry)
@php
    $e = $entry;
    $wi = \App\Support\Alur::warIndex($e);
    $ws = \App\Support\Alur::warStep($e);
    $ng = $e->drop->neg();
    $nm = $e->user?->displayName();
@endphp
<aside class="ord-drawer" aria-labelledby="dr-title" style="position: absolute; top: 0; right: 0; bottom: 0; width: 440px; max-width: 100%; background: #FFFFFF; box-shadow: -20px 0 50px rgba(21,22,28,0.16); display: flex; flex-direction: column; z-index: 5">
@include('admin._drawer-head', ['nm' => $nm, 'sub' => $warNo($e).' · War · ', 'ng' => $ng, 'user' => $e->user, 'close' => route('admin.orders', $keep())])
<div style="flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 18px">
@include('admin._drawer-route', ['groups' => \App\Support\Alur::WAR, 'cur' => $wi, 'posisi' => \App\Support\Alur::WAR[$wi]['t'], 'posSub' => \App\Support\Alur::WAR[$wi]['sub'].' · '.yg_lalu($e->updated_at)])
<dl style="margin: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; font-size: 13.5px">
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px"><dt style="color: #4E515C">Antrian</dt><dd style="margin: 2px 0 0; font-size: 24px; font-weight: 800">#{{ $e->queue_no }}</dd></div>
<div style="background: #F7F6F2; border-radius: 12px; padding: 12px"><dt style="color: #4E515C">Varian</dt><dd style="margin: 2px 0 0; font-size: 15px; font-weight: 800">{{ $e->variant_main }} × {{ $e->qty }}</dd>@if($e->variant_backup)<dd style="margin: 2px 0 0; font-size: 12px; color: #4E515C">Cadangan: {{ $e->variant_backup }}</dd>@endif</div>
<div style="grid-column: 1 / -1; background: #F7F6F2; border-radius: 12px; padding: 12px"><dt style="color: #4E515C">Produk</dt><dd style="margin: 2px 0 0; font-size: 15px; font-weight: 800">{{ $e->drop->name }}</dd><dd style="margin: 2px 0 0; font-size: 12px; color: #4E515C">Rilis {{ tgl($e->drop->release_at, 'j M') }} · {{ $e->drop->release_at->format('H:i') }} WIB · total {{ rp($e->total) }}</dd></div>
</dl>
<div style="display: flex; flex-direction: column; gap: 8px">
<div style="display: flex; justify-content: space-between; gap: 8px; font-size: 13.5px"><span style="font-weight: 700">Pembayaran</span><span style="color: #4E515C">{{ $ws['payLabel'] }}</span></div>
<div role="img" aria-label="Pembayaran {{ $ws['pay'] }}%" style="height: 8px; border-radius: 4px; background: #F0EEE9; display: flex"><span style="width: {{ $ws['pay'] }}%; background: #1D4E9E; border-radius: 4px"></span></div>
<div style="display: flex; gap: 12px; flex-wrap: wrap; font-size: 12.5px">
@if ($e->dp_proof)<a href="{{ upload_url($e->dp_proof) }}" target="_blank" style="font-weight: 700; color: #1D4E9E">Bukti DP {{ rp($e->dp_amount) }}</a>@endif
@if ($e->settle_proof)<a href="{{ upload_url($e->settle_proof) }}" target="_blank" style="font-weight: 700; color: #1D4E9E">Bukti pelunasan</a>@endif
</div>
</div>
<section aria-labelledby="wa" style="border: 2px solid {{ $ws['kind'] === 'done' ? '#1D7A4C' : '#1D4E9E' }}; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 12px">
<span style="{{ $kicker }}; color: {{ $ws['kind'] === 'done' ? '#16653F' : '#1D4E9E' }}">LANGKAH BERIKUTNYA</span>
<h3 id="wa" style="{{ $h3 }}">{{ $ws['title'] }}</h3>
<p style="{{ $desc }}">{{ $ws['desc'] }}</p>
@if ($e->result === 'menang' && $e->settle_proof && ! $e->order_id)
<a href="{{ upload_url($e->settle_proof) }}" target="_blank" style="align-self: flex-start"><img src="{{ upload_url($e->settle_proof) }}" alt="Bukti pelunasan" class="ph" style="width: 110px; height: 110px; border-radius: 12px"></a>
@endif
@if ($e->result === 'menang' && ! $e->order_id)
<form method="POST" action="{{ route('admin.war.settled', $e) }}" style="margin: 0">@csrf<button type="submit" data-confirm="Konfirmasi pelunasan {{ rp($e->remaining()) }} dan lanjut ke Checkout?" style="{{ $btn }}">Tandai lunas, lanjut ke pesanan</button></form>
@elseif ($e->result === 'kalah' && ! $e->refunded_at)
<form method="POST" action="{{ route('admin.war.refunded', $e) }}" style="margin: 0">@csrf<button type="submit" data-confirm="Tandai refund {{ rp($e->refund_amount) }} sudah ditransfer?" style="{{ $btn }}">Tandai DP sudah dikembalikan</button></form>
@elseif ($e->order_id)
<a href="{{ route('admin.orders', ['id' => $e->order_id]) }}" style="{{ $btn }}">Buka pesanan {{ $e->order?->code }}</a>
@endif
<a href="{{ route('admin.war', ['entry' => $e->id]) }}#entri-{{ $e->id }}" style="font-size: 13.5px; font-weight: 700; text-align: center">Buka halaman Jasa war</a>
</section>
</div>
</aside>
@endif

{{-- ======================= DRAWER: PESANAN TITIP ======================= --}}
@if (! $isWar && $o)
@php $nm = $o->user?->displayName(); @endphp
<aside class="ord-drawer" aria-labelledby="dr-title" style="position: absolute; top: 0; right: 0; bottom: 0; width: 440px; max-width: 100%; background: #FFFFFF; box-shadow: -20px 0 50px rgba(21,22,28,0.16); display: flex; flex-direction: column; z-index: 5">
@include('admin._drawer-head', ['nm' => $nm, 'sub' => $o->code.' · '.($o->type === 'war' ? 'War' : 'Titip').' · ', 'ng' => $neg, 'user' => $o->user, 'close' => route('admin.orders', $keep())])
<div style="flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 20px">
@include('admin._drawer-route', ['groups' => \App\Support\Alur::POS, 'cur' => $oPos, 'posisi' => \App\Support\Alur::POS[$oPos]['t'].($oPos === 2 ? ' '.$neg['nama'] : ''), 'posSub' => \App\Support\Alur::POS[$oPos]['sub'].' · sejak '.yg_lalu($o->updated_at)])

<section aria-labelledby="aksi" style="border: 2px solid {{ $allDone ? '#1D7A4C' : '#1D4E9E' }}; border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 12px">
<span style="{{ $kicker }}; color: {{ $allDone ? '#16653F' : '#1D4E9E' }}">{{ $allDone ? 'SELESAI' : 'LANGKAH BERIKUTNYA' }}</span>

@if ($o->stage === 0)
<form method="POST" action="{{ route('admin.orders.offer', $o) }}" data-offer data-kurs="{{ $harga['kj'] }}" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">Kirim penawaran</h3>
<p style="{{ $desc }}">Cek stok lalu kirim total harga barang dengan kurs hari ini ke email penitip.</p>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Harga di toko ({{ $sym }})<input type="number" name="store_price" step="any" min="0" required value="{{ old('store_price', $suggest ?: '') }}" placeholder="0" style="{{ $in }}; font-weight: 800"></label>
<label style="{{ $lab }}">Ongkir dalam negeri<input type="number" name="domestic_shipping" step="any" min="0" value="{{ old('domestic_shipping') }}" placeholder="0" style="{{ $in }}; font-weight: 700"></label>
</div>
<dl style="{{ $dl }}">
<div style="display: flex; justify-content: space-between"><dt style="color: #4E515C">Kurs jastip</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}1 = {{ rpk($harga['kj']) }}</dd></div>
<div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 800">Total tahap 1</dt><dd style="margin: 0; font-size: 22px; font-weight: 800" data-o="offerTotal">{{ rp($suggest * $harga['kj']) }}</dd></div>
</dl>
<p style="margin: 0; font-size: 12.5px; color: #4E515C">Total = (harga + ongkir dalam negeri) × kurs jual. Kurs dikunci {{ config('jastip.kurs_dikunci_jam') }} jam.</p>
<button type="submit" style="{{ $btn }}">Kirim penawaran via email</button>
</form>
@endif

@if ($o->stage === 1)
<h3 id="aksi" style="{{ $h3 }}">{{ $o->payment_proof ? 'Konfirmasi pembayaran' : 'Menunggu pembayaran' }}</h3>
<p style="{{ $desc }}">{{ $o->payment_proof ? 'Penitip sudah transfer. Cek stok & harga di toko (link di daftar Barang) dan bukti transfernya, lalu tandai lunas.' : 'Penawaran sudah terkirim. Konfirmasi setelah bukti transfer tahap 1 masuk.' }}</p>
<dl style="{{ $dl }}">
<div style="display: flex; justify-content: space-between"><dt style="color: #4E515C">Harga di toko</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}{{ angka($o->foreignTotal()) }}</dd></div>
<div style="display: flex; justify-content: space-between"><dt style="color: #4E515C">Kurs jastip</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}1 = {{ rpk($o->kurs_jual) }}</dd></div>
<div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 800">Total tahap 1</dt><dd style="margin: 0; font-size: 22px; font-weight: 800">{{ rp($o->tahap1()) }}</dd></div>
</dl>
<div style="display: flex; gap: 14px; align-items: center">
@if ($o->payment_proof)
<a href="{{ upload_url($o->payment_proof) }}" target="_blank" style="flex: none"><img src="{{ upload_url($o->payment_proof) }}" alt="Bukti transfer penitip" class="ph" style="width: 88px; height: 88px; border-radius: 12px"></a>
@endif
<span style="font-size: 13px; line-height: 1.5; color: #4E515C">{{ $o->payment_submitted_at ? 'Bukti dikirim '.tgl($o->payment_submitted_at, 'j M H:i').($o->pay_method === 'qris' ? ' · QRIS' : '').($o->payer_name ? ' · a.n. '.$o->payer_name : '').($o->payer_bank ? ' ('.$o->payer_bank.')' : '') : ($o->payment_rejected_at ? 'Bukti ditolak '.tgl($o->payment_rejected_at, 'j M H:i').': '.$o->payment_reject_reason.'. Menunggu upload ulang.' : 'Belum ada bukti transfer') }}@if($o->offer_expires_at)<br>Kurs dikunci s/d {{ tgl($o->offer_expires_at, 'j M H:i') }}@endif</span>
</div>
<div style="display: flex; flex-direction: column; gap: 8px">
<form method="POST" action="{{ route('admin.orders.paid', $o) }}" style="margin: 0">@csrf<button type="submit" style="{{ $btn }}" @unless($o->payment_proof) data-confirm="Belum ada bukti transfer. Tetap tandai lunas?" data-confirm-tone="danger" data-confirm-ok="Tetap tandai lunas" @endunless>Tandai lunas</button></form>
@if ($o->payment_proof)
<details class="rej" style="border: 1.5px solid #CFCDC6; border-radius: 12px">
<summary style="min-height: 44px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; cursor: pointer; list-style: none">Tolak, minta ulang</summary>
<form method="POST" action="{{ route('admin.orders.reject', $o) }}" style="margin: 0; padding: 0 12px 12px; display: flex; flex-direction: column; gap: 8px">@csrf
<label style="{{ $lab }}">Alasan (dikirim ke penitip)<input type="text" name="reason" maxlength="180" list="alasan-tolak" placeholder="Mis. nominal di bukti Rp 786.000, seharusnya {{ rp($o->tahap1()) }}" style="{{ $in }}"></label>
<datalist id="alasan-tolak"><option value="Nominal di bukti belum sesuai tagihan"><option value="Transfer belum masuk ke rekening kami"><option value="Bukti tidak terbaca, upload yang lebih jelas"></datalist>
<button type="submit" style="{{ $btnLine }}">Kirim ke penitip</button>
</form>
</details>
@endif
</div>
@endif

@if ($o->stage === 2)
<form method="POST" action="{{ route('admin.orders.checkout', $o) }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">Checkout di toko</h3>
<p style="{{ $desc }}">Beli di toko resminya, isi nomor order, lalu kirim bukti checkout ke penitip.</p>
<label style="{{ $lab }}">No. order dari toko<input type="text" name="store_order_no" required placeholder="Nomor order" value="{{ old('store_order_no') }}" style="{{ $in }}"></label>
<label style="{{ $lab }}">Resi kurir lokal ke warehouse (opsional)<input type="text" name="local_resi" maxlength="80" placeholder="Bisa diisi nanti" value="{{ old('local_resi') }}" style="{{ $in }}"></label>
<label class="drop" style="min-height: 64px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 8px; box-sizing: border-box; text-align: center">
<input type="file" name="proof" data-file accept="image/*"><span data-file-name data-empty="Upload screenshot bukti checkout">Upload screenshot bukti checkout</span>
</label>
<button type="submit" style="{{ $btn }}">Kirim bukti checkout</button>
</form>
@endif

@if ($o->stage === 3)
<form method="POST" action="{{ route('admin.orders.photos', $o) }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">{{ $o->photoList() ? 'Setujui foto unboxing' : 'Upload foto unboxing' }}</h3>
<p style="{{ $desc }}">Foto barang, photocard/POB, dan timbangan dari warehouse. Penitip baru melihatnya setelah kamu setujui.</p>
@if ($o->wh_submitted_at)
<div style="background: {{ in_array($o->wh_condition, ['penyok', 'rusak'], true) ? '#FBEDEA' : '#F7F6F2' }}; border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 4px; font-size: 13.5px">
<strong style="font-size: 12px; letter-spacing: 0.04em; color: #5B3A9E">DIKIRIM WAREHOUSE · {{ tgl($o->wh_submitted_at, 'j M H:i') }}</strong>
<span>Kondisi: <strong style="color: {{ in_array($o->wh_condition, ['penyok', 'rusak'], true) ? '#B02A19' : '#16653F' }}">{{ \App\Models\Order::WH_CONDITION[$o->wh_condition] ?? 'Baik' }}</strong></span>
@if ($o->wh_note)<span style="color: #4E515C">Catatan: {{ $o->wh_note }}</span>@endif
</div>
@elseif (\App\Support\Alur::hasWarehouse($o->country))
<p style="margin: 0; font-size: 13px; color: #7A4B00; background: #FFF6E5; border-radius: 10px; padding: 10px 12px">{{ $o->wh_arrived_at ? 'Sudah tiba di gudang '.tgl($o->wh_arrived_at, 'j M').', warehouse sedang foto & timbang.' : 'Menunggu tiba di gudang '.$neg['nama'].'.' }} Kamu tetap bisa upload sendiri.</p>
@endif
<div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px">
@foreach ($o->photoList() as $ph)
<a href="{{ upload_url($ph) }}" target="_blank"><img src="{{ upload_url($ph) }}" alt="Foto {{ $loop->iteration }}" class="ph" style="width: 100%; aspect-ratio: 1; border-radius: 10px"></a>
@endforeach
<label class="drop" style="aspect-ratio: 1; border: 1.5px dashed #CFCDC6; border-radius: 10px; background: #FAFAF8; color: #4E515C; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; text-align: center; padding: 6px; box-sizing: border-box">
<input type="file" name="photos[]" data-file accept="image/*" multiple><span data-file-name data-empty="+ Foto">+ Foto</span>
</label>
</div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<label style="{{ $lab }}; font-size: 12.5px">Photocard / POB<input type="text" name="photocard_note" value="{{ old('photocard_note', $o->photocard_note) }}" placeholder="Member, POB" style="{{ $in }}; height: 42px; font-size: 14px"></label>
<label style="{{ $lab }}; font-size: 12.5px">Berat (kg)<input type="number" name="weight" step="0.01" min="0" value="{{ old('weight', $o->weight) }}" placeholder="0,0" style="{{ $in }}; height: 42px; font-size: 14px; font-weight: 700"></label>
<label style="{{ $lab }}; font-size: 12.5px">Kondisi album<input type="text" name="condition_note" value="{{ old('condition_note', $o->condition_note) }}" placeholder="Sudut aman" style="{{ $in }}; height: 42px; font-size: 14px"></label>
<label style="{{ $lab }}; font-size: 12.5px">Packing<select name="pack_type" style="{{ $in }}; height: 42px; font-size: 14px; font-weight: 700"><option value="">Pilih…</option>@foreach ($packs as $pk)<option value="{{ $pk['id'] }}" @selected(old('pack_type', $o->pack_type) === $pk['id'])>{{ $pk['label'] }} · {{ rp($pk['jual']) }}</option>@endforeach</select></label>
</div>
<div style="display: flex; flex-direction: column; gap: 8px">
<button type="submit" name="action" value="setujui" style="{{ $btn }}">Setujui &amp; tampilkan ke penitip</button>
@if ($o->photoList())
<button type="submit" name="action" value="ulang" formnovalidate data-confirm="Hapus semua foto dan minta warehouse kirim ulang?" data-confirm-ok="Ya, minta ulang" style="{{ $btnLine }}">Minta foto ulang</button>
@else
<button type="submit" name="action" value="simpan" style="{{ $btnLine }}">Simpan dulu</button>
@endif
</div>
</form>
@endif

@if ($o->stage === 4)
<form method="POST" action="{{ route('admin.orders.schedule', $o) }}" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">Masukkan ke batch kirim</h3>
<p style="{{ $desc }}">Barang siap dikirim ({{ angka($o->weight) }} kg, ditagih {{ $o->billedKg() }} kg · {{ \App\Support\Harga::PACK_LABEL[$o->pack_type] ?? 'packing' }}). Pilih batch pengiriman berikutnya.</p>
@if ($batches->isEmpty())
<p style="margin: 0; font-size: 14px; color: #7A4B00; background: #FFF6E5; padding: 12px 14px; border-radius: 12px">Belum ada batch {{ $neg['nama'] }} yang sedang mengumpulkan. Buat dulu di Jadwal kirim.</p>
<a href="{{ route('admin.batches') }}" style="{{ $btnLine }}">Buka jadwal kirim</a>
@else
<fieldset style="border: 0; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px">
<legend class="sr-only">Batch</legend>
@foreach ($batches as $b)
<label class="opt opt-neutral" style="justify-content: flex-start; gap: 10px; min-height: 46px; padding: 0 14px; border-radius: 12px; font-size: 14px"><input type="radio" name="batch_id" value="{{ $b->id }}" @checked($loop->first)><span aria-hidden="true" style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid currentColor; box-sizing: border-box; flex: none"></span>{{ $b->label() }} · {{ strtolower($b->routeLabel()) }} · {{ angka($b->totalWeight(), 1) }}/{{ angka($b->capacity_kg, 1) }} kg</label>
@endforeach
</fieldset>
<button type="submit" style="{{ $btn }}">Masukkan ke batch &amp; kabari penitip</button>
@endif
</form>
@endif

@if ($o->stage === 5)
<form method="POST" action="{{ route('admin.orders.arrived', $o) }}" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">Dalam perjalanan</h3>
<p style="{{ $desc }}">Barang ikut {{ $o->batch?->label() ?? 'batch' }} ({{ $o->batch ? $o->batch->statusPill()['label'] : '—' }}) menuju Indonesia. Tandai saat sudah diterima di tempatmu, atau sekaligus satu batch di Jadwal kirim.</p>
<button type="submit" style="{{ $btn }}">Tandai sudah tiba</button>
@if ($o->batch)<a href="{{ route('admin.batches', ['batch' => $o->batch_id]) }}" style="{{ $btnLine }}">Buka batch di Jadwal kirim</a>@endif
</form>
@endif

@if ($o->stage === 6)
<form method="POST" action="{{ route('admin.orders.shopee', $o) }}" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">Upload ke Shopee</h3>
<p style="{{ $desc }}">Buat produk khusus berisi ongkir + packing, lalu kirim linknya ke penitip.</p>
<dl style="{{ $dl }}">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Ongkir · {{ $o->billedKg() }} kg × {{ rp($o->tarif_jual) }}</dt><dd style="margin: 0; font-weight: 700">{{ rp($o->ongkir()) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Packing · {{ \App\Support\Harga::PACK_LABEL[$o->pack_type] ?? '—' }}</dt><dd style="margin: 0; font-weight: 700">{{ rp($o->pack_jual) }}</dd></div>
<div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 800">Harga produk di Shopee</dt><dd style="margin: 0; font-size: 20px; font-weight: 800">{{ rp($o->tahap2()) }}</dd></div>
</dl>
<label style="{{ $lab }}">Link produk Shopee<input type="url" name="shopee_link" required placeholder="https://shopee.co.id/…" value="{{ old('shopee_link') }}" style="{{ $in }}"></label>
<button type="submit" style="{{ $btn }}">Kirim link ke penitip</button>
</form>
@endif

@if ($o->stage === 7)
<form method="POST" action="{{ route('admin.orders.complete', $o) }}" style="display: flex; flex-direction: column; gap: 12px; margin: 0">
@csrf
<h3 id="aksi" style="{{ $h3 }}">{{ $o->shopee_reported_at ? 'Cek checkout di Shopee' : 'Menunggu checkout di Shopee' }}</h3>
<dl style="{{ $dl }}">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Username Shopee penitip</dt><dd style="margin: 0; font-weight: 800">{{ $o->user->shopee_username ? '@'.$o->user->shopee_username : '—' }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Harga produk di Shopee</dt><dd style="margin: 0; font-weight: 800">{{ rp($o->tahap2()) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Link dikirim</dt><dd style="margin: 0; font-weight: 700">{{ tgl($o->shopee_sent_at, 'j M H:i') }} · <a href="{{ $o->shopee_link }}" target="_blank" rel="noopener" style="color: #1D4E9E">buka</a></dd></div>
</dl>
@if ($o->shopee_reported_at)
<div style="border: 2px solid #C2311F; border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 4px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.04em; color: #B02A19">PENITIP LAPOR SUDAH CHECKOUT · {{ tgl($o->shopee_reported_at, 'j M H:i') }}</span>
<span style="font-size: 20px; font-weight: 800; letter-spacing: 0.04em; overflow-wrap: anywhere">No. {{ $o->shopee_order_no }}</span>
<span style="font-size: 13px; color: #4E515C">Cari nomor ini di Seller Centre › Pesanan Saya. Pastikan pembelinya {{ $o->user->shopee_username ? '@'.$o->user->shopee_username : 'penitip ini' }} dan totalnya {{ rp($o->tahap2()) }}, lalu tandai selesai.</span>
</div>
<input type="hidden" name="shopee_order_no" value="{{ $o->shopee_order_no }}">
<button type="submit" data-confirm="Pesanan Shopee {{ $o->shopee_order_no }} sudah dicek dan sesuai?" style="{{ $btn }}">Sudah sesuai, tandai selesai</button>
@else
<p style="margin: 0; font-size: 13.5px; line-height: 1.5; color: {{ $o->shopeeWaitDays() >= 3 ? '#7A4B00' : '#4E515C' }}; background: {{ $o->shopeeWaitDays() >= 3 ? '#FFF6E5' : 'transparent' }}; border-radius: 10px; padding: {{ $o->shopeeWaitDays() >= 3 ? '10px 12px' : '0' }}">Penitip belum lapor checkout{{ $o->shopeeWaitDays() ? ' ('.$o->shopeeWaitDays().' hari sejak link dikirim)' : '' }}. Kalau di Seller Centre sudah ada pesanan dari username di atas, isi nomornya lalu tandai selesai.</p>
<label style="{{ $lab }}">No. pesanan Shopee (dari Seller Centre)<input type="text" name="shopee_order_no" maxlength="40" placeholder="Opsional" style="{{ $in }}"></label>
<button type="submit" data-confirm="Tandai selesai tanpa laporan penitip?" style="{{ $btn }}">Tandai selesai</button>
@if ($o->user->whatsapp)<a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $o->user->whatsapp)) }}" target="_blank" rel="noopener" style="{{ $btnLine }}">Ingatkan via WA</a>@endif
@endif
</form>
@endif

@if ($allDone)
<h3 id="aksi" style="{{ $h3 }}">Pesanan selesai</h3>
<p style="{{ $desc }}">Penitip sudah checkout di Shopee{{ $o->shopee_order_no ? ' (No. '.$o->shopee_order_no.')' : '' }} dan barang dikirim ({{ tgl($o->completed_at) }}). Tahap 1 {{ rp($o->tahap1()) }} · tahap 2 {{ rp($o->tahap2()) }}.</p>
@endif
</section>

@if ($o->cancelled_at)
<p style="margin: 0; font-size: 13.5px; line-height: 1.5; background: #F0EEE9; border-radius: 12px; padding: 12px 14px">Dibatalkan {{ tgl($o->cancelled_at, 'j M H:i') }} · {{ $o->cancel_reason }}</p>
@endif
@if ($o->refund_amount > 0)
<a href="{{ route('admin.payments', ['tab' => 'refund']) }}" style="font-size: 13.5px; line-height: 1.5; background: {{ $o->refunded_at ? '#E6F4EC' : '#FFF6E5' }}; color: {{ $o->refunded_at ? '#16653F' : '#7A4B00' }}; border-radius: 12px; padding: 12px 14px; text-decoration: none; font-weight: 600">Refund {{ rp($o->refund_amount) }} · {{ $o->refund_reason }} · {{ $o->refunded_at ? 'sudah ditransfer '.tgl($o->refunded_at, 'j M') : 'belum dikirim, buka Pembayaran →' }}</a>
@elseif ($o->paid_at && ! $o->cancelled_at)
<details style="border: 1px solid #E6E4DF; border-radius: 12px; padding: 0 14px">
<summary style="min-height: 44px; display: flex; align-items: center; justify-content: space-between; font-size: 14px; font-weight: 700; cursor: pointer; list-style: none">Catat refund untuk pesanan ini</summary>
<form method="POST" action="{{ route('admin.orders.refund', $o) }}" style="margin: 0; padding-bottom: 14px; display: flex; flex-direction: column; gap: 10px">@csrf
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<label style="{{ $lab }}">Jumlah (Rp)<input type="number" name="amount" min="1000" step="100" required placeholder="0" style="{{ $in }}; font-weight: 700"></label>
<label style="{{ $lab }}">Alasan<input type="text" name="reason" required maxlength="120" list="alasan-refund" placeholder="Pilih / tulis" style="{{ $in }}"></label>
</div>
<datalist id="alasan-refund"><option value="Kelebihan bayar"><option value="Stok habis di toko"><option value="Dibatalkan oleh toko"><option value="Harga di toko turun"></datalist>
<label style="display: flex; gap: 10px; align-items: center; font-size: 13.5px"><input type="checkbox" name="cancel" value="1" style="width: 18px; height: 18px; margin: 0">Batalkan pesanan (stok habis / dibatalkan toko)</label>
<button type="submit" data-confirm="Catat refund dan kabari penitip?" style="{{ $btnLine }}">Catat refund &amp; kabari penitip</button>
</form>
</details>
@endif

<section aria-labelledby="brg" style="display: flex; flex-direction: column; gap: 10px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px"><h3 id="brg" style="margin: 0; font-size: 15px; font-weight: 800">Barang ({{ $o->items->count() }})</h3><span style="font-size: 13px; color: #4E515C">Total {{ $sym }}{{ angka($totalAsing) }}</span></div>
@foreach ($o->items as $it)
<div style="display: flex; justify-content: space-between; gap: 14px; padding: 12px 14px; border: 1px solid #E6E4DF; border-radius: 12px">
<div style="display: flex; flex-direction: column; gap: 3px; min-width: 0">
<span style="font-size: 14px; font-weight: 700">{{ $it->name }}</span>
<span style="font-size: 12.5px; color: #4E515C">{{ collect([$it->variant, '× '.$it->qty, $it->web_price ? $sym.angka($it->web_price).' / pcs' : 'harga belum diisi'])->filter()->implode(' · ') }}</span>
@if ($it->url)<a href="{{ $it->url }}" target="_blank" rel="noopener" style="font-size: 12.5px; color: #1D4E9E; word-break: break-all">{{ \Illuminate\Support\Str::limit($it->url, 60) }}</a>@endif
@if ($it->note)<span style="font-size: 12.5px; color: #7A4B00; background: #FFF6E5; border-radius: 6px; padding: 4px 8px; margin-top: 2px">Catatan: {{ $it->note }}</span>@endif
@if ($it->screenshot)<a href="{{ upload_url($it->screenshot) }}" target="_blank" style="align-self: flex-start; margin-top: 4px"><img src="{{ upload_url($it->screenshot) }}" alt="Screenshot {{ $it->name }}" class="ph" style="width: 56px; height: 56px; border-radius: 8px"></a>@endif
</div>
<span style="font-size: 14px; font-weight: 700; white-space: nowrap">{{ $it->web_price ? $sym.angka($it->web_price * $it->qty) : '—' }}</span>
</div>
@endforeach
@if ($o->warEntry)
<p style="margin: 0; font-size: 12.5px; color: #4E515C">Dari war antrian #{{ $o->warEntry->queue_no }}{{ $o->warEntry->variant_backup ? ' · cadangan '.$o->warEntry->variant_backup : '' }}.</p>
@endif
</section>

<section aria-labelledby="riw" style="display: flex; flex-direction: column; gap: 10px">
<h3 id="riw" style="margin: 0; font-size: 15px; font-weight: 800">Riwayat</h3>
<ol style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px">
@forelse ($events as $ev)
<li style="display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px"><span style="display: flex; gap: 8px; align-items: baseline; font-weight: {{ $loop->first ? 700 : 500 }}; color: {{ $loop->first ? '#15161C' : '#4E515C' }}"><span aria-hidden="true" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $loop->first ? '#C2311F' : '#1D7A4C' }}; flex: none; transform: translateY(-1px)"></span><span>{!! $ev->html() !!}</span></span><span style="color: #4E515C; white-space: nowrap">{{ lalu($ev->created_at) }}</span></li>
@empty
<li style="font-size: 13.5px; color: #4E515C">Dibuat {{ tgl($o->created_at, 'j M H:i') }}</li>
@endforelse
</ol>
</section>

@if ($o->stage > 0)
<div style="border-top: 1px solid #E6E4DF; padding-top: 12px; display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap">
<form method="POST" action="{{ route('admin.orders.back', $o) }}" style="margin: 0">@csrf<button type="submit" data-confirm="Kembalikan pesanan ke tahap sebelumnya?" style="min-height: 40px; padding: 0; border: 0; background: transparent; font-family: inherit; font-weight: 700; font-size: 13.5px; color: #4E515C; cursor: pointer">← Kembali ke tahap sebelumnya</button></form>
<a href="{{ route('track', ['no' => $o->code]) }}" target="_blank" style="min-height: 40px; display: flex; align-items: center; font-weight: 700; font-size: 13.5px; color: #4E515C">Lihat sebagai penitip ↗</a>
</div>
@endif
</div>
</aside>
@endif
@endsection
