@extends('layouts.admin')
@section('title', 'Admin · Ringkasan')

@section('main')
{{-- ===================== Versi HP: daftar tugas ===================== --}}
@php
    $me = auth()->user();
    $tPes = $tasks->where('group', 'pesanan')->count();
    $tBay = $tasks->where('group', 'bayar')->count();
    $tWar = $tasks->where('group', 'war')->count();
@endphp
<div class="mob" style="margin: -20px -16px 0">
<header style="background: #15161C; color: #FFFFFF; padding: 18px 16px 20px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; flex-direction: column; gap: 2px"><span style="font-size: 13px; color: #C9CAD1">Halo, {{ $me->displayName() }}</span><h1 style="margin: 0; font-size: 22px; font-weight: 800">{{ $tasks->count() }} tugas hari ini</h1></div>
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px">
<div style="background: #2A2C35; border-radius: 12px; padding: 10px; display: flex; flex-direction: column; gap: 2px"><span style="font-size: 18px; font-weight: 800">{{ $tPes }}</span><span style="font-size: 11.5px; color: #C9CAD1">Pesanan</span></div>
<div style="background: #2A2C35; border-radius: 12px; padding: 10px; display: flex; flex-direction: column; gap: 2px"><span style="font-size: 18px; font-weight: 800">{{ $tBay }}</span><span style="font-size: 11.5px; color: #C9CAD1">Cek transfer</span></div>
<div style="background: #2A2C35; border-radius: 12px; padding: 10px; display: flex; flex-direction: column; gap: 2px"><span style="font-size: 18px; font-weight: 800">{{ $tWar }}</span><span style="font-size: 11.5px; color: #C9CAD1">Jasa war</span></div>
</div>
</header>
<div style="padding: 14px 16px 8px; display: flex; flex-direction: column; gap: 10px">
@forelse ($tasks as $i => $t)
<button type="button" data-sheet-open="tugas-{{ $i }}" style="text-align: left; border: 0; border-left: 4px solid {{ $t['dot'] }}; background: #FFFFFF; border-radius: 14px; padding: 12px 14px; font-family: inherit; color: #15161C; cursor: pointer; display: flex; gap: 12px; align-items: center">
<span style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px">
<span style="font-size: 14.5px; font-weight: 800">{{ $t['task'] }}</span>
<span style="font-size: 12.5px; color: #4E515C; white-space: nowrap; overflow: hidden; text-overflow: ellipsis">{{ $t['id'] }} · {{ $t['nama'] }}</span>
<span style="align-self: flex-start; margin-top: 2px; font-size: 11.5px; font-weight: 700; color: {{ $t['pill']['fg'] }}; background: {{ $t['pill']['bg'] }}; padding: 3px 9px; border-radius: 999px">{{ $t['pos'] }}</span>
</span>
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#767884" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"></path></svg>
</button>
<dialog id="tugas-{{ $i }}" data-sheet class="sheet" aria-labelledby="tugas-{{ $i }}-t">
<div style="display: flex; flex-direction: column; gap: 14px">
<span aria-hidden="true" style="align-self: center; width: 40px; height: 4px; border-radius: 2px; background: #D9D6CF"></span>
<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px">
<div style="display: flex; flex-direction: column; gap: 4px"><span style="align-self: flex-start; font-size: 11.5px; font-weight: 700; color: {{ $t['pill']['fg'] }}; background: {{ $t['pill']['bg'] }}; padding: 3px 9px; border-radius: 999px">{{ $t['pos'] }}</span><h2 id="tugas-{{ $i }}-t" style="margin: 0; font-size: 20px; font-weight: 800">{{ $t['task'] }}</h2><span style="font-size: 13.5px; color: #4E515C">{{ $t['id'] }} · {{ $t['nama'] }}</span></div>
<button type="button" data-sheet-close aria-label="Tutup" style="width: 40px; height: 40px; border: 1px solid #E6E4DF; border-radius: 10px; background: #FFFFFF; font-family: inherit; font-size: 20px; color: #15161C; flex: none">×</button>
</div>
<p style="margin: 0; font-size: 14px; line-height: 1.55; color: #2C2E36; background: #F7F6F2; border-radius: 12px; padding: 12px 14px">{{ $t['desc'] }}</p>
@if ($t['action'])
<form method="POST" action="{{ $t['action'] }}" style="margin: 0; display: flex">@csrf<input type="hidden" name="kembali" value="hp"><button type="submit" style="flex: 1; min-height: 54px; border: 0; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px">{{ $t['btn'] }}</button></form>
@else
<a href="{{ $t['href'] }}" style="min-height: 54px; border-radius: 12px; background: #C2311F; color: #FFFFFF; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none">{{ $t['btn'] }}</a>
@endif
<a href="{{ $t['href'] }}" style="text-align: center; font-size: 14px; font-weight: 700; min-height: 40px; display: flex; align-items: center; justify-content: center">Buka detail lengkap</a>
</div>
</dialog>
@empty
<div style="background: #FFFFFF; border-radius: 16px; padding: 32px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px"><span style="width: 56px; height: 56px; border-radius: 50%; background: #E6F4EC; display: flex; align-items: center; justify-content: center"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1D7A4C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path></svg></span><strong style="font-size: 17px">Semua tugas beres</strong><span style="font-size: 13.5px; color: #4E515C">Tugas baru akan muncul di sini.</span></div>
@endforelse
</div>
</div>

{{-- ===================== Versi desktop ===================== --}}
<div class="dsk"><div style="display: flex; flex-direction: column; gap: 22px">
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Ringkasan</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">{{ tgl(now(), 'l, j F Y') }} · @if ($tasks->count())ada <strong style="color: #B02A19">{{ $tasks->count() }} tugas</strong> yang menunggu kamu.@else semua tugas beres.@endif</p>
</div>
<a href="{{ route('admin.orders') }}" style="display: inline-flex; align-items: center; min-height: 46px; padding: 0 20px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 15px">Buka semua pesanan</a>
</div>

<section aria-labelledby="alur" style="display: flex; flex-direction: column; gap: 12px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap"><h2 id="alur" style="margin: 0; font-size: 17px; font-weight: 800">Posisi semua pesanan</h2><span style="font-size: 13px; color: #4E515C">Klik untuk membuka di halaman Pesanan</span></div>
<div style="display: flex; align-items: stretch; gap: 8px; flex-wrap: wrap">
@foreach ($flow as $f)
@php
    $note = $f['last'] ? 'Bulan ini' : ($f['aksi'] ? $f['aksi'].' perlu tindakan' : 'Tidak ada tugas');
    $noteFg = $f['last'] ? '#16653F' : ($f['aksi'] ? '#B02A19' : '#4E515C');
    $dot = $f['last'] ? '#1D7A4C' : ($f['aksi'] ? '#C2311F' : '#CFCDC6');
@endphp
<div style="flex: 1 1 150px; display: flex; align-items: center; gap: 8px">
<a href="{{ $f['href'] }}" class="rk-flow" style="flex: 1; background: #FFFFFF; border: 1.5px solid {{ $f['aksi'] ? '#F3D3CD' : '#E6E4DF' }}; border-radius: 16px; padding: 14px 16px; text-decoration: none; display: flex; flex-direction: column; gap: 4px">
<span style="font-size: 13px; font-weight: 700; color: #4E515C">{{ $f['t'] }}</span>
<span style="font-size: 30px; font-weight: 800; line-height: 1.05; color: #15161C">{{ $f['n'] }}</span>
<span style="font-size: 12px; font-weight: 700; color: {{ $noteFg }}; display: flex; align-items: center; gap: 6px"><span aria-hidden="true" style="width: 7px; height: 7px; border-radius: 50%; background: {{ $dot }}"></span>{{ $note }}</span>
</a>
@unless ($f['last'])<span aria-hidden="true" style="color: #A9A69F; font-size: 18px; font-weight: 700">›</span>@endunless
</div>
@endforeach
</div>
</section>

<div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-start">
<section aria-labelledby="todo" style="flex: 3 1 520px; min-width: 0; background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="padding: 18px 20px; border-bottom: 1px solid #F0EEE9; display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap"><h2 id="todo" style="margin: 0; font-size: 17px; font-weight: 800">Tugas prioritas</h2><span style="font-size: 13px; color: #4E515C">Urut dari yang paling lama menunggu</span></div>
<ul style="list-style: none; margin: 0; padding: 0">
@forelse ($tasks->take(15) as $t)
<li class="rk-task" style="display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1.3fr) minmax(110px, 0.8fr) auto; gap: 14px; align-items: center; padding: 12px 20px; border-bottom: 1px solid #F0EEE9">
<span style="display: flex; gap: 10px; align-items: center; font-size: 14px; font-weight: 800"><span aria-hidden="true" style="width: 8px; height: 8px; border-radius: 50%; background: #C2311F; flex: none"></span>{{ $t['task'] }}</span>
<span style="font-size: 13.5px; color: #4E515C; white-space: nowrap; overflow: hidden; text-overflow: ellipsis"><strong style="color: #15161C">{{ $t['id'] }}</strong> · {{ $t['nama'] }}</span>
<span style="justify-self: start; font-size: 12px; font-weight: 700; color: {{ $t['pill']['fg'] }}; background: {{ $t['pill']['bg'] }}; padding: 4px 10px; border-radius: 999px; white-space: nowrap">{{ $t['pos'] }}</span>
<a href="{{ $t['href'] }}" style="font-size: 13.5px; font-weight: 700; white-space: nowrap; min-height: 36px; display: inline-flex; align-items: center">Kerjakan →</a>
</li>
@empty
<li style="padding: 28px 20px; text-align: center; font-size: 14px; color: #4E515C">Tidak ada tugas. Semua pesanan sedang menunggu penitip atau pengiriman.</li>
@endforelse
@if ($tasks->count() > 15)
<li style="padding: 12px 20px; font-size: 13.5px"><a href="{{ route('admin.orders') }}" style="font-weight: 700">+ {{ $tasks->count() - 15 }} tugas lain di halaman Pesanan</a></li>
@endif
</ul>
</section>

<div style="flex: 2 1 320px; min-width: 0; display: flex; flex-direction: column; gap: 14px">
<a href="{{ route('admin.war', $nextWar ? ['drop' => $nextWar->id] : []) }}" style="background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 18px 20px; text-decoration: none; display: flex; flex-direction: column; gap: 8px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #FF8A78">WAR BERIKUTNYA</span>
@if ($nextWar)
<span style="font-size: 17px; font-weight: 800">{{ $nextWar->name }}</span>
<span style="font-size: 13.5px; color: #C9CAD1">{{ tgl($nextWar->release_at, 'j M') }} · {{ $nextWar->release_at->format('H:i') }} WIB · {{ $nextWar->entries_count }} dari {{ $nextWar->quota }} slot terisi</span>
@else
<span style="font-size: 17px; font-weight: 800">Belum ada drop</span>
<span style="font-size: 13.5px; color: #C9CAD1">Buat drop baru di menu Jasa war</span>
@endif
</a>
<a href="{{ route('admin.batches', $nextBatch ? ['batch' => $nextBatch->id] : []) }}" style="background: #FFFFFF; border-radius: 16px; padding: 18px 20px; text-decoration: none; display: flex; flex-direction: column; gap: 10px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #4E515C">KIRIM BERIKUTNYA</span>
@if ($nextBatch)
@php $bw = $nextBatch->totalWeight(); $bk = max(1, $nextBatch->capacity_kg); @endphp
<span style="font-size: 17px; font-weight: 800">{{ $nextBatch->label() }} · {{ negara($nextBatch->country)['nama'] }} · {{ strtolower($nextBatch->routeLabel()) }}</span>
<span style="display: flex; justify-content: space-between; font-size: 13px; color: #4E515C"><span>{{ angka($bw, 1) }} kg dari {{ angka($bk, 1) }} kg</span><span>{{ $nextBatch->orders->count() }} paket</span></span>
<span aria-hidden="true" style="height: 8px; border-radius: 4px; background: #F0EEE9; display: flex"><span style="width: {{ min(100, round($bw / $bk * 100, 1)) }}%; background: {{ $bw > $bk ? '#C2311F' : '#1D4E9E' }}; border-radius: 4px"></span></span>
@else
<span style="font-size: 17px; font-weight: 800">Belum ada batch</span>
<span style="font-size: 13px; color: #4E515C">Buat di menu Jadwal kirim</span>
@endif
</a>
<a href="{{ route('admin.pricing') }}" style="background: #FFFFFF; border-radius: 16px; padding: 18px 20px; text-decoration: none; display: flex; flex-direction: column; gap: 10px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #4E515C">KURS HARI INI</span>
<span style="display: flex; justify-content: space-between; gap: 8px; font-size: 14px"><span style="font-weight: 700">¥1</span><span>modal {{ rpk($jp['km']) }} · <strong>jual {{ rpk($jp['kj']) }}</strong></span></span>
<span style="display: flex; justify-content: space-between; gap: 8px; font-size: 14px"><span style="font-weight: 700">₩1</span><span>modal {{ rpk($kr['km']) }} · <strong>jual {{ rpk($kr['kj']) }}</strong></span></span>
</a>
<a href="{{ route('admin.finance') }}" style="background: #FFFFFF; border-radius: 16px; padding: 18px 20px; text-decoration: none; display: flex; flex-direction: column; gap: 6px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #4E515C">PROFIT BULAN INI</span>
<span style="font-size: 24px; font-weight: 800; color: #16653F">{{ rp($keu['bersih']) }}</span>
<span style="font-size: 13px; color: #4E515C">Belum cair {{ rp($keu['belum']) }} · lihat Keuangan →</span>
</a>
</div>
</div>

<section aria-labelledby="akt" style="background: #FFFFFF; border-radius: 16px; padding: 18px 20px; display: flex; flex-direction: column; gap: 4px">
<h2 id="akt" style="margin: 0 0 8px; font-size: 17px; font-weight: 800">Aktivitas terbaru</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; font-size: 14px">
@forelse ($activities as $a)
<li style="display: flex; gap: 14px; padding: 11px 0; {{ $loop->last ? '' : 'border-bottom: 1px solid #F0EEE9' }}"><span style="width: 70px; color: #4E515C; flex: none">{{ lalu($a->created_at) }}</span><span>{!! $a->html() !!}</span></li>
@empty
<li style="padding: 11px 0; color: #4E515C">Belum ada aktivitas.</li>
@endforelse
</ul>
</section>
</div></div>
@endsection
