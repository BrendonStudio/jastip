@extends('layouts.site')
@section('title', 'Jastip album & merch K-pop dari Korea dan Jepang')

@section('content')
@php
    $p0 = $packs['standar'];
    $neg0 = $harga['jp'];
    $h0 = 5000; $b0 = 1.2;
    $bb0 = kg_bulat($b0);
@endphp
<div style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #FFFFFF; width: 100%">

@include('partials.header', ['home' => true])
@include('partials.flash')

<section id="top" class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 56px 24px 72px; display: flex; flex-wrap: wrap; gap: 48px; align-items: center">
<div style="flex: 1 1 440px; min-width: 0; display: flex; flex-direction: column; gap: 22px">
<div style="display: flex; gap: 8px; flex-wrap: wrap">
<span style="font-size: 13px; font-weight: 700; color: #B02A19; background: #FBEDEA; padding: 6px 12px; border-radius: 999px">Jepang</span>
<span style="font-size: 13px; font-weight: 700; color: #1D4E9E; background: #EAF0FA; padding: 6px 12px; border-radius: 999px">Korea</span>
<span style="font-size: 13px; font-weight: 700; color: #15161C; background: #F4F3EF; padding: 6px 12px; border-radius: 999px">Album · Lightstick · Photocard · MD</span>
@if ($nextBatch)
<span style="font-size: 13px; font-weight: 600; color: #4E515C; background: #F4F3EF; padding: 6px 12px; border-radius: 999px">Kirim berikutnya: {{ tgl($nextBatch->ship_date) }}</span>
@endif
</div>
<h1 class="hero-title" style="margin: 0; font-size: 58px; line-height: 1.05; letter-spacing: -0.035em; font-weight: 800">Album &amp; merch idol dari Seoul dan Tokyo, sampai di rumahmu.</h1>
<p style="margin: 0; font-size: 18px; line-height: 1.6; color: #4E515C; max-width: 520px">Kirim link album atau merch incaranmu dan bayar harganya dengan kurs jastip hari ini. Kami checkout di toko resminya dan kirim buktinya. Ongkir &amp; packing dibayar belakangan lewat Shopee.</p>
<div style="display: flex; gap: 12px; flex-wrap: wrap">
<a href="{{ route('order.create') }}" style="display: inline-flex; align-items: center; min-height: 52px; padding: 0 22px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 16px">Titip sekarang</a>
<a href="#cara-kerja" style="display: inline-flex; align-items: center; min-height: 52px; padding: 0 22px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 16px">Lihat cara kerja</a>
</div>
<ul style="list-style: none; margin: 8px 0 0; padding: 0; display: flex; flex-direction: column; gap: 12px; font-size: 15px; color: #2C2E36">
<li style="display: flex; gap: 10px; align-items: center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D7A4C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex: none"><path d="M20 6 9 17l-5-5"></path></svg>Bukti checkout dari toko dikirim ke kamu</li>
<li style="display: flex; gap: 10px; align-items: center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D7A4C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex: none"><path d="M20 6 9 17l-5-5"></path></svg>Foto unboxing + photocard yang kamu dapat, sebelum dikirim</li>
<li style="display: flex; gap: 10px; align-items: center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D7A4C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex: none"><path d="M20 6 9 17l-5-5"></path></svg>Ongkir &amp; packing dibayar aman lewat Shopee</li>
</ul>
</div>

<section id="kalkulator" data-calc aria-labelledby="calc-title" style="flex: 1 1 380px; max-width: 480px; min-width: 0; background: #FFFFFF; border: 1px solid #E6E4DF; border-radius: 20px; padding: 24px; box-shadow: 0 16px 48px rgba(21,22,28,0.08); display: flex; flex-direction: column; gap: 18px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap">
<h2 id="calc-title" style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.01em">Kalkulator estimasi</h2>
<span style="font-size: 13px; font-weight: 600; color: #4E515C">Kurs hari ini: <span data-o="kursLabel">¥1 = {{ rpk($neg0['kj']) }}</span></span>
</div>
<div role="radiogroup" aria-label="Negara asal barang" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<label class="opt opt-jp" style="min-height: 48px; border-radius: 12px; font-size: 15px"><input type="radio" name="calc_neg" value="jp" checked>Jepang (¥)</label>
<label class="opt opt-kr" style="min-height: 48px; border-radius: 12px; font-size: 15px"><input type="radio" name="calc_neg" value="kr">Korea (₩)</label>
</div>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px">
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C"><span>Harga barang (<span data-o="sym">¥</span>)</span>
<input type="number" inputmode="decimal" min="0" value="{{ $h0 }}" data-calc-harga style="height: 48px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 17px; font-weight: 700; color: #15161C; background: #FFFFFF; min-width: 0">
</label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Perkiraan berat (kg)
<input type="number" inputmode="decimal" min="0" step="0.1" value="{{ $b0 }}" data-calc-berat style="height: 48px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 17px; font-weight: 700; color: #15161C; background: #FFFFFF; min-width: 0">
</label>
</div>
<div role="radiogroup" aria-label="Pilihan packing" style="display: flex; flex-direction: column; gap: 6px">
<span style="font-size: 13px; font-weight: 600; color: #4E515C">Packing</span>
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px">
@foreach ($packs as $pk)
<label class="opt opt-neutral" style="min-height: 58px; padding: 8px 6px; border-radius: 12px; flex-direction: column; gap: 2px"><input type="radio" name="calc_pack" value="{{ $pk['id'] }}" @checked($loop->first)><span style="font-size: 14px; font-weight: 700">{{ $pk['label'] }}</span><span style="font-size: 12px; font-weight: 500; color: #4E515C">{{ rp($pk['jual']) }}</span></label>
@endforeach
</div>
</div>
<div style="display: flex; flex-direction: column; gap: 10px; font-size: 14px">
<div style="border: 1px solid #E6E4DF; border-radius: 14px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.04em; color: #4E515C">TAHAP 1 · BAYAR DI WEB</span>
<div style="display: flex; justify-content: space-between; gap: 12px"><span style="color: #4E515C">Harga barang · <span data-o="hargaFmt">¥{{ angka($h0) }}</span> × <span data-o="kursFmt">{{ rpk($neg0['kj']) }}</span></span><strong style="white-space: nowrap" data-o="hargaIdr">{{ rp($h0 * $neg0['kj']) }}</strong></div>
</div>
<div style="background: #F7F6F2; border-radius: 14px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.04em; color: #4E515C">TAHAP 2 · BAYAR DI SHOPEE SAAT TIBA</span>
<div style="display: flex; justify-content: space-between; gap: 12px"><span style="color: #4E515C">Ongkir · <span data-o="beratBulat">{{ $bb0 }}</span> kg × <span data-o="tarifFmt">{{ rp($neg0['tj']) }}</span></span><strong style="white-space: nowrap" data-o="ongkir">{{ rp($bb0 * $neg0['tj']) }}</strong></div>
<div style="display: flex; justify-content: space-between; gap: 12px"><span style="color: #4E515C">Packing · <span data-o="packLabel">{{ $p0['label'] }}</span></span><strong style="white-space: nowrap" data-o="packFee">{{ rp($p0['jual']) }}</strong></div>
</div>
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 4px 4px 0"><span style="font-weight: 800; font-size: 15px">Estimasi total</span><span style="font-weight: 800; font-size: 26px; letter-spacing: -0.02em; white-space: nowrap" data-o="total">{{ rp($h0 * $neg0['kj'] + $bb0 * $neg0['tj'] + $p0['jual']) }}</span></div>
</div>
<p style="margin: 0; font-size: 13px; line-height: 1.5; color: #4E515C">Berat dibulatkan ke atas per kg (minimal 1 kg). Tahap 2 mengikuti berat timbang di warehouse <span data-o="gudang">Jepang</span>.</p>
<a href="{{ route('order.create') }}" data-o-accent style="display: flex; align-items: center; justify-content: center; min-height: 52px; border-radius: 12px; background: #C2311F; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 16px">Titip sekarang</a>
</section>
</section>

<section id="war" aria-labelledby="war-title" style="background: #15161C; color: #FFFFFF">
<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 64px 24px; display: flex; flex-direction: column; gap: 28px">
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 20px">
<div style="display: flex; flex-direction: column; gap: 10px; max-width: 640px">
<span style="font-size: 13px; font-weight: 800; letter-spacing: 0.06em; color: #FF8A78">JASA WAR · EDISI TERBATAS</span>
<h2 id="war-title" class="sec-title" style="margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.025em">Pop-up, MD konser, album limited? Biar kami yang war.</h2>
<p style="margin: 0; font-size: 17px; line-height: 1.6; color: #C9CAD1">Cukup DP {{ $warInfo['dp'] }}% untuk amankan slot, kami checkout tepat saat barang dibuka. Menang, lunasi sisanya. Kalah, DP dikembalikan.</p>
</div>
<a href="{{ route('war') }}" style="display: inline-flex; align-items: center; min-height: 52px; padding: 0 22px; border-radius: 12px; background: #FFFFFF; color: #15161C; text-decoration: none; font-weight: 700; font-size: 16px">Lihat jadwal war</a>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 16px">
@forelse ($drops as $d)
@php [$stLabel, $stC] = $d->statusPill(); @endphp
<a href="{{ $d->isOpen() ? route('war.register', $d) : route('war') }}" style="flex: 1 1 300px; background: #22242C; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 10px; text-decoration: none; color: #FFFFFF">
<div style="display: flex; justify-content: space-between; gap: 8px"><span style="font-size: 12px; font-weight: 700; color: {{ $d->country === 'kr' ? '#A9C4F0' : '#FFB3A7' }}">{{ $d->neg()['nama'] }}</span><span style="font-size: 12px; font-weight: 700; color: {{ ['green' => '#8FE0B5', 'amber' => '#FFD27A'][$stC] ?? '#C9CAD1' }}">{{ $stLabel }}</span></div>
<span style="font-size: 17px; font-weight: 800">{{ $d->name }}</span>
<span style="font-size: 14px; color: #C9CAD1">Rilis {{ tgl($d->release_at, 'j M') }} · {{ $d->release_at->format('H:i') }} WIB @if($d->isOpen()) · {{ $d->slotsLeft() }} slot tersisa @endif</span>
</a>
@empty
<div style="flex: 1 1 300px; background: #22242C; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 10px; color: #FFFFFF">
<span style="font-size: 17px; font-weight: 800">Belum ada jadwal war</span>
<span style="font-size: 14px; color: #C9CAD1">Daftar akun dan isi grup favoritmu, kami kabari lewat email saat ada war baru.</span>
</div>
@endforelse
</div>
<p style="margin: 0; font-size: 14px; color: #C9CAD1">Fee war {{ rp($warInfo['fee']) }} per barang · DP {{ $warInfo['dp'] }}% saat daftar · pendaftaran tutup {{ $warInfo['close'] }} jam sebelum rilis</p>
</div>
</section>

<section id="cara-kerja" aria-labelledby="ck-title" style="background: #F4F3EF">
<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 72px 24px; display: flex; flex-direction: column; gap: 36px">
<div style="display: flex; flex-direction: column; gap: 10px; max-width: 680px">
<h2 id="ck-title" class="sec-title" style="margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.025em">Cara titip, 6 langkah</h2>
<p style="margin: 0; font-size: 17px; line-height: 1.6; color: #4E515C">Bayar dua kali supaya adil: harga barang dulu di web, lalu ongkir dan packing di Shopee setelah berat aslinya diketahui.</p>
</div>
<ol style="list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 16px">
@foreach ([
    ['01 · Kamu', '#B02A19', 'Kirim link produk', 'Tempel link album atau merch dari toko resmi Korea atau Jepang, isi versi, jumlah, dan harganya di toko.'],
    ['02 · Kamu', '#B02A19', 'Bayar dengan kurs jastip', 'Harga barang × kurs jastip hari itu langsung terhitung. Transfer dan upload buktinya di form yang sama.'],
    ['03 · Kami', '#1D4E9E', 'Cek stok & checkout', 'Admin cek stok dan pembayaranmu, lalu checkout di toko aslinya dan kirim bukti checkout ke kamu.'],
    ['04 · Kami', '#1D4E9E', 'Tiba di warehouse', 'Barang di-unboxing di warehouse kami, difoto (termasuk photocard & POB) dan ditimbang. Fotonya dicek admin dulu, lalu dikirim ke kamu.'],
    ['05 · Kami', '#1D4E9E', 'Dikirim ke Indonesia', 'Barang ikut jadwal pengiriman berikutnya. Tanggalnya muncul di halaman lacak pesanan.'],
    ['06 · Kamu', '#B02A19', 'Checkout di Shopee', 'Sampai Indonesia, kami upload pesananmu di Shopee. Checkout di sana untuk bayar ongkir & packing, lalu barang dikirim.'],
] as [$no, $col, $judul, $isi])
<li style="flex: 1 1 300px; background: #FFFFFF; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; gap: 10px">
<span style="font-size: 14px; font-weight: 800; color: {{ $col }}">{{ $no }}</span>
<h3 style="margin: 0; font-size: 18px; font-weight: 800">{{ $judul }}</h3>
<p style="margin: 0; font-size: 15px; line-height: 1.55; color: #4E515C">{{ $isi }}</p>
</li>
@endforeach
</ol>
</div>
</section>

<section id="tarif" class="pad-sm" aria-labelledby="tarif-title" style="max-width: 1200px; margin: 0 auto; padding: 80px 24px; display: flex; flex-direction: column; gap: 36px">
<div style="display: flex; flex-direction: column; gap: 10px; max-width: 640px">
<h2 id="tarif-title" class="sec-title" style="margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.025em">Harga transparan, tanpa biaya tersembunyi</h2>
<p style="margin: 0; font-size: 17px; line-height: 1.6; color: #4E515C">Total yang kamu bayar dibagi dua tahap.</p>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 16px">
<div style="flex: 1 1 340px; border: 1px solid #E6E4DF; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; gap: 14px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.04em; color: #4E515C">TAHAP 1 · DI WEB, SEBELUM CHECKOUT</span>
<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-weight: 700; font-size: 16px">
<span style="padding: 12px 16px; border-radius: 12px; background: #F4F3EF">Harga barang</span>
<span aria-hidden="true" style="font-size: 20px; color: #4E515C">×</span>
<span style="padding: 12px 16px; border-radius: 12px; background: #F4F3EF">Kurs jastip</span>
</div>
</div>
<div style="flex: 1 1 340px; border: 1px solid #E6E4DF; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; gap: 14px">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.04em; color: #4E515C">TAHAP 2 · DI SHOPEE, SETELAH TIBA</span>
<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-weight: 700; font-size: 16px">
<span style="padding: 12px 16px; border-radius: 12px; background: #F4F3EF">Berat (kg) × tarif/kg</span>
<span aria-hidden="true" style="font-size: 20px; color: #4E515C">+</span>
<span style="padding: 12px 16px; border-radius: 12px; background: #F4F3EF">Packing</span>
</div>
</div>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 16px">
@foreach (['jp' => ['Jepang', '#C2311F', '¥', 'estimasi_jp'], 'kr' => ['Korea', '#1D4E9E', '₩', 'estimasi_kr']] as $k => [$nm, $col, $sym, $est])
<article style="flex: 1 1 340px; border: 1px solid #E6E4DF; border-top: 4px solid {{ $col }}; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; gap: 16px">
<h3 style="margin: 0; font-size: 20px; font-weight: 800">{{ $nm }}</h3>
<dl style="margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 15px">
<div style="display: flex; justify-content: space-between; gap: 12px"><dt style="color: #4E515C">Kurs jastip</dt><dd style="margin: 0; font-weight: 700">{{ $sym }}1 = {{ rpk($harga[$k]['kj']) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 12px"><dt style="color: #4E515C">Ongkir per kg</dt><dd style="margin: 0; font-weight: 700">{{ rp($harga[$k]['tj']) }}</dd></div>
<div style="display: flex; justify-content: space-between; gap: 12px"><dt style="color: #4E515C">Estimasi tiba</dt><dd style="margin: 0; font-weight: 700">{{ config('jastip.'.$est) }} hari</dd></div>
</dl>
</article>
@endforeach
</div>
<div style="display: flex; flex-direction: column; gap: 14px">
<h3 style="margin: 0; font-size: 20px; font-weight: 800">Pilihan packing</h3>
<div style="display: flex; flex-wrap: wrap; gap: 16px">
@foreach ($packs as $k)
<div style="flex: 1 1 260px; background: #F4F3EF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 8px">
<div style="display: flex; justify-content: space-between; align-items: baseline; gap: 12px"><span style="font-size: 17px; font-weight: 800">{{ $k['label'] }}</span><span style="font-size: 16px; font-weight: 700">{{ rp($k['jual']) }}</span></div>
<p style="margin: 0; font-size: 14.5px; line-height: 1.55; color: #4E515C">{{ $k['desc'] }}</p>
</div>
@endforeach
</div>
</div>
</section>

<section aria-labelledby="bisa-title" style="background: #F4F3EF">
<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 64px 24px; display: flex; flex-wrap: wrap; gap: 40px">
<div style="flex: 1 1 420px; display: flex; flex-direction: column; gap: 16px">
<h2 id="bisa-title" style="margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.02em">Bisa titip apa saja?</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 10px; font-size: 15px; font-weight: 600">
@foreach (['Album (semua versi)', 'Album versi Jepang', 'Lightstick', 'Photocard & POB toko', "Season's greetings", 'MD konser & pop-up store', 'Majalah & photobook', 'Merch official lainnya'] as $x)
<li style="padding: 10px 16px; background: #FFFFFF; border-radius: 999px">{{ $x }}</li>
@endforeach
</ul>
</div>
<div style="flex: 1 1 340px; background: #FFFFFF; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; gap: 8px">
<h3 style="margin: 0; font-size: 17px; font-weight: 800">Tidak menerima</h3>
<p style="margin: 0; font-size: 15px; line-height: 1.6; color: #4E515C">{{ config('jastip.barang_terlarang') }}</p>
</div>
</div>
</section>

<section id="faq" class="pad-sm" aria-labelledby="faq-title" style="max-width: 860px; margin: 0 auto; padding: 80px 24px; display: flex; flex-direction: column; gap: 24px">
<h2 id="faq-title" class="sec-title" style="margin: 0; font-size: 36px; font-weight: 800; letter-spacing: -0.025em">Pertanyaan umum</h2>
<div style="display: flex; flex-direction: column; border-top: 1px solid #E6E4DF">
@foreach ([
    ['Kenapa ongkir dibayar belakangan?', 'Berat pasti baru diketahui setelah barang tiba di warehouse dan ditimbang. Jadi kamu hanya membayar ongkir sesuai berat sebenarnya, bukan perkiraan.'],
    ['Kenapa tahap 2 dibayar lewat Shopee?', 'Supaya pembayaran ongkir & packing tercatat dan dilindungi sistem Shopee sampai barang kamu terima. Kami upload pesananmu sebagai produk khusus, kamu tinggal checkout.'],
    ['Kurs apa yang dipakai?', 'Kurs jastip yang tertera di halaman ini, diperbarui admin secara berkala. Kurs dikunci saat kamu mengirim titipan dan membayar.'],
    ['Bagaimana berat dihitung?', 'Berat dibulatkan ke atas per kg, minimal 1 kg, sesuai hasil timbang di warehouse.'],
    ['Bagaimana kalau barang habis atau harganya berbeda?', 'Admin menghubungi kamu dulu sebelum checkout. Kalau barang habis, pembayaran dikembalikan ke rekening refund kamu atau diganti varian lain kalau kamu setuju.'],
    ['Bisa pilih photocard member tertentu?', 'Photocard di album biasanya acak dari toko, jadi tidak bisa dipilih. Kamu bisa lihat photocard yang didapat lewat foto unboxing sebelum barang dikirim.'],
    ['Bagaimana dengan POB dan event (lucky draw / fansign)?', 'POB mengikuti toko yang kamu pilih lewat link. Untuk event lucky draw atau fansign, tanyakan admin dulu sebelum titip.'],
] as [$q, $a])
<details style="border-bottom: 1px solid #E6E4DF; padding: 18px 0">
<summary style="font-size: 17px; font-weight: 700; cursor: pointer; min-height: 28px">{{ $q }}</summary>
<p style="margin: 12px 0 0; font-size: 15.5px; line-height: 1.65; color: #4E515C">{{ $a }}</p>
</details>
@endforeach
</div>
</section>

<footer id="kontak" style="background: #15161C; color: #FFFFFF">
<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 56px 24px; display: flex; flex-wrap: wrap; gap: 24px; align-items: center; justify-content: space-between">
<div style="display: flex; flex-direction: column; gap: 8px">
<h2 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Ada album atau merch incaran?</h2>
<p style="margin: 0; font-size: 16px; color: #C9CAD1">Tanya stok dan estimasi harga langsung ke admin. <a href="{{ route('terms') }}" style="color: #FFFFFF; font-weight: 700">Syarat &amp; ketentuan</a></p>
</div>
<div style="display: flex; gap: 12px; flex-wrap: wrap">
<a href="https://wa.me/{{ preg_replace('/\D/', '', config('jastip.whatsapp')) }}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; min-height: 52px; padding: 0 22px; border-radius: 12px; background: #FFFFFF; color: #15161C; text-decoration: none; font-weight: 700">WhatsApp: {{ config('jastip.whatsapp') }}</a>
<a href="{{ config('jastip.shopee_url') }}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; min-height: 52px; padding: 0 22px; border-radius: 12px; border: 1.5px solid #FFFFFF; color: #FFFFFF; text-decoration: none; font-weight: 700">Toko Shopee: {{ config('jastip.shopee_toko') }}</a>
</div>
</div>
</footer>

</div>
<script>window.HARGA = @json($hargaJs);</script>
@endsection
