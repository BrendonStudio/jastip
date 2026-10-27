<section data-item style="background: #FFFFFF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px">
<div style="display: flex; justify-content: space-between; align-items: center">
<h2 style="margin: 0; font-size: 17px; font-weight: 800">Barang <span data-o="no">1</span></h2>
<button type="button" data-remove-item hidden style="min-height: 40px; padding: 0 12px; border: 0; background: transparent; border-radius: 10px; font-family: inherit; font-weight: 700; font-size: 14px; color: #B02A19; cursor: pointer">Hapus</button>
</div>
<label style="{{ $lab }}">Link produk
<input type="url" data-name="url" placeholder="https://… (tempel link toko)" value="{{ $it['url'] ?? '' }}" style="height: 50px; {{ $inp }}">
</label>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px">
<label style="{{ $lab }}">Nama produk
<input type="text" data-name="name" required placeholder="Contoh: nama album, mini album" value="{{ $it['name'] ?? '' }}" style="height: 50px; {{ $inp }}; min-width: 0">
</label>
<label style="{{ $lab }}">Versi / member
<input type="text" data-name="variant" placeholder="Ver. A, Digipack" value="{{ $it['variant'] ?? '' }}" style="height: 50px; {{ $inp }}; min-width: 0">
</label>
<label style="{{ $lab }}">Harga di toko (per barang)
<input type="number" data-name="web_price" data-sym-ph required inputmode="decimal" min="0" step="any" placeholder="¥" value="{{ $it['web_price'] ?? '' }}" style="height: 50px; {{ $inp }}; font-weight: 700; min-width: 0">
</label>
<label style="{{ $lab }}">Jumlah
<input type="number" data-name="qty" min="1" max="99" value="{{ $it['qty'] ?? 1 }}" style="height: 50px; {{ $inp }}; font-weight: 700; min-width: 0">
</label>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 12px">
<label class="drop" style="flex: 1 1 220px; min-height: 96px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 8px; box-sizing: border-box; text-align: center">
<input type="file" data-name="screenshot" data-file accept="image/*">
{!! $imgIcon !!}
<span style="font-size: 14px; font-weight: 700" data-file-name data-empty="Upload screenshot produk">Upload screenshot produk</span>
<span style="font-size: 12px">Opsional, bantu kami pilih varian yang tepat</span>
</label>
<label style="flex: 2 1 280px; {{ $lab }}">Catatan untuk admin
<textarea rows="3" data-name="note" placeholder="Mis. kalau ver. A habis, ganti ver. B · bias: nama member" style="border: 1px solid #CFCDC6; border-radius: 12px; padding: 12px 14px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF; resize: none">{{ $it['note'] ?? '' }}</textarea>
</label>
</div>
</section>
