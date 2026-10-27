<section aria-label="Posisi barang" style="background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 18px 20px; display: flex; flex-direction: column; gap: 16px">
<div style="display: flex; flex-direction: column; gap: 4px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #FF8A78">POSISI SEKARANG</span>
<span style="font-size: 24px; font-weight: 800; letter-spacing: -0.01em">{{ $posisi }}</span>
<span style="font-size: 13px; color: #C9CAD1">{{ $posSub }}</span>
</div>
<ol aria-label="Rute barang" style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat({{ count($groups) }}, minmax(0, 1fr))">
@foreach ($groups as $i => $g)
@php $done = $i < $cur; $now = $i === $cur; $sz = $now ? '16px' : '11px'; @endphp
<li style="display: flex; flex-direction: column; gap: 8px" @if($now) aria-current="step" @endif>
<span style="display: flex; align-items: center; height: 16px"><span style="width: {{ $sz }}; height: {{ $sz }}; border-radius: 50%; box-sizing: border-box; border: 2px solid {{ $now ? '#FF8A78' : ($done ? '#FFFFFF' : '#4E515C') }}; background: {{ $now ? '#FF8A78' : ($done ? '#FFFFFF' : 'transparent') }}; flex: none"></span><span style="height: 2px; flex: 1; background: {{ $loop->last ? 'transparent' : ($done ? '#FFFFFF' : '#3A3C45') }}"></span></span>
<span style="font-size: 11px; line-height: 1.3; font-weight: {{ $now ? 800 : 600 }}; color: {{ $now ? '#FFFFFF' : ($done ? '#E4E5EA' : '#8C8E98') }}; padding-right: 4px">{{ $g['t'] }}</span>
</li>
@endforeach
</ol>
</section>
