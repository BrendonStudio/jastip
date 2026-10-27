@php
    $icons = [
        'bag' => '<path d="M6 8h12l-1 12H7Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path>',
        'search' => '<circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path>',
        'clock' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>',
        'card' => '<rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path>',
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7Z"></path>',
        'box' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4Z"></path><path d="M3 7l9 4 9-4M12 11v10"></path>',
    ];
@endphp
<section style="background: {{ $bg ?? '#FFFFFF' }}; border-radius: 16px; padding: {{ $pad ?? '32px 20px' }}; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px">
<span aria-hidden="true" style="width: 60px; height: 60px; border-radius: 50%; background: #F0EEE9; color: #4E515C; display: flex; align-items: center; justify-content: center"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$icon ?? 'bag'] !!}</svg></span>
<h2 style="margin: 4px 0 0; font-size: 18px; font-weight: 800">{{ $title }}</h2>
@if (! empty($text))<p style="margin: 0; max-width: 380px; font-size: 14.5px; line-height: 1.55; color: #4E515C">{{ $text }}</p>@endif
@if (! empty($cta))<a href="{{ $href }}" style="margin-top: 6px; display: inline-flex; align-items: center; min-height: 46px; padding: 0 20px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 14.5px">{{ $cta }}</a>@endif
{{ $slot ?? '' }}
</section>
