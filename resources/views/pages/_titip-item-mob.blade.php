<section data-item style="background: #FFFFFF; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 14px">
<div style="display: flex; justify-content: space-between; align-items: center">
<h2 style="margin: 0; font-size: 16px; font-weight: 800">Barang <span data-o="no">1</span></h2>
<button type="button" data-remove-item hidden aria-label="Hapus barang" style="width: 44px; height: 44px; border: 0; background: transparent; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4E515C"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M6 6l1 14h10l1-14"></path></svg></button>
</div>
<label style="{{ $lab }}">Link produk
<input type="url" data-name="url" placeholder="https://… (tempel link toko)" value="{{ $it['url'] ?? '' }}" style="height: 48px; {{ $inp }}">
</label>
<label style="{{ $lab }}">Nama produk
<input type="text" data-name="name" required placeholder="Contoh: nama album, mini album" value="{{ $it['name'] ?? '' }}" style="height: 48px; {{ $inp }}">
</label>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px">
<label style="{{ $lab }}">Versi / member
<input type="text" data-name="variant" placeholder="Ver. A, Digipack" value="{{ $it['variant'] ?? '' }}" style="height: 48px; {{ $inp }}; padding: 0 12px; min-width: 0">
</label>
<label style="{{ $lab }}">Harga di toko (per barang)
<input type="number" data-name="web_price" data-sym-ph required inputmode="decimal" min="0" step="any" placeholder="¥" value="{{ $it['web_price'] ?? '' }}" style="height: 48px; {{ $inp }}; padding: 0 12px; font-weight: 700; min-width: 0">
</label>
</div>
<div data-stepper style="display: flex; align-items: center; justify-content: space-between">
<span style="font-size: 13px; font-weight: 600; color: #4E515C">Jumlah</span>
<div style="display: flex; align-items: center; gap: 4px; border: 1px solid #CFCDC6; border-radius: 12px">
<button type="button" data-step="-1" aria-label="Kurangi jumlah" style="width: 44px; height: 44px; border: 0; background: transparent; font-family: inherit; font-size: 20px; font-weight: 700; color: #15161C">−</button>
<span data-step-show style="min-width: 28px; text-align: center; font-size: 16px; font-weight: 800">{{ $it['qty'] ?? 1 }}</span>
<input type="hidden" data-name="qty" min="1" max="99" value="{{ $it['qty'] ?? 1 }}">
<button type="button" data-step="1" aria-label="Tambah jumlah" style="width: 44px; height: 44px; border: 0; background: transparent; font-family: inherit; font-size: 20px; font-weight: 700; color: #15161C">+</button>
</div>
</div>
<label class="drop" style="min-height: 84px; border: 1.5px dashed #CFCDC6; border-radius: 12px; background: #FAFAF8; color: #4E515C; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 8px; box-sizing: border-box; text-align: center">
<input type="file" data-name="screenshot" data-file accept="image/*">
{!! $imgIcon !!}
<span style="font-size: 14px; font-weight: 700" data-file-name data-empty="Upload screenshot produk">Upload screenshot produk</span>
<span style="font-size: 12px">Opsional, bantu kami pilih varian yang tepat</span>
</label>
<label style="{{ $lab }}">Catatan untuk admin
<textarea rows="2" data-name="note" placeholder="Mis. kalau ver. A habis, ganti ver. B · bias: nama member" style="border: 1px solid #CFCDC6; border-radius: 12px; padding: 12px 14px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF; resize: none">{{ $it['note'] ?? '' }}</textarea>
</label>
</section>
