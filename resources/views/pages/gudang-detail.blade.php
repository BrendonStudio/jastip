@extends('layouts.site')
@section('title', $o->code.' · foto & timbang')
@section('body_class', 'bg-soft')

@php
    $photos = array_pad(array_slice((array) ($o->photos ?? []), 0, 4), 4, null);
    $cnt = count(array_filter($photos));
    $kond = old('wh_condition', $o->wh_condition ?? 'baik');
    $in = 'height: 48px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 12px; font-family: inherit; color: #15161C; min-width: 0; width: 100%; box-sizing: border-box; background: #FFFFFF';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
@endphp

@section('content')
<form method="POST" action="{{ route('warehouse.submit', $o) }}" enctype="multipart/form-data" data-wh-form style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; max-width: 560px; margin: 0 auto; min-height: 100vh; background: #F4F3EF; display: flex; flex-direction: column">
@csrf
<header style="background: #FFFFFF; padding: 14px 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E6E4DF">
<a href="{{ route('warehouse', ['tab' => 'foto']) }}" aria-label="Kembali ke daftar" style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #15161C; flex: none"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></a>
<div style="display: flex; flex-direction: column; min-width: 0"><h1 style="margin: 0; font-size: 18px; font-weight: 800">{{ $o->code }} · foto &amp; timbang</h1><span style="font-size: 12.5px; color: #4E515C; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $o->user?->displayName() }} · {{ $o->title() }}</span></div>
</header>
@include('partials.flash')
<main style="flex: 1; padding: 16px; display: flex; flex-direction: column; gap: 14px">
<section aria-labelledby="ft" style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<div style="display: flex; justify-content: space-between; align-items: baseline"><h2 id="ft" style="margin: 0; font-size: 16px; font-weight: 800">Foto wajib</h2><span style="font-size: 13px; font-weight: 700; color: {{ $cnt === 4 ? '#16653F' : '#B02A19' }}" data-wh-count>{{ $cnt }} dari 4</span></div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
@foreach (\App\Http\Controllers\WarehouseController::SLOTS as $i => [$l, $sub])
@php $up = (bool) $photos[$i]; @endphp
<label class="drop wh-slot {{ $up ? 'is-up' : '' }}" style="aspect-ratio: 1; border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 8px; box-sizing: border-box; text-align: center; overflow: hidden; position: relative">
<input type="file" name="photo[{{ $i }}]" accept="image/*" capture="environment" data-wh-slot>
@if ($up)<img src="{{ upload_url($photos[$i]) }}" alt="" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.35">@endif
<span class="wh-ic" style="position: relative; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800">{{ $up ? '✓' : '+' }}</span>
<span style="position: relative; font-size: 13px; font-weight: 800">{{ $l }}</span>
<span class="wh-sub" style="position: relative; font-size: 11.5px">{{ $up ? 'Ketuk untuk ganti' : $sub }}</span>
</label>
@endforeach
</div>
</section>
<section style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 12px">
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Berat (kg)<input type="number" name="weight" step="0.01" min="0.01" inputmode="decimal" required placeholder="0,0" value="{{ old('weight', $o->weight) }}" style="{{ $in }}; font-size: 17px; font-weight: 800"></label>
<label style="{{ $lab }}">Photocard / POB<input type="text" name="photocard_note" maxlength="190" placeholder="Member, POB" value="{{ old('photocard_note', $o->photocard_note) }}" style="{{ $in }}; font-size: 15px"></label>
</div>
<div role="radiogroup" aria-label="Kondisi barang" style="display: flex; flex-direction: column; gap: 6px">
<span style="font-size: 13px; font-weight: 600; color: #4E515C">Kondisi</span>
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px">
@foreach (\App\Models\Order::WH_CONDITION as $k => $l)
<label class="opt {{ $k === 'baik' ? 'opt-ok' : 'opt-warn' }}" style="min-height: 44px; border-radius: 10px; font-size: 13px"><input type="radio" name="wh_condition" value="{{ $k }}" @checked($kond === $k)>{{ $l }}</label>
@endforeach
</div>
</div>
<label style="{{ $lab }}">Catatan untuk admin<textarea name="wh_note" rows="2" maxlength="500" placeholder="Mis. segel plastik sobek sedikit" style="border: 1px solid #CFCDC6; border-radius: 12px; padding: 10px 12px; font-family: inherit; font-size: 14px; color: #15161C; resize: none">{{ old('wh_note', $o->wh_note) }}</textarea></label>
</section>
<button type="submit" data-wh-submit style="min-height: 54px; border: 0; border-radius: 12px; background: {{ $cnt === 4 ? '#15161C' : '#E6E4DF' }}; color: {{ $cnt === 4 ? '#FFFFFF' : '#4E515C' }}; font-family: inherit; font-weight: 700; font-size: 16px">{{ $cnt === 4 ? 'Kirim ke admin' : 'Lengkapi '.(4 - $cnt).' foto lagi' }}</button>
<p style="margin: 0; font-size: 12.5px; color: #4E515C; text-align: center">Foto yang sudah kamu pilih tersimpan walau belum lengkap.</p>
</main>
</form>
@endsection

@push('scripts')
<script>
(function () {
  var f = document.querySelector('[data-wh-form]'); if (!f) return;
  var slots = f.querySelectorAll('[data-wh-slot]'), out = f.querySelector('[data-wh-count]'), btn = f.querySelector('[data-wh-submit]');
  function upd() {
    var n = 0;
    slots.forEach(function (i) { var l = i.closest('.wh-slot'); if (i.files.length) l.classList.add('is-up'); if (l.classList.contains('is-up')) n++; var ic = l.querySelector('.wh-ic'); if (ic) ic.textContent = l.classList.contains('is-up') ? '✓' : '+'; });
    out.textContent = n + ' dari 4'; out.style.color = n === 4 ? '#16653F' : '#B02A19';
    btn.textContent = n === 4 ? 'Kirim ke admin' : 'Lengkapi ' + (4 - n) + ' foto lagi';
    btn.style.background = n === 4 ? '#15161C' : '#E6E4DF'; btn.style.color = n === 4 ? '#FFFFFF' : '#4E515C';
  }
  slots.forEach(function (i) { i.addEventListener('change', upd); });
})();
</script>
@endpush
