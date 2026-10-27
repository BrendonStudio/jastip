<?php

namespace App\Support;

use App\Mail\Notice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notify
{
    /**
     * Kirim email kabar. Gagal kirim tidak menghentikan proses (dicatat di log).
     *
     * $opt (semua opsional):
     *  - tag:       label kecil di atas judul, mis. "PENAWARAN SIAP"
     *  - c:         warna tag & tombol: red | green | blue | purple | orange | gray
     *  - rows:      rincian [[label, nilai, tebal?], …]
     *  - note:      catatan kecil di bawah tombol
     *  - photos:    url foto (maks 4) untuk ditampilkan berjajar
     *  - pre:       teks pratinjau di kotak masuk
     *  - marketing: true = tampilkan link berhenti langganan info war
     */
    public static function send(User|string $to, string $judul, array $baris = [], ?string $tombol = null, ?string $url = null, ?string $kode = null, array $opt = []): bool
    {
        $email = $to instanceof User ? $to->email : $to;
        $nama = $to instanceof User ? $to->displayName() : null;
        try {
            Mail::to($email)->send(new Notice($judul, $baris, $tombol, $url, $kode, $opt + ['email' => $email, 'nama' => $nama, 'user_id' => $to instanceof User ? $to->id : null]));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email ke '.$email.': '.$e->getMessage());

            return false;
        }
    }

    /** Email belum disetel (mode uji) → kode OTP ditampilkan di layar. */
    public static function isTestMode(): bool
    {
        return in_array(config('mail.default'), ['log', 'array'], true);
    }
}
