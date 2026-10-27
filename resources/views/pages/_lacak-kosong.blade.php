<section style="background: #FFFFFF; border-radius: {{ $m ? '16px' : '20px' }}; padding: {{ $m ? '20px 16px' : '40px 24px' }}; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px">
<h2 style="margin: 0; font-size: {{ $m ? '18px' : '22px' }}; font-weight: 800">Belum ada pesanan</h2>
<p style="margin: 0; font-size: 15px; line-height: 1.55; color: #4E515C; max-width: 460px">Masukkan nomor pesanan di atas, atau mulai titip barang pertamamu.</p>
<div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center">
<a href="{{ route('order.create') }}" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 20px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700">Titip sekarang</a>
<a href="{{ route('war') }}" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 20px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700">Jadwal war</a>
</div>
</section>
