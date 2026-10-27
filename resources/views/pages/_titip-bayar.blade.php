{{-- Pembayaran tahap 1 langsung saat kirim request: harga barang × kurs jastip --}}
<div data-bayar style="display: flex; flex-direction: column; gap: 12px">
<dl style="margin: 0; background: #F7F6F2; border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; font-size: 14px">
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Total harga di toko</dt><dd style="margin: 0; font-weight: 700" data-o="bayarAsing">¥0</dd></div>
<div style="display: flex; justify-content: space-between; gap: 8px"><dt style="color: #4E515C">Kurs jastip</dt><dd style="margin: 0; font-weight: 700" data-o="bayarKurs">—</dd></div>
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; border-top: 1px dashed #CFCDC6; padding-top: 8px"><dt style="font-weight: 800">Bayar sekarang</dt><dd style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.01em" data-o="bayarTotal">Rp 0</dd></div>
</dl>
<p style="margin: 0; font-size: 13px; line-height: 1.55; color: #2C2E36">Transfer ke <strong>{{ config('jastip.rekening') }}</strong>, lalu upload buktinya. Kurs dikunci saat request dikirim.</p>
<label class="drop" style="min-height: 72px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 8px; box-sizing: border-box; text-align: center">
<input type="file" name="proof" data-file accept="image/*" required>
<span style="font-size: 14px; font-weight: 700" data-file-name data-empty="Upload bukti transfer">Upload bukti transfer</span>
<span style="font-size: 12px">Foto atau screenshot, maks 5 MB</span>
</label>
<p style="margin: 0; font-size: 12.5px; line-height: 1.5; color: #4E515C">Kalau barang habis atau harga di toko berbeda, admin menghubungi kamu dulu sebelum checkout. Ongkir &amp; packing dibayar nanti lewat Shopee setelah barang ditimbang.</p>
</div>
