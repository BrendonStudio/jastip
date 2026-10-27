# Website Jastip Jepang & Korea (Laravel, untuk Herd)

Tampilan mengikuti design canvas "Website Jastip Jepang & Korea": versi HP dan desktop
untuk penitip + panel admin 7 menu. Laravel + Blade + JavaScript biasa, tanpa Filament, tanpa npm.

## Cara menjalankan (sekali saja)
Buka PowerShell:

```powershell
cd C:\Users\rendy\Herd\jastip
powershell -ExecutionPolicy Bypass -File .\setup.ps1
```

Setelah selesai, buka http://jastip.test (Herd menyajikan folder ini otomatis).
- Admin: http://jastip.test/admin — `admin@jastip.test` / `admin12345`
- Penitip: masuk dengan email apa saja; selama email belum disetel, kode masuk tampil di layar.
- Data contoh sudah terisi (penitip contoh `nadia@contoh.test`). Untuk database kosong: `.\setup.ps1 -TanpaContoh`

## Mengubah info toko & email
Edit file `.env`:
- `JASTIP_NAMA`, `JASTIP_WHATSAPP`, `JASTIP_SHOPEE_TOKO`, `JASTIP_SHOPEE_URL`, `JASTIP_REKENING`, `JASTIP_ESTIMASI_JP/KR`
- `ADMIN_EMAIL` / `ADMIN_PASSWORD` (dipakai saat seeding)
- Email sungguhan (kode masuk & kabar pesanan): ganti `MAIL_MAILER=log` ke SMTP, contoh Gmail ada di dalam `.env`.

Harga (kurs, tarif per kg, packing) diatur dari **Admin › Pengaturan harga**, langsung tampil di website.

## Alur yang sudah jalan
**Penitip:** daftar/masuk pakai kode email → lengkapi profil → minta penawaran (banyak barang + screenshot)
→ terima penawaran → upload bukti transfer → lacak 9 tahap (bukti checkout, foto unboxing, berat, jadwal kirim)
→ bayar tahap 2 lewat link Shopee. Jasa war: daftar + upload DP, nomor antrian, menang → upload pelunasan, kalah → refund.

**Admin:** Ringkasan (tugas hari ini, aktivitas) · Pesanan per tahap (penawaran → lunas → checkout → foto & berat
→ batch → tiba → link Shopee → selesai) · Jasa war (drop, kuota, jam JST/KST → WIB, hasil menang/kalah, kirim tagihan,
konfirmasi lunas jadi pesanan, refund) · Jadwal kirim (batch, kapasitas, untung ongkir, status kabari penitip)
· Pengaturan harga + simulasi untung · Keuangan (profit per sumber, cair vs belum, biaya bulanan, unduh CSV)
· Pelanggan (cari, catatan, tandai bermasalah = tidak bisa ikut war).

Setiap pindah tahap, penitip dikabari lewat email.

## Letak file penting
- Tampilan: `resources/views` (`pages/` penitip, `admin/` panel admin, `auth/` masuk/daftar)
- Gaya & interaksi: `public/css/app.css`, `public/js/app.js`
- Logika: `app/Http/Controllers`, `app/Models`, rumus profit di `app/Support/Keuangan.php`
- Upload foto tersimpan di `public/uploads`
- Database: `database/database.sqlite` (bisa diganti MySQL lewat `.env`)

Teks FAQ & syarat bisa diedit langsung di `resources/views/pages/home.blade.php`.
