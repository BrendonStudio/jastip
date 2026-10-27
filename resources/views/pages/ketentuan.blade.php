@extends('layouts.site')
@section('title', 'Syarat & ketentuan')

@php
    $jam = config('jastip.kurs_dikunci_jam');
    $wa = 'https://wa.me/'.preg_replace('/\D/', '', config('jastip.whatsapp'));
    $toc = [
        'k1' => 'Alur & pembayaran dua tahap', 'k2' => 'Kurs & masa berlaku penawaran', 'k3' => 'Jasa war, DP & pelunasan',
        'k4' => 'Refund & pembatalan', 'k5' => 'Berat, ongkir & packing', 'k6' => 'Photocard, POB & event',
        'k7' => 'Kerusakan & klaim', 'k8' => 'Barang terlarang', 'k9' => 'Data pribadi',
    ];
    $h2 = 'margin: 0; font-size: 22px; font-weight: 800; color: #15161C; scroll-margin-top: 24px';
    $ul = 'margin: 0; padding-left: 22px; display: flex; flex-direction: column; gap: 8px';
@endphp

@section('content')
<div class="dsk">@include('partials.header')</div>
<div class="mob">@include('partials.mob-header', ['title' => 'Syarat & ketentuan', 'back' => url()->previous() !== url()->current() ? url()->previous() : route('home')])</div>

<div style="background: #F4F3EF">
<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 48px 24px 40px; display: flex; flex-direction: column; gap: 10px">
<span style="font-size: 13px; font-weight: 800; letter-spacing: 0.06em; color: #B02A19">KETENTUAN LAYANAN</span>
<h1 class="sec-title" style="margin: 0; font-size: 40px; font-weight: 800; letter-spacing: -0.03em">Syarat &amp; ketentuan</h1>
<p style="margin: 0; font-size: 16px; color: #4E515C; max-width: 640px">Berlaku untuk semua titipan dan jasa war di {{ nama_jastip() }}. Terakhir diperbarui {{ config('jastip.diperbarui') }}.</p>
</div>
</div>

<div class="pad-sm" style="max-width: 1200px; margin: 0 auto; padding: 40px 24px 80px; display: flex; flex-wrap: wrap; gap: 48px; align-items: flex-start">
<nav aria-label="Daftar isi" class="toc" style="flex: 1 1 220px; max-width: 260px; position: sticky; top: 24px; display: flex; flex-direction: column; gap: 2px; font-size: 14.5px; font-weight: 600">
<span style="font-size: 12px; font-weight: 800; letter-spacing: 0.05em; color: #4E515C; padding: 0 12px 8px">DAFTAR ISI</span>
@foreach ($toc as $id => $t)
<a href="#{{ $id }}" class="toc-a" style="text-decoration: none; padding: 10px 12px; border-radius: 10px">{{ $loop->iteration }}. {{ $t }}</a>
@endforeach
</nav>

<article style="flex: 999 1 560px; min-width: 0; max-width: 760px; display: flex; flex-direction: column; gap: 40px; font-size: 16px; line-height: 1.7; color: #2C2E36">

<section id="k1" aria-labelledby="h-k1" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k1" style="{{ $h2 }}">1. Alur &amp; pembayaran dua tahap</h2>
<p style="margin: 0">Setiap titipan dibayar dalam dua tahap:</p>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px">
<div style="background: #F7F6F2; border-radius: 14px; padding: 16px"><strong style="color: #15161C">Tahap 1 · di website</strong><br>Harga barang × kurs jastip, dibayar sebelum kami checkout.</div>
<div style="background: #F7F6F2; border-radius: 14px; padding: 16px"><strong style="color: #15161C">Tahap 2 · di Shopee</strong><br>Ongkir (berat × tarif per kg) + packing, dibayar setelah barang tiba di Indonesia.</div>
</div>
<p style="margin: 0">Kami baru membeli barang setelah tahap 1 terverifikasi. Bukti checkout dari toko dikirim ke emailmu.</p>
</section>

<section id="k2" aria-labelledby="h-k2" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k2" style="{{ $h2 }}">2. Kurs &amp; masa berlaku penawaran</h2>
<ul style="{{ $ul }}">
<li>Kurs jastip ditampilkan di website dan bisa berubah setiap hari mengikuti kurs pasar.</li>
<li>Kurs dikunci saat penawaran dikirim dan berlaku <strong>{{ $jam }} jam</strong>. Lewat dari itu, penawaran hangus dan harga dihitung ulang.</li>
<li>Harga dari toko bisa berubah sebelum kamu membayar. Kalau berubah, kami kirim penawaran baru.</li>
</ul>
</section>

<section id="k3" aria-labelledby="h-k3" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k3" style="{{ $h2 }}">3. Jasa war, DP &amp; pelunasan</h2>
<ul style="{{ $ul }}">
<li>Pendaftaran war membutuhkan <strong>DP</strong> (umumnya 30%) dari harga barang + fee war, dibayar sebelum pendaftaran tutup. Besaran DP tertulis di setiap drop.</li>
<li>War tidak dijamin menang. Nomor antrian menentukan urutan pembelian.</li>
<li>Jika menang, sisa pembayaran wajib dilunasi dalam batas waktu yang tertulis di email hasil war (umumnya 24 jam). Lewat batas waktu, <strong>DP hangus</strong> dan barang dapat dijual ke peserta lain.</li>
<li>Jika kalah, DP dikembalikan penuh atau dipotong fee war sesuai aturan drop, dalam {{ config('jastip.refund_hari') }} hari kerja.</li>
</ul>
</section>

<section id="k4" aria-labelledby="h-k4" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k4" style="{{ $h2 }}">4. Refund &amp; pembatalan</h2>
<ul style="{{ $ul }}">
<li>Penawaran boleh ditolak tanpa biaya selama belum dibayar.</li>
<li>Setelah kami checkout, pesanan <strong>tidak bisa dibatalkan</strong>.</li>
<li>Jika barang habis atau dibatalkan oleh toko, dana tahap 1 dikembalikan penuh ke rekening refund di akunmu.</li>
<li>Refund diproses dalam {{ config('jastip.refund_hari') }} hari kerja dan tercatat di <a href="{{ route('history') }}" style="font-weight: 700">Riwayat pembayaran</a>.</li>
</ul>
</section>

<section id="k5" aria-labelledby="h-k5" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k5" style="{{ $h2 }}">5. Berat, ongkir &amp; packing</h2>
<ul style="{{ $ul }}">
<li>Berat ditimbang di warehouse dan dibulatkan ke atas per kg, minimal 1 kg.</li>
<li>Foto timbangan disertakan bersama foto unboxing.</li>
<li>Estimasi pengiriman {{ config('jastip.estimasi_jp') }} hari (Jepang) dan {{ config('jastip.estimasi_kr') }} hari (Korea) sejak batch dikirim. Keterlambatan dari kargo atau bea cukai di luar kendali kami.</li>
<li>Bea masuk dan pajak impor {{ config('jastip.bea_masuk') }}.</li>
</ul>
</section>

<section id="k6" aria-labelledby="h-k6" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k6" style="{{ $h2 }}">6. Photocard, POB &amp; event</h2>
<ul style="{{ $ul }}">
<li>Photocard di dalam album bersifat acak dari toko dan tidak dapat dipilih.</li>
<li>POB mengikuti toko yang kamu pilih lewat link dan ketersediaan dari toko.</li>
<li>Pendaftaran lucky draw / fansign mengikuti aturan toko; hasilnya diumumkan toko dan kami teruskan apa adanya.</li>
</ul>
</section>

<section id="k7" aria-labelledby="h-k7" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k7" style="{{ $h2 }}">7. Kerusakan &amp; klaim</h2>
<ul style="{{ $ul }}">
<li>Foto unboxing di warehouse menjadi acuan kondisi barang sebelum dikirim.</li>
<li>Kerusakan setelah tiba wajib dilaporkan maksimal {{ config('jastip.klaim_jam') }} jam dengan <strong>video unboxing</strong> tanpa jeda.</li>
<li>Kerusakan karena pengiriman domestik di Indonesia diklaim lewat Shopee sesuai aturan Shopee.</li>
</ul>
</section>

<section id="k8" aria-labelledby="h-k8" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k8" style="{{ $h2 }}">8. Barang terlarang</h2>
<p style="margin: 0">Kami tidak menerima titipan: {{ \Illuminate\Support\Str::lcfirst(config('jastip.barang_terlarang')) }}</p>
</section>

<section id="k9" aria-labelledby="h-k9" style="display: flex; flex-direction: column; gap: 12px">
<h2 id="h-k9" style="{{ $h2 }}">9. Data pribadi</h2>
<p style="margin: 0">Email, username Shopee, nomor WhatsApp, dan rekening refund hanya dipakai untuk memproses pesanan dan mengirim kabar terkait pesananmu. Kami tidak menjual atau membagikan datamu ke pihak lain. Info war &amp; rilisan baru bisa dihentikan kapan saja dari link di email.</p>
</section>

<div style="background: #15161C; color: #FFFFFF; border-radius: 18px; padding: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 4px"><strong style="font-size: 18px">Masih ada pertanyaan?</strong><span style="font-size: 14.5px; color: #C9CAD1">Admin siap bantu sebelum kamu menitip.</span></div>
<a href="{{ $wa }}" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 20px; border-radius: 12px; background: #FFFFFF; color: #15161C; text-decoration: none; font-weight: 700">Hubungi admin</a>
</div>
</article>
</div>
@endsection
