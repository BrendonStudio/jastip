@php
    $p1 = pill($t1Pill[1]);
    $p2 = pill($t2Pill[1]);
    $r = $m ? '16px' : '20px';
    $pad = $m ? '16px' : '24px';
    $hs = $m ? '16px' : '17px';
    $sym = $o->neg()['sym'];
@endphp
<section id="bayar" aria-label="Pembayaran" style="background: #FFFFFF; border-radius: {{ $r }}; padding: {{ $pad }}; display: flex; flex-direction: column; gap: {{ $m ? '12px' : '14px' }}">
<h2 style="margin: 0; font-size: {{ $hs }}; font-weight: 800">Pembayaran</h2>
<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 14px">
<div style="display: flex; flex-direction: column; gap: 2px"><span style="font-weight: 700">Tahap 1 · Harga barang</span><span style="color: #4E515C">{{ $o->stage >= 1 ? 'Dibayar di web · '.rp($o->tahap1()) : 'Menunggu penawaran admin' }}</span></div>
<span style="font-size: 12px; font-weight: 700; color: {{ $p1['fg'] }}; background: {{ $p1['bg'] }}; padding: 5px 10px; border-radius: 999px; white-space: nowrap">{{ $t1Pill[0] }}</span>
</div>

@if ($o->stage >= 1 && $o->kurs_jual)
<dl style="margin: 0; background: #F7F6F2; border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 6px; font-size: 13.5px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Harga di toko</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}{{ angka($o->store_price) }}</dd></div>
@if ($o->domestic_shipping > 0)
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Ongkir dalam {{ $o->neg()['nama'] }}</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}{{ angka($o->domestic_shipping) }}</dd></div>
@endif
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Kurs jastip (dikunci)</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}1 = {{ rpk($o->kurs_jual) }}</dd></div>
@if ($o->war_fee > 0)
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Fee war</dt><dd style="margin: 0; font-weight: 700">{{ rp($o->war_fee) }}</dd></div>
@endif
<div style="display: flex; justify-content: space-between; gap: 8px; border-top: 1px dashed #CFCDC6; padding-top: 6px"><dt style="font-weight: 800">Total tahap 1</dt><dd style="margin: 0; font-weight: 800">{{ rp($o->tahap1()) }}</dd></div>
</dl>
@endif

@if ($o->stage === 1 && ! $o->payment_proof && ! $o->cancelled_at)
<div style="display: flex; flex-direction: column; gap: 10px; border: 2px solid {{ $o->offerExpired() ? '#CFCDC6' : '#C2311F' }}; border-radius: 14px; padding: 14px">
<p style="margin: 0; font-size: 14px; line-height: 1.55; color: #2C2E36">@if($o->offerExpired())Penawaran sudah hangus karena kurs hanya dikunci {{ config('jastip.kurs_dikunci_jam') }} jam. Minta penawaran baru dengan kurs hari ini.@elseif($o->payment_rejected_at)<strong style="color: #8E2213">Bukti transfer belum cocok:</strong> {{ $o->payment_reject_reason }}. Upload ulang bukti yang benar.@else Bayar <strong>{{ rp($o->tahap1()) }}</strong> lewat transfer bank{{ \App\Http\Controllers\PayController::qris() ? ' atau QRIS' : '' }}.@if($o->offer_expires_at) Kurs dikunci sampai <strong>{{ tgl($o->offer_expires_at, 'j M H:i') }} WIB</strong>.@endif @endif</p>
<a href="{{ $o->payUrl() }}" style="display: flex; align-items: center; justify-content: center; min-height: 50px; border-radius: 12px; background: {{ $o->offerExpired() ? '#15161C' : '#C2311F' }}; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 15px">{{ $o->offerExpired() ? 'Minta penawaran baru' : ($o->payment_rejected_at ? 'Upload ulang bukti' : 'Lihat & bayar penawaran') }}</a>
</div>
@elseif ($o->stage === 1 && $o->payment_proof)
<p style="margin: 0; font-size: 13.5px; color: #1A3F7F; background: #EAF0FA; border-radius: 12px; padding: 12px">Bukti transfer terkirim {{ tgl($o->payment_submitted_at, 'j M H:i') }}. Admin akan konfirmasi dan kabari lewat email. <a href="{{ upload_url($o->payment_proof) }}" target="_blank" style="color: #1A3F7F; font-weight: 700">Lihat bukti</a></p>
@endif

<div style="height: 1px; background: #E6E4DF"></div>
<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 14px">
<div style="display: flex; flex-direction: column; gap: 2px"><span style="font-weight: 700">Tahap 2 · Ongkir + packing</span><span style="color: #4E515C">
@if ($o->stage >= 6 && $o->weight)
{{ $o->billedKg() }} kg × {{ rp($o->tarif_jual) }} + {{ \App\Support\Harga::PACK_LABEL[$o->pack_type] ?? 'packing' }} = {{ rp($o->tahap2()) }}
@else
Lewat Shopee, setelah tiba{{ $m ? ' di Indonesia' : '' }}
@endif
</span></div>
<span style="font-size: 12px; font-weight: 700; color: {{ $p2['fg'] }}; background: {{ $p2['bg'] }}; padding: 5px 10px; border-radius: 999px; white-space: nowrap">{{ $t2Pill[0] }}</span>
</div>
@if ($o->stage === 7 && $o->shopee_reported_at)
<div style="background: #EAF0FA; border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 4px; font-size: 13.5px; color: #1A3F7F">
<strong style="font-size: 14px">Checkout Shopee sudah dilaporkan</strong>
<span>No. pesanan Shopee <strong>{{ $o->shopee_order_no }}</strong> · {{ tgl($o->shopee_reported_at, 'j M H:i') }}. Admin sedang cek, lalu barang dikirim ke alamatmu.</span>
</div>
@elseif ($o->stage === 7 && $o->shopee_link)
<a href="{{ $o->shopee_link }}" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; min-height: {{ $m ? '52px' : '50px' }}; border-radius: 12px; background: #C2311F; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 15px">1 · Bayar ongkir &amp; packing di Shopee</a>
<form method="POST" action="{{ route('order.shopee', $o) }}" style="display: flex; flex-direction: column; gap: 8px; border: 1.5px solid #CFCDC6; border-radius: 12px; padding: 12px">
@csrf
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 700; color: #15161C">2 · Sudah checkout? Masukkan nomor pesanan Shopee
<input type="text" name="shopee_order_no" required minlength="6" maxlength="40" autocomplete="off" placeholder="Mis. 2410279ABCDEFG" value="{{ old('shopee_order_no') }}" style="height: 46px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 15px; font-weight: 700; color: #15161C; background: #FFFFFF; letter-spacing: 0.02em">
</label>
<span style="font-size: 12px; font-weight: 500; color: #4E515C">Nomor pesanan ada di aplikasi Shopee › Saya › Pesanan Saya › pilih pesanan. Pastikan checkout pakai akun {{ '@'.$o->user->shopee_username }}.</span>
<button type="submit" style="min-height: 46px; border: 0; border-radius: 10px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14.5px">Saya sudah checkout</button>
</form>
@elseif ($o->stage < 7)
<div style="display: flex; align-items: center; justify-content: center; min-height: {{ $m ? '52px' : '50px' }}; border-radius: 12px; background: #F0EEE9; color: #4E515C; font-weight: 700; font-size: 14px; text-align: center; padding: 0 12px">Link Shopee muncul di sini saat barang tiba</div>
@endif
</section>
