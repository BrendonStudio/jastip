{{-- Header versi HP: tombol kembali + judul --}}
<header style="background: #FFFFFF; padding: 14px 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #E6E4DF">
<a href="{{ $back ?? route('home') }}" aria-label="Kembali" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; text-decoration: none"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></a>
<h1 style="margin: 0; font-size: 18px; font-weight: 800; flex: 1">{{ $title }}</h1>
{{ $slot ?? '' }}
</header>
