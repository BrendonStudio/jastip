<?php

/*
| Info toko yang tampil di website. Ubah lewat file .env
*/
return [
    'nama' => env('JASTIP_NAMA', 'Jastip Seoul Tokyo'),
    'whatsapp' => env('JASTIP_WHATSAPP', '628xxxxxxxxxx'),
    'shopee_toko' => env('JASTIP_SHOPEE_TOKO', 'nama_toko'),
    'shopee_url' => env('JASTIP_SHOPEE_URL', 'https://shopee.co.id/'),
    'rekening' => env('JASTIP_REKENING', 'BCA 1234567890 a.n. Nama Pemilik'),
    'estimasi_jp' => env('JASTIP_ESTIMASI_JP', '7–14'),
    'estimasi_kr' => env('JASTIP_ESTIMASI_KR', '7–14'),
    'barang_terlarang' => env('JASTIP_BARANG_TERLARANG', 'Cairan mudah terbakar, baterai lepas, obat-obatan, senjata, dan barang yang dilarang bea cukai.'),
    'kurs_dikunci_jam' => (int) env('JASTIP_KURS_DIKUNCI_JAM', 24),
    // Syarat & ketentuan
    'refund_hari' => (int) env('JASTIP_REFUND_HARI', 3),          // refund diproses maks. N hari kerja
    'klaim_jam' => (int) env('JASTIP_KLAIM_JAM', 48),              // batas lapor kerusakan setelah barang diterima
    'bea_masuk' => env('JASTIP_BEA_MASUK', 'sudah termasuk tarif ongkir per kg'),
    'diperbarui' => env('JASTIP_KETENTUAN_TANGGAL', '28 Oktober 2026'),
];
