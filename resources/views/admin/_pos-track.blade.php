@php
    $isW = $war ?? false;
    $grp = $isW ? \App\Support\Alur::WAR : \App\Support\Alur::POS;
    $pc = ($isW ? \App\Support\Alur::WAR_COLOR : \App\Support\Alur::POS_COLOR)[$cur];
    $icons = $isW ? \App\Support\Alur::WAR_ICON : \App\Support\Alur::POS_ICON;
    $n = count($grp);
@endphp
<span role="img" aria-label="Langkah {{ $cur + 1 }} dari {{ $n }}" style="display: flex; align-items: center; width: {{ $isW ? 200 : 236 }}px; padding: 5px; box-sizing: content-box">
@foreach ($grp as $i => $g)
@php
    $done = $i < $cur;
    $now = $i === $cur;
    $on = $done || $now;
    $sz = $now ? 32 : 24;
@endphp
@unless ($loop->last)<span style="display: flex; align-items: center; flex: 1">@endunless
<span title="{{ $g['t'] }}" style="width: {{ $sz }}px; height: {{ $sz }}px; border-radius: 50%; box-sizing: border-box; background: {{ $on ? $pc['dot'] : '#FFFFFF' }}; border: 1.5px solid {{ $on ? $pc['dot'] : '#DDDAD3' }}; box-shadow: 0 0 0 {{ $now ? '5px' : '0' }} {{ $now ? $pc['bg'] : 'transparent' }}; color: {{ $on ? '#FFFFFF' : '#B5B2AA' }}; display: flex; align-items: center; justify-content: center; flex: none"><svg width="{{ $now ? 15 : 12 }}" height="{{ $now ? 15 : 12 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $loop->last && ! $isW ? '2.6' : '2.4' }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$i] !!}</svg></span>
@unless ($loop->last)<span style="flex: 1; margin: 0 3px; border-top: 2px {{ $done ? 'solid' : 'dashed' }} {{ $done ? $pc['dot'] : '#D9D6CF' }}"></span></span>@endunless
@endforeach
</span>
