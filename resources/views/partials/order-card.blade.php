@php $st = pill($c['color']); @endphp
<article style="{{ $m ? 'background: #FFFFFF; border-radius: 16px; padding: 14px; gap: 10px' : 'border: 1px solid #E6E4DF; border-radius: 16px; padding: 16px; gap: 12px' }}; display: flex; flex-direction: column">
<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px">
<span style="font-size: 13px; font-weight: 800">#{{ $c['no'] }} <span style="font-weight: 700; color: {{ $c['neg']['fg'] }}">· {{ $c['jenis'] }} {{ $c['neg']['nama'] }}</span></span>
<span style="font-size: 12px; font-weight: 700; color: {{ $st['fg'] }}; background: {{ $st['bg'] }}; padding: 4px 9px; border-radius: 999px; white-space: nowrap">{{ $c['status'] }}</span>
</div>
<div style="display: flex; gap: 12px; align-items: center">
@if ($c['thumb'])
<img src="{{ $c['thumb'] }}" alt="" class="ph" style="width: {{ $m ? '52px' : '56px' }}; height: {{ $m ? '52px' : '56px' }}; border-radius: 10px; flex: none">
@else
<div role="img" aria-label="Foto produk" style="width: {{ $m ? '52px' : '56px' }}; height: {{ $m ? '52px' : '56px' }}; border-radius: 10px; background: #ECEAE4; flex: none"></div>
@endif
<div style="display: flex; flex-direction: column; gap: 2px; min-width: 0"><span style="font-size: {{ $m ? '14.5px' : '15px' }}; font-weight: 700; overflow-wrap: anywhere">{{ $c['title'] }}</span><span style="font-size: {{ $m ? '12.5px' : '13px' }}; color: #4E515C">{{ $c['sub'] }}</span></div>
</div>
<div style="display: flex; gap: 8px">
@if ($c['action'])
<a href="{{ $c['href'] }}" @if(str_starts_with($c['href'], 'http') && ! str_starts_with($c['href'], url('/'))) target="_blank" rel="noopener" @endif style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 10px; background: #C2311F; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 14px; text-align: center; padding: 0 8px">{{ $c['action'] }}</a>
@endif
<a href="{{ $c['track'] }}" style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 44px; border-radius: 10px; border: 1.5px solid #CFCDC6; text-decoration: none; font-weight: 700; font-size: 14px">Lacak</a>
</div>
</article>
