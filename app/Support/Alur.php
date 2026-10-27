<?php

namespace App\Support;

use App\Models\Order;
use App\Models\User;
use App\Models\WarEntry;
use Illuminate\Support\Collection;

/**
 * Posisi barang & langkah berikutnya untuk panel admin
 * (dipakai Ringkasan, Pesanan, dan badge menu).
 */
class Alur
{
    /** 6 posisi barang. Tiap posisi mencakup beberapa tahap pesanan. */
    public const POS = [
        ['t' => 'Menunggu bayar', 'sub' => 'Di penitip', 'stages' => [0, 1], 'c' => 'amber'],
        ['t' => 'Dibeli', 'sub' => 'Di toko', 'stages' => [2], 'c' => 'red'],
        ['t' => 'Di gudang', 'sub' => 'Warehouse kita', 'stages' => [3, 4], 'c' => 'purple'],
        ['t' => 'Perjalanan', 'sub' => 'Menuju Indonesia', 'stages' => [5], 'c' => 'blue'],
        ['t' => 'Di Indonesia', 'sub' => 'Siap ke Shopee', 'stages' => [6, 7], 'c' => 'teal'],
        ['t' => 'Selesai', 'sub' => 'Diterima penitip', 'stages' => [8], 'c' => 'green'],
    ];

    /** 5 posisi pendaftar war. */
    public const WAR = [
        ['k' => 'daftar', 't' => 'Terdaftar', 'sub' => 'Menunggu hari war'],
        ['k' => 'menang', 't' => 'Menang', 'sub' => 'Tagih sisa'],
        ['k' => 'lunas', 't' => 'Lunas', 'sub' => 'Masuk alur pesanan'],
        ['k' => 'kalah', 't' => 'Kalah', 'sub' => 'Refund DP'],
        ['k' => 'refund', 't' => 'DP kembali', 'sub' => 'Selesai'],
    ];

    /** Warna tiap posisi (teks, latar, titik) untuk tabel Pesanan. */
    public const POS_COLOR = [
        ['fg' => '#7A4B00', 'bg' => '#FFF0D1', 'dot' => '#D99A1E'],
        ['fg' => '#B02A19', 'bg' => '#FBEDEA', 'dot' => '#C2311F'],
        ['fg' => '#5B3A9E', 'bg' => '#EDE7F6', 'dot' => '#7E57C2'],
        ['fg' => '#1A3F7F', 'bg' => '#EAF0FA', 'dot' => '#1D4E9E'],
        ['fg' => '#0F6B66', 'bg' => '#E0F2F1', 'dot' => '#14857E'],
        ['fg' => '#16653F', 'bg' => '#E6F4EC', 'dot' => '#1D7A4C'],
    ];

    public const WAR_COLOR = [
        ['fg' => '#1A3F7F', 'bg' => '#EAF0FA', 'dot' => '#1D4E9E'],
        ['fg' => '#7A4B00', 'bg' => '#FFF0D1', 'dot' => '#D99A1E'],
        ['fg' => '#16653F', 'bg' => '#E6F4EC', 'dot' => '#1D7A4C'],
        ['fg' => '#B02A19', 'bg' => '#FBEDEA', 'dot' => '#C2311F'],
        ['fg' => '#4E515C', 'bg' => '#F0EEE9', 'dot' => '#8C8E98'],
    ];

    /** Ikon garis (isi <svg>) untuk tiap titik posisi. */
    public const POS_ICON = [
        '<rect x="3" y="6" width="18" height="13" rx="2"></rect><path d="M3 10h18"></path>',
        '<path d="M6 8h12l-1 12H7Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path>',
        '<path d="M3 10 12 4l9 6v10H3Z"></path><path d="M8 20v-6h8v6"></path>',
        '<path d="M22 2 11 13"></path><path d="M22 2 15 22l-4-9-9-4Z"></path>',
        '<path d="M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12Z"></path><circle cx="12" cy="9" r="2.5"></circle>',
        '<path d="M20 6 9 17l-5-5"></path>',
    ];

    public const WAR_ICON = [
        '<circle cx="12" cy="12" r="8"></circle><path d="M12 8v4l3 2"></path>',
        '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9Z"></path>',
        '<path d="M20 6 9 17l-5-5"></path>',
        '<path d="M6 6l12 12M18 6 6 18"></path>',
        '<path d="M9 14 4 9l5-5"></path><path d="M4 9h11a5 5 0 0 1 0 10h-3"></path>',
    ];

    protected static ?Collection $tasks = null;

    public static function pos(Order $o): int
    {
        if ($o->cancelled_at) {
            return count(self::POS) - 1;
        }
        foreach (self::POS as $i => $p) {
            if (in_array(min($o->stage, Order::DONE), $p['stages'], true)) {
                return $i;
            }
        }

        return 0;
    }

    protected static array $whStaff = [];

    /** Ada staf warehouse aktif untuk negara ini? (kalau tidak, admin yang upload foto) */
    public static function hasWarehouse(string $country): bool
    {
        return self::$whStaff[$country] ??= User::where('is_admin', true)->where('role', 'wh')
            ->where(fn ($q) => $q->where('wh_country', $country)->orWhereNull('wh_country'))->whereNotNull('password')->exists();
    }

    public static function posStages(int $i): array
    {
        return self::POS[$i]['stages'] ?? [];
    }

    /**
     * Langkah berikutnya untuk satu pesanan.
     * kind: need (perlu tindakanmu), wait (menunggu orang lain), done.
     */
    public static function step(Order $o): array
    {
        $r = fn ($t, $kind, $task = null) => ['t' => $t, 'kind' => $kind, 'task' => $task ?? $t];
        if ($o->cancelled_at) {
            return ($o->refund_amount > 0 && ! $o->refunded_at) ? $r('Kirim refund', 'need') : $r('Dibatalkan', 'done');
        }
        if ($o->refund_amount > 0 && ! $o->refunded_at) {
            return $r('Kirim refund', 'need');
        }

        return match ($o->stage) {
            Order::REQUEST => $r('Kirim penawaran', 'need'),
            Order::OFFERED => match (true) {
                (bool) $o->payment_proof => $r('Konfirmasi pembayaran', 'need', 'Cek transfer '.rp($o->tahap1())),
                $o->offerExpired() => $r('Penawaran hangus', 'wait'),
                (bool) $o->payment_rejected_at => $r('Tunggu upload ulang bukti', 'wait'),
                default => $r('Menunggu transfer', 'wait'),
            },
            Order::PAID => $r('Checkout & kirim bukti', 'need', 'Checkout di toko'),
            Order::CHECKED_OUT => match (true) {
                $o->wh_submitted_at !== null || ($o->photoList() && ! self::hasWarehouse($o->country)) => $r('Setujui foto unboxing', 'need'),
                self::hasWarehouse($o->country) => $r($o->wh_arrived_at ? 'Warehouse sedang foto' : 'Tunggu tiba di gudang', 'wait'),
                default => $r('Upload foto warehouse', 'need'),
            },
            Order::WAREHOUSE => $r('Masukkan ke batch', 'need', 'Masukkan ke batch kirim'),
            Order::SCHEDULED => ($o->batch && $o->batch->status >= 1) ? $r('Tandai tiba', 'wait') : $r('Tunggu batch dikirim', 'wait'),
            Order::ARRIVED => $r('Upload ke Shopee', 'need'),
            Order::SHOPEE => $o->shopee_reported_at
                ? $r('Cek checkout Shopee', 'need')
                : $r('Tunggu checkout Shopee'.(($d = $o->shopeeWaitDays()) >= 3 ? ' · '.$d.' hari' : ''), 'wait'),
            default => $r('Selesai', 'done'),
        };
    }

    public static function warIndex(WarEntry $e): int
    {
        $k = match (true) {
            (bool) $e->order_id => 'lunas',
            $e->result === 'menang' => 'menang',
            $e->result === 'kalah' && $e->refunded_at !== null => 'refund',
            $e->result === 'kalah' => 'kalah',
            default => 'daftar',
        };

        return array_search($k, array_column(self::WAR, 'k'), true);
    }

    /** Langkah berikutnya untuk pendaftar war. */
    public static function warStep(WarEntry $e): array
    {
        $pct = (int) ($e->drop->dp_pct ?? 30);
        $i = self::warIndex($e);
        $k = self::WAR[$i]['k'];

        return match ($k) {
            'menang' => $e->settle_proof
                ? ['t' => 'Cek pelunasan '.rp($e->remaining()), 'kind' => 'need', 'pay' => $pct, 'payLabel' => 'Bukti pelunasan masuk', 'title' => 'Konfirmasi pelunasan '.(100 - $pct).'%', 'desc' => 'Penitip sudah upload bukti pelunasan '.rp($e->remaining()).'. Cek buktinya, lalu tandai lunas.']
                : ['t' => 'Tagih sisa '.(100 - $pct).'%', 'kind' => 'need', 'pay' => $pct, 'payLabel' => 'Menunggu '.(100 - $pct).'%', 'title' => 'Tagih pelunasan '.(100 - $pct).'%', 'desc' => 'Kirim tagihan sisa lewat email ('.rp($e->remaining()).'). Lewat batas waktu'.($e->settle_deadline ? ' ('.tgl($e->settle_deadline, 'j M H:i').')' : '').', DP hangus.'],
            'lunas' => ['t' => 'Masuk pesanan titip', 'kind' => 'done', 'pay' => 100, 'payLabel' => 'Lunas 100%', 'title' => 'Lunas', 'desc' => 'Pesanan ini lanjut ke tahap Checkout seperti titip biasa.'],
            'kalah' => ['t' => 'Kembalikan DP', 'kind' => 'need', 'pay' => $pct, 'payLabel' => 'DP perlu dikembalikan', 'title' => 'Kembalikan DP', 'desc' => 'Kembalikan '.rp($e->refund_amount).' ('.(($e->drop->refund_mode ?? 'potong') === 'penuh' ? 'penuh' : 'dipotong fee war').') ke '.($e->user?->refund_account ?: 'rekening penitip').'.'],
            'refund' => ['t' => 'Selesai', 'kind' => 'done', 'pay' => 0, 'payLabel' => 'Dikembalikan', 'title' => 'DP sudah dikembalikan', 'desc' => 'Ditransfer '.tgl($e->refunded_at).'. Tidak ada tindakan lagi.'],
            default => match (true) {
                $e->dp_proof && ! $e->dp_verified_at => ['t' => 'Cek DP '.rp($e->dp_amount), 'kind' => 'need', 'pay' => $pct, 'payLabel' => 'DP menunggu verifikasi', 'title' => 'Cek DP '.$pct.'%', 'desc' => 'Cocokkan bukti DP '.rp($e->dp_amount).' dengan mutasi rekening di halaman Pembayaran.'],
                default => null,
            } ?? ($e->dp_proof
                ? ['t' => 'Menunggu war · antrian #'.$e->queue_no, 'kind' => 'wait', 'pay' => $pct, 'payLabel' => 'DP '.$pct.'% masuk', 'title' => 'Menunggu hari war', 'desc' => 'DP '.$pct.'% sudah masuk. Peserta ikut war sesuai nomor antrian. Catat hasilnya di halaman Jasa war.']
                : ['t' => 'Menunggu DP', 'kind' => 'wait', 'pay' => 0, 'payLabel' => $e->dp_reject_reason ? 'DP ditolak · tunggu upload ulang' : 'DP belum dibayar', 'title' => 'Menunggu DP', 'desc' => $e->dp_reject_reason ? 'Bukti DP ditolak: '.$e->dp_reject_reason.'. Penitip diminta upload ulang.' : 'Penitip belum upload bukti DP.']),
        };
    }

    /** Warna titik + teks untuk kind. */
    public static function kindColor(string $kind): array
    {
        return match ($kind) {
            'need' => ['dot' => '#C2311F', 'fg' => '#B02A19'],
            'done' => ['dot' => '#1D7A4C', 'fg' => '#16653F'],
            default => ['dot' => '#D99A1E', 'fg' => '#7A4B00'],
        };
    }

    /** Segmen bar posisi: biru = lewat, merah = sekarang (hijau kalau terakhir), abu = belum. */
    public static function track(int $cur, int $n): array
    {
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $out[] = $i < $cur ? '#1D4E9E' : ($i === $cur ? ($cur === $n - 1 ? '#1D7A4C' : '#C2311F') : '#E6E4DF');
        }

        return $out;
    }

    /**
     * Semua tugas yang perlu tindakan admin, urut dari yang paling lama menunggu.
     * Tiap tugas: task, id, nama, pos, pill, dot, group (pesanan|bayar|war), desc, btn, action (POST, opsional), href.
     */
    public static function tasks(): Collection
    {
        if (self::$tasks !== null) {
            return self::$tasks;
        }

        $out = collect();
        $orders = Order::with(['user', 'batch', 'items'])->where(function ($q) {
            $q->where(fn ($a) => $a->where('stage', '<', Order::DONE)->whereNull('cancelled_at'))
                ->orWhere(fn ($b) => $b->where('refund_amount', '>', 0)->whereNull('refunded_at'));
        })->get();
        foreach ($orders as $o) {
            $s = self::step($o);
            if ($s['kind'] !== 'need') {
                continue;
            }
            $p = self::pos($o);
            $sym = $o->neg()['sym'];
            [$desc, $btn, $action, $group] = match (true) {
                $s['t'] === 'Kirim refund' => ['Refund '.rp($o->refund_amount).' · '.($o->refund_reason ?: 'pesanan').'. Rekening: '.($o->user?->refund_account ?: 'belum diisi').'.', 'Buka Pembayaran', null, 'bayar'],
                $o->stage === Order::OFFERED => ['Bukti transfer tahap 1 untuk '.$o->title().'. Cocokkan nominal '.rp($o->tahap1()).' dengan mutasi rekening.', 'Tandai lunas', route('admin.orders.paid', $o), 'bayar'],
                $o->stage === Order::REQUEST => [$o->title().' · '.$sym.angka($o->items->sum(fn ($i) => ($i->web_price ?? 0) * $i->qty)).'.', 'Buka & kirim penawaran', null, 'pesanan'],
                $o->stage === Order::PAID => [$o->title().' · '.$sym.angka($o->foreignTotal()).'.', 'Buka & kirim bukti checkout', null, 'pesanan'],
                $o->stage === Order::CHECKED_OUT => [$o->wh_submitted_at ? 'Warehouse '.$o->neg()['nama'].' mengirim '.count($o->photoList()).' foto · '.angka($o->weight).' kg.' : 'Upload foto barang, photocard/POB, dan timbangan.', 'Buka & setujui foto', null, 'pesanan'],
                $o->stage === Order::WAREHOUSE => [$o->title().' · '.angka($o->weight).' kg · gudang '.$o->neg()['nama'].'.', 'Pilih batch', null, 'pesanan'],
                $o->stage === Order::ARRIVED => ['Buat produk khusus: ongkir '.$o->billedKg().' kg × '.rp($o->tarif_jual).' + packing '.rp($o->pack_jual).' = '.rp($o->tahap2()).'.', 'Buka & kirim link Shopee', null, 'pesanan'],
                $o->stage === Order::SHOPEE => ['Penitip lapor checkout No. '.$o->shopee_order_no.'. Cek di Seller Centre, pembeli @'.$o->user?->shopee_username.'.', 'Buka & cek', null, 'pesanan'],
                default => [$o->title(), 'Buka detail', null, 'pesanan'],
            };
            $out->push([
                'task' => $s['task'], 'id' => $o->code, 'nama' => $o->user?->displayName(),
                'pos' => $group === 'bayar' ? 'Pembayaran' : self::POS[$p]['t'],
                'pill' => $group === 'bayar' ? pill('amber') : pill(self::POS[$p]['c']),
                'dot' => $group === 'bayar' ? '#D99A1E' : self::POS_COLOR[$p]['dot'],
                'group' => $group, 'desc' => $desc, 'btn' => $btn, 'action' => $action,
                'href' => $s['t'] === 'Kirim refund' ? route('admin.payments', ['tab' => 'refund']) : route('admin.orders', ['id' => $o->id]),
                'at' => $o->updated_at,
            ]);
        }

        $entries = WarEntry::with(['drop', 'user'])->whereNull('order_id')->where(function ($q) {
            $q->where('result', 'menang')
                ->orWhere(fn ($k) => $k->where('result', 'kalah')->whereNull('refunded_at'))
                ->orWhere(fn ($d) => $d->whereNull('result')->whereNotNull('dp_proof')->whereNull('dp_verified_at'));
        })->get();
        foreach ($entries as $e) {
            $s = self::warStep($e);
            if ($s['kind'] !== 'need') {
                continue;
            }
            $isPay = ! $e->result || ($e->result === 'menang' && $e->settle_proof);
            [$desc, $btn, $action, $href] = match (true) {
                ! $e->result => ['DP '.$e->drop->dp_pct.'% war '.$e->drop->name.', antrian #'.$e->queue_no.'.', 'Tandai DP masuk', route('admin.payments.dp', $e), route('admin.payments')],
                $e->result === 'menang' && $e->settle_proof => ['Pelunasan '.(100 - $e->drop->dp_pct).'% war '.$e->drop->name.'.', 'Tandai lunas', route('admin.war.settled', $e), route('admin.payments')],
                $e->result === 'menang' => ['Menang war '.$e->drop->name.'. Sisa '.rp($e->remaining()).'.', 'Buka Jasa war', null, route('admin.war', ['entry' => $e->id]).'#entri-'.$e->id],
                default => ['Kalah war. Refund '.rp($e->refund_amount).' (DP '.rp($e->dp_amount).($e->drop->refund_mode === 'potong' ? ' − fee war' : '').').', 'Buka Pembayaran', null, route('admin.payments', ['tab' => 'refund'])],
            };
            $out->push([
                'task' => $s['t'], 'id' => $e->code(), 'nama' => $e->user?->displayName(),
                'pos' => 'Jasa war', 'pill' => pill('ink'), 'dot' => '#15161C',
                'group' => $isPay ? 'bayar' : 'war', 'desc' => $desc, 'btn' => $btn, 'action' => $action,
                'href' => $href, 'at' => $e->updated_at,
            ]);
        }

        return self::$tasks = $out->sortBy('at')->values();
    }

    /** Jumlah pembayaran yang menunggu verifikasi (badge menu Pembayaran). */
    public static function verifCount(): int
    {
        try {
            return Order::where('stage', Order::OFFERED)->whereNotNull('payment_proof')->whereNull('cancelled_at')->count()
                + WarEntry::whereNull('result')->whereNotNull('dp_proof')->whereNull('dp_verified_at')->count()
                + WarEntry::where('result', 'menang')->whereNotNull('settle_proof')->whereNull('order_id')->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function count(): int
    {
        try {
            return self::tasks()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
