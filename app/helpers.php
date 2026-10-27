<?php

if (! function_exists('rp')) {
    /** Format rupiah bulat: Rp 1.234.567 (minus pakai tanda −). */
    function rp($n): string
    {
        $n = (float) $n;
        return ($n < -0.5 ? '−Rp ' : 'Rp ').number_format(abs(round($n)), 0, ',', '.');
    }
}

if (! function_exists('rpk')) {
    /** Format rupiah sampai 2 desimal (untuk kurs): Rp 11,3 */
    function rpk($n): string
    {
        $n = (float) $n;
        $s = number_format(abs($n), 2, ',', '.');
        $s = rtrim(rtrim($s, '0'), ',');
        return ($n < 0 ? '−Rp ' : 'Rp ').$s;
    }
}

if (! function_exists('angka')) {
    /** Angka format Indonesia, maks $dec desimal. */
    function angka($n, int $dec = 2): string
    {
        $s = number_format((float) $n, $dec, ',', '.');
        if ($dec > 0) {
            $s = rtrim(rtrim($s, '0'), ',');
        }
        return $s;
    }
}

if (! function_exists('kg_bulat')) {
    /** Berat dibulatkan ke atas per kg, minimal 1 kg. */
    function kg_bulat($kg): int
    {
        return max(1, (int) ceil(((float) $kg) - 1e-9));
    }
}

if (! function_exists('upload_url')) {
    function upload_url(?string $path): ?string
    {
        return $path ? asset('uploads/'.ltrim($path, '/')) : null;
    }
}

if (! function_exists('negara')) {
    /** Info tampilan per negara. */
    function negara(?string $kode): array
    {
        return $kode === 'kr'
            ? ['kode' => 'kr', 'nama' => 'Korea', 'sym' => '₩', 'zona' => 'KST', 'fg' => '#1D4E9E', 'bg' => '#EAF0FA', 'line' => '#1D4E9E']
            : ['kode' => 'jp', 'nama' => 'Jepang', 'sym' => '¥', 'zona' => 'JST', 'fg' => '#B02A19', 'bg' => '#FBEDEA', 'line' => '#C2311F'];
    }
}

if (! function_exists('pill')) {
    /** Warna pill status: red, amber, blue, green, gray. */
    function pill(string $c): array
    {
        return [
            'red' => ['fg' => '#B02A19', 'bg' => '#FBEDEA'],
            'amber' => ['fg' => '#7A4B00', 'bg' => '#FFF0D1'],
            'blue' => ['fg' => '#1A3F7F', 'bg' => '#EAF0FA'],
            'green' => ['fg' => '#16653F', 'bg' => '#E6F4EC'],
            'gray' => ['fg' => '#4E515C', 'bg' => '#F0EEE9'],
            'purple' => ['fg' => '#5B3A9E', 'bg' => '#EDE7F6'],
            'teal' => ['fg' => '#0F6B66', 'bg' => '#E0F2F1'],
            'ink' => ['fg' => '#15161C', 'bg' => '#F0EEE9'],
        ][$c] ?? ['fg' => '#4E515C', 'bg' => '#F0EEE9'];
    }
}

if (! function_exists('tgl')) {
    function tgl($d, string $fmt = 'j M Y'): string
    {
        if (! $d) {
            return '—';
        }
        return \Illuminate\Support\Carbon::parse($d)->locale('id')->translatedFormat($fmt);
    }
}

if (! function_exists('nama_jastip')) {
    function nama_jastip(): string
    {
        return config('jastip.nama');
    }
}

if (! function_exists('inisial')) {
    function inisial(?string $nama): string
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            return '?';
        }
        $parts = preg_split('/\s+/', $nama);
        $s = mb_substr($parts[0], 0, 1);
        if (count($parts) > 1) {
            $s .= mb_substr(end($parts), 0, 1);
        }
        return mb_strtoupper($s);
    }
}

if (! function_exists('avatar_bg')) {
    /** Warna avatar inisial, tetap untuk nama yang sama. */
    function avatar_bg(?string $nama): string
    {
        $av = ['#1D4E9E', '#B02A19', '#5B3A9E', '#0F6B66', '#7A4B00', '#B4410F'];
        $sum = 0;
        foreach (preg_split('//u', (string) $nama, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $sum += mb_ord($ch);
        }

        return $av[$sum % count($av)];
    }
}

if (! function_exists('lalu')) {
    /** Selisih waktu singkat: "15 mnt", "2 jam", "3 hari". */
    function lalu($d): string
    {
        if (! $d) {
            return '—';
        }
        $m = max(0, (int) \Illuminate\Support\Carbon::parse($d)->diffInMinutes(now()));
        if ($m < 1) {
            return 'baru saja';
        }
        if ($m < 60) {
            return $m.' mnt';
        }
        if ($m < 60 * 24) {
            return intdiv($m, 60).' jam';
        }
        if ($m < 60 * 24 * 30) {
            return intdiv($m, 60 * 24).' hari';
        }

        return intdiv($m, 60 * 24 * 30).' bln';
    }
}

if (! function_exists('yg_lalu')) {
    /** "2 jam lalu" / "baru saja". */
    function yg_lalu($d): string
    {
        $s = lalu($d);

        return in_array($s, ['baru saja', '—'], true) ? $s : $s.' lalu';
    }
}
