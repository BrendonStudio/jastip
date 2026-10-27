<section id="{{ $m ? 'riwayat-m' : 'riwayat' }}" aria-label="Riwayat pembayaran dan refund" style="background: #FFFFFF; border-radius: {{ $m ? '16px' : '20px' }}; padding: {{ $m ? '16px' : '24px' }}; display: flex; flex-direction: column; gap: 10px">
<h2 style="margin: 0; font-size: {{ $m ? '16px' : '20px' }}; font-weight: 800">Riwayat pembayaran &amp; refund</h2>
@forelse ($riwayat as $r)
<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #F0EEE9; font-size: 14px">
<div style="display: flex; flex-direction: column; gap: 2px; min-width: 0"><span style="font-weight: 700">{{ $r['label'] }}</span><span style="font-size: 12.5px; color: #4E515C">{{ tgl($r['tgl']) }} · {{ $r['sub'] }}</span></div>
<strong style="white-space: nowrap; color: {{ $r['in'] ? '#16653F' : '#15161C' }}">{{ $r['in'] ? '+' : '' }}{{ rp($r['amt']) }}</strong>
</div>
@empty
<p style="margin: 0; font-size: 14px; color: #4E515C">Belum ada pembayaran.</p>
@endforelse
</section>
