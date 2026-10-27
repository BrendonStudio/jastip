@extends('layouts.site')
@section('title', 'Lacak pesanan')
@section('body_class', 'bg-soft')

@php
    $o = $order;
    if ($o) {
        $neg = $o->neg();
        [$bannerText, $bannerColor] = $o->banner();
        $bp = pill($bannerColor);
        $steps = $o->trackSteps();
        $cur = $o->trackCurrent();
        $photos = ($o->stage >= \App\Models\Order::WAREHOUSE) ? $o->photoList() : [];
        $item = $o->mainItem();
        $sub = collect([$o->totalQty().' barang', $item?->variant])->filter()->implode(' · ');
        $t1Pill = $o->paid_at ? ['Lunas', 'green'] : ($o->payment_proof ? ['Dicek admin', 'blue'] : ($o->stage === 1 ? ['Belum bayar', 'red'] : ['Nanti', 'gray']));
        $t2Pill = $o->stage >= 8 ? ['Lunas', 'green'] : ($o->stage === 7 ? ($o->shopee_reported_at ? ['Dicek admin', 'blue'] : ['Bayar sekarang', 'red']) : ['Nanti', 'gray']);
    }
    $stepStyle = function ($i) use (&$cur, &$steps) {
        $done = $i < $cur; $now = $i === $cur;
        return [
            'mark' => $done ? '✓' : '',
            'ring' => $done ? '#1D7A4C' : ($now ? '#1D4E9E' : '#CFCDC6'),
            'fill' => $done ? '#1D7A4C' : ($now ? '#EAF0FA' : '#FFFFFF'),
            'line' => $i === count($steps) - 1 ? 'transparent' : ($done ? '#1D7A4C' : '#E6E4DF'),
            'weight' => $now ? '800' : '600',
            'color' => ($done || $now) ? '#15161C' : '#4E515C',
        ];
    };
    $phIcon = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><path d="m21 16-5-5-8 8"></path></svg>';
@endphp

@section('content')
{{-- ======================= DESKTOP ======================= --}}
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%">
@include('partials.header', ['active' => 'lacak'])
@include('partials.flash')

<div style="max-width: 1200px; margin: 0 auto; padding: 40px 24px 64px; display: flex; flex-direction: column; gap: 20px">
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<h1 style="margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.025em">Lacak pesanan</h1>
<form role="search" method="GET" action="{{ route('track') }}" style="display: flex; gap: 8px; flex: 0 1 420px">
<label style="flex: 1; display: flex"><span class="sr-only">Nomor pesanan</span>
<input type="search" name="no" value="{{ $no }}" placeholder="Nomor pesanan" style="flex: 1; min-width: 0; height: 48px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF">
</label>
<button type="submit" style="height: 48px; padding: 0 20px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px">Cek</button>
</form>
</div>

@if (! $o)
@include('pages._lacak-kosong', ['m' => false])
@else
<section aria-labelledby="ord" style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 20px">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap">
<div style="display: flex; gap: 16px; align-items: center; min-width: 0">
@if ($o->thumb())
<img src="{{ $o->thumb() }}" alt="" class="ph" style="width: 64px; height: 64px; border-radius: 12px; flex: none">
@else
<div role="img" aria-label="Foto produk" style="width: 64px; height: 64px; border-radius: 12px; background: #ECEAE4; flex: none"></div>
@endif
<div style="display: flex; flex-direction: column; gap: 4px; min-width: 0">
<div style="display: flex; gap: 8px; align-items: center"><h2 id="ord" style="margin: 0; font-size: 20px; font-weight: 800">#{{ $o->code }}</h2><span style="font-size: 12px; font-weight: 700; color: {{ $neg['fg'] }}; background: {{ $neg['bg'] }}; padding: 4px 10px; border-radius: 999px">{{ $neg['nama'] }}</span></div>
<span style="font-size: 14px; color: #4E515C">{{ $o->title() }} · {{ $sub }}</span>
</div>
</div>
<div style="background: {{ $bp['bg'] }}; border-radius: 12px; padding: 12px 16px; font-size: 14.5px; font-weight: 700; color: {{ $bp['fg'] }}">{{ $bannerText }}</div>
</div>
<ol aria-label="Status pesanan" style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(9, minmax(0, 1fr)); gap: 4px">
@foreach ($steps as $i => [$title, $s])
@php $st = $stepStyle($i); @endphp
<li style="display: flex; flex-direction: column; gap: 8px" @if($i === $cur) aria-current="step" @endif>
<div style="display: flex; align-items: center">
<span style="width: 26px; height: 26px; border-radius: 50%; box-sizing: border-box; border: 2px solid {{ $st['ring'] }}; background: {{ $st['fill'] }}; display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; font-weight: 800; flex: none">{{ $st['mark'] }}</span>
<span style="height: 2px; flex: 1; background: {{ $st['line'] }}"></span>
</div>
<span style="font-size: 13px; line-height: 1.3; font-weight: {{ $st['weight'] }}; color: {{ $st['color'] }}; padding-right: 6px">{{ $title }}</span>
</li>
@endforeach
</ol>
<p style="margin: 0; padding: 12px 16px; background: #F7F6F2; border-radius: 12px; font-size: 14px; color: #2C2E36"><strong>Sekarang:</strong> {{ $cur >= 9 ? 'Pesanan selesai. Terima kasih!' : $steps[$cur][1] }}</p>
@if ($o->items->count() > 1)
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; font-size: 14px">
@foreach ($o->items as $it)
<li style="display: flex; justify-content: space-between; gap: 12px; border-top: 1px solid #F0EEE9; padding-top: 6px"><span><strong>{{ $it->name }}</strong>@if($it->variant) · {{ $it->variant }}@endif</span><span style="color: #4E515C">× {{ $it->qty }}</span></li>
@endforeach
</ul>
@endif
</section>

<div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start">
<section aria-labelledby="foto" style="flex: 3 1 480px; background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px"><h2 id="foto" style="margin: 0; font-size: 17px; font-weight: 800">Foto unboxing dari warehouse</h2>@if($photos)<span style="font-size: 14px; font-weight: 700">Berat timbang {{ angka($o->weight) }} kg</span>@endif</div>
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px">
@forelse ($photos as $ph)
<a href="{{ upload_url($ph) }}" target="_blank" class="ph-link"><img src="{{ upload_url($ph) }}" alt="Foto barang {{ $loop->iteration }}" class="ph" style="width: 100%; aspect-ratio: 4 / 3; border-radius: 12px"></a>
@empty
@foreach (['Foto barang 1', 'Foto barang 2', 'Foto timbangan'] as $lbl)
<div role="img" aria-label="{{ $lbl }}" style="aspect-ratio: 4 / 3; border-radius: 12px; background: #ECEAE4; display: flex; align-items: center; justify-content: center; color: #767884">{!! $phIcon !!}</div>
@endforeach
@endforelse
</div>
@if ($photos)
<p style="margin: 0; font-size: 14.5px; color: #2C2E36">Photocard / POB: <strong>{{ $o->photocard_note ?: '—' }}</strong> · Kondisi: <strong>{{ $o->condition_note ?: '—' }}</strong></p>
<span style="font-size: 13px; color: #4E515C">Dicek admin {{ tgl($o->warehouse_approved_at) }}</span>
@else
<p style="margin: 0; font-size: 14px; color: #4E515C">Foto unboxing, photocard &amp; berat timbang muncul di sini setelah barang tiba di warehouse dan dicek admin.</p>
@endif
</section>

<div style="flex: 2 1 340px; display: flex; flex-direction: column; gap: 20px">
@include('pages._lacak-bayar', ['m' => false])
<section aria-labelledby="bukti" style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 12px">
<div style="display: flex; flex-direction: column; gap: 3px">
<h2 id="bukti" style="margin: 0; font-size: 17px; font-weight: 800">Bukti checkout</h2>
<span style="font-size: 13.5px; color: #4E515C">No. order toko: {{ $o->store_order_no ?: 'belum ada' }}</span>
</div>
@if ($o->checkout_proof)
<a href="{{ upload_url($o->checkout_proof) }}" target="_blank" style="display: inline-flex; align-items: center; min-height: 44px; padding: 0 16px; border-radius: 10px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 14px; white-space: nowrap">Lihat bukti</a>
@endif
</section>
</div>
</div>
@endif

@if ($others->count() > 1)
<section aria-label="Pesanan lain" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; font-size: 14px">
<span style="color: #4E515C; font-weight: 600">Pesanan lain:</span>
@foreach ($others as $x)
<a href="{{ route('track', ['no' => $x->code]) }}" style="padding: 8px 12px; border-radius: 999px; background: {{ $o && $x->id === $o->id ? '#15161C' : '#FFFFFF' }}; color: {{ $o && $x->id === $o->id ? '#FFFFFF' : '#15161C' }}; text-decoration: none; font-weight: 700">#{{ $x->code }}</a>
@endforeach
</section>
@endif
</div>
</div>

{{-- ======================= HP ======================= --}}
<div class="mob">
<div class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF">
@include('partials.mob-header', ['title' => 'Lacak pesanan', 'back' => route('account')])
@include('partials.flash', ['style' => 'padding: 12px 16px 0'])
<main style="flex: 1; padding: 16px; display: flex; flex-direction: column; gap: 14px">
<form role="search" method="GET" action="{{ route('track') }}" style="display: flex; gap: 8px">
<label style="flex: 1; display: flex"><span class="sr-only">Nomor pesanan</span>
<input type="search" name="no" value="{{ $no }}" placeholder="Nomor pesanan" style="flex: 1; min-width: 0; height: 48px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF">
</label>
<button type="submit" style="height: 48px; padding: 0 18px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px">Cek</button>
</form>

@if (! $o)
@include('pages._lacak-kosong', ['m' => true])
@else
<section aria-labelledby="ord-m" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px">
<h2 id="ord-m" style="margin: 0; font-size: 16px; font-weight: 800">#{{ $o->code }}</h2>
<span style="font-size: 12px; font-weight: 700; color: {{ $neg['fg'] }}; background: {{ $neg['bg'] }}; padding: 5px 10px; border-radius: 999px">{{ $neg['nama'] }}</span>
</div>
<div style="display: flex; flex-direction: column; gap: 3px">
<span style="font-size: 15px; font-weight: 700">{{ $o->title() }}</span>
<span style="font-size: 13px; color: #4E515C">{{ $sub }}</span>
</div>
<div style="background: {{ $bp['bg'] }}; border-radius: 12px; padding: 12px; font-size: 14px; font-weight: 700; color: {{ $bp['fg'] }}">{{ $bannerText }}</div>
</section>

<section aria-labelledby="foto-m" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<div style="display: flex; justify-content: space-between; align-items: baseline"><h2 id="foto-m" style="margin: 0; font-size: 16px; font-weight: 800">Foto unboxing</h2>@if($photos)<span style="font-size: 13px; font-weight: 700">Berat {{ angka($o->weight) }} kg</span>@endif</div>
@if ($photos)
<p style="margin: 0; font-size: 13.5px; color: #2C2E36">Photocard / POB: <strong>{{ $o->photocard_note ?: '—' }}</strong> · Kondisi: <strong>{{ $o->condition_note ?: '—' }}</strong></p>
@endif
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px">
@forelse ($photos as $ph)
<a href="{{ upload_url($ph) }}" target="_blank" class="ph-link"><img src="{{ upload_url($ph) }}" alt="Foto barang {{ $loop->iteration }}" class="ph" style="width: 100%; aspect-ratio: 1; border-radius: 10px"></a>
@empty
@foreach (['Foto barang 1', 'Foto barang 2', 'Foto timbangan'] as $lbl)
<div role="img" aria-label="{{ $lbl }}" style="aspect-ratio: 1; border-radius: 10px; background: #ECEAE4; display: flex; align-items: center; justify-content: center; color: #767884">{!! $phIcon !!}</div>
@endforeach
@endforelse
</div>
@unless ($photos)
<p style="margin: 0; font-size: 13px; color: #4E515C">Muncul setelah barang tiba di warehouse dan dicek admin.</p>
@endunless
</section>

<section aria-labelledby="bukti-m" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px">
<div style="display: flex; flex-direction: column; gap: 3px">
<h2 id="bukti-m" style="margin: 0; font-size: 16px; font-weight: 800">Bukti checkout</h2>
<span style="font-size: 13px; color: #4E515C">No. order toko: {{ $o->store_order_no ?: 'belum ada' }}</span>
</div>
@if ($o->checkout_proof)
<a href="{{ upload_url($o->checkout_proof) }}" target="_blank" style="display: inline-flex; align-items: center; min-height: 44px; padding: 0 14px; border-radius: 10px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 14px; white-space: nowrap">Lihat</a>
@endif
</section>

@include('pages._lacak-bayar', ['m' => true])

<section aria-labelledby="status-m" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 4px">
<h2 id="status-m" style="margin: 0 0 10px; font-size: 16px; font-weight: 800">Status</h2>
<ol style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column">
@foreach ($steps as $i => [$title, $s])
@php $st = $stepStyle($i); @endphp
<li style="display: flex; gap: 12px" @if($i === $cur) aria-current="step" @endif>
<div style="display: flex; flex-direction: column; align-items: center; width: 22px; flex: none">
<span style="width: 22px; height: 22px; border-radius: 50%; box-sizing: border-box; border: 2px solid {{ $st['ring'] }}; background: {{ $st['fill'] }}; display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; font-weight: 800">{{ $st['mark'] }}</span>
<span style="width: 2px; flex: 1; min-height: 16px; background: {{ $st['line'] }}"></span>
</div>
<div style="padding-bottom: 14px; display: flex; flex-direction: column; gap: 2px">
<span style="font-size: 15px; font-weight: {{ $st['weight'] }}; color: {{ $st['color'] }}">{{ $title }}</span>
<span style="font-size: 13px; line-height: 1.45; color: #4E515C">{{ $s }}</span>
</div>
</li>
@endforeach
</ol>
</section>
@endif

@if ($others->count() > 1)
<section aria-label="Pesanan lain" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; font-size: 13.5px">
<span style="color: #4E515C; font-weight: 600">Pesanan lain:</span>
@foreach ($others as $x)
<a href="{{ route('track', ['no' => $x->code]) }}" style="padding: 8px 12px; border-radius: 999px; background: {{ $o && $x->id === $o->id ? '#15161C' : '#FFFFFF' }}; color: {{ $o && $x->id === $o->id ? '#FFFFFF' : '#15161C' }}; text-decoration: none; font-weight: 700">#{{ $x->code }}</a>
@endforeach
</section>
@endif
</main>
</div>
</div>
@endsection
