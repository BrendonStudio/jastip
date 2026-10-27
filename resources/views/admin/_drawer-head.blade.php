<div style="padding: 20px 24px; border-bottom: 1px solid #F0EEE9; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px">
<div style="display: flex; gap: 12px; align-items: center; min-width: 0">
<span aria-hidden="true" style="width: 48px; height: 48px; border-radius: 50%; background: {{ avatar_bg($nm) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex: none">{{ inisial($nm) }}</span>
<div style="display: flex; flex-direction: column; gap: 3px; min-width: 0">
<h2 id="dr-title" style="margin: 0; font-size: 20px; font-weight: 800">{{ $nm }}</h2>
<span style="font-size: 13px; color: #4E515C">{{ $sub }}<span style="color: {{ $ng['fg'] }}; font-weight: 700">{{ $ng['nama'] }}</span></span>
<span style="font-size: 12.5px; color: #4E515C; overflow-wrap: anywhere">{{ $user?->email }} · Shopee {{ $user?->shopee_username ? '@'.$user->shopee_username : '—' }}@if($user?->whatsapp) · WA {{ $user->whatsapp }}@endif</span>
</div>
</div>
<a href="{{ $close }}" aria-label="Tutup detail" style="width: 40px; height: 40px; border: 1px solid #E6E4DF; border-radius: 10px; background: #FFFFFF; font-size: 20px; color: #15161C; display: flex; align-items: center; justify-content: center; text-decoration: none; flex: none; box-sizing: border-box">×</a>
</div>
