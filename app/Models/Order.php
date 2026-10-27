<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    // Tahap pesanan (sama dengan urutan di halaman Lacak pesanan)
    public const REQUEST = 0;      // request dikirim penitip
    public const OFFERED = 1;      // penawaran dikirim, menunggu bayar tahap 1
    public const PAID = 2;         // tahap 1 lunas, menunggu checkout
    public const CHECKED_OUT = 3;  // sudah checkout di toko, menunggu tiba di warehouse
    public const WAREHOUSE = 4;    // foto disetujui, menunggu jadwal kirim
    public const SCHEDULED = 5;    // masuk batch kirim
    public const ARRIVED = 6;      // tiba di Indonesia, siapkan Shopee
    public const SHOPEE = 7;       // link Shopee dikirim, menunggu penitip checkout
    public const DONE = 8;         // selesai

    public const ADMIN_STEPS = ['Penawaran', 'Pembayaran', 'Checkout', 'Foto warehouse', 'Jadwal kirim', 'Tiba di Indo', 'Upload Shopee', 'Selesai'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'stage' => 'integer',
            'war_fee' => 'integer',
            'tarif_modal' => 'integer',
            'tarif_jual' => 'integer',
            'pack_modal' => 'integer',
            'pack_jual' => 'integer',
            'store_price' => 'float',
            'domestic_shipping' => 'float',
            'kurs_modal' => 'float',
            'kurs_jual' => 'float',
            'weight' => 'float',
            'offered_at' => 'datetime',
            'offer_expires_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'paid_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'warehouse_approved_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'arrived_at' => 'datetime',
            'shopee_sent_at' => 'datetime',
            'shopee_reported_at' => 'datetime',
            'completed_at' => 'datetime',
            'payment_rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refund_amount' => 'integer',
            'refund_requested_at' => 'datetime',
            'refunded_at' => 'datetime',
            'wh_arrived_at' => 'datetime',
            'wh_submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Order $o) {
            if (! $o->code) {
                $o->code = ($o->type === 'war' ? 'WR' : 'TP').'-'.str_pad((string) $o->id, 4, '0', STR_PAD_LEFT);
                $o->saveQuietly();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function warEntry(): HasOne
    {
        return $this->hasOne(WarEntry::class);
    }

    /* ---------- Tampilan ---------- */

    public function neg(): array
    {
        return negara($this->country);
    }

    public function mainItem(): ?OrderItem
    {
        return $this->items->first();
    }

    public function title(): string
    {
        $i = $this->mainItem();
        if (! $i) {
            return 'Pesanan';
        }
        $extra = $this->items->count() - 1;

        return $i->name.($extra > 0 ? ' +'.$extra.' barang' : '');
    }

    public function totalQty(): int
    {
        return (int) $this->items->sum('qty');
    }

    /** Foto warehouse yang sudah diupload (tanpa slot kosong). */
    public function photoList(): array
    {
        return array_values(array_filter((array) ($this->photos ?? [])));
    }

    public function thumb(): ?string
    {
        $p = $this->photoList()[0] ?? null;
        if ($p && $this->stage >= self::WAREHOUSE) {
            return upload_url($p);
        }
        $s = $this->items->firstWhere('screenshot', '!=', null)?->screenshot;

        return upload_url($s);
    }

    /* ---------- Hitungan ---------- */

    /** Total harga barang dalam ¥/₩ (harga toko + ongkir domestik). */
    public function foreignTotal(): float
    {
        return (float) $this->store_price + (float) $this->domestic_shipping;
    }

    /** Tagihan tahap 1 (dibayar di web). */
    public function tahap1(): int
    {
        if ($this->kurs_jual === null) {
            return 0;
        }

        return (int) round($this->foreignTotal() * $this->kurs_jual) + (int) $this->war_fee;
    }

    public function billedKg(): ?int
    {
        return $this->weight ? kg_bulat($this->weight) : null;
    }

    public function ongkir(): int
    {
        return $this->billedKg() ? $this->billedKg() * (int) $this->tarif_jual : 0;
    }

    /** Tagihan tahap 2 (ongkir + packing, dibayar di Shopee). */
    public function tahap2(): int
    {
        return $this->ongkir() + (int) $this->pack_jual;
    }

    public function profitKurs(): float
    {
        if ($this->kurs_jual === null || $this->stage < self::PAID) {
            return 0;
        }

        return $this->foreignTotal() * ($this->kurs_jual - $this->kurs_modal);
    }

    public function profitBerat(): float
    {
        return $this->billedKg() ? $this->billedKg() * ((int) $this->tarif_jual - (int) $this->tarif_modal) : 0;
    }

    public function profitPacking(): float
    {
        return $this->pack_type ? (int) $this->pack_jual - (int) $this->pack_modal : 0;
    }

    /* ---------- Status ---------- */

    public const WH_CONDITION = ['baik' => 'Baik', 'penyok' => 'Sudut penyok', 'rusak' => 'Rusak'];

    /** Halaman penawaran & pembayaran untuk penitip. */
    public function payUrl(): string
    {
        return route('pay', $this);
    }

    /** Penawaran belum dibayar dan masa kunci kurs sudah lewat. */
    public function offerExpired(): bool
    {
        return $this->stage === self::OFFERED && ! $this->payment_proof && ! $this->cancelled_at
            && $this->offer_expires_at && $this->offer_expires_at->isPast();
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function scopeLive($q)
    {
        return $q->whereNull('cancelled_at');
    }

    public function paymentWaitingConfirm(): bool
    {
        return $this->stage === self::OFFERED && $this->payment_proof && ! $this->paid_at;
    }

    /** Langkah halaman lacak: [title, sub] */
    public function trackSteps(): array
    {
        $batchTgl = $this->batch?->ship_date ? tgl($this->batch->ship_date) : '—';
        $first = $this->type === 'war'
            ? [
                ['Daftar war', 'DP diterima, kamu masuk antrian war'],
                ['Menang war', 'Slot berhasil didapat saat rilis'],
                ['Pelunasan dibayar', 'Sisa pembayaran terverifikasi'],
            ]
            : [
                ['Request & bayar', 'Link produk dan bukti transfer terkirim, kurs dikunci'],
                ['Dicek admin', 'Admin cek stok, harga, dan bukti transfer'],
                ['Pembayaran lunas', 'Pembayaran tahap 1 terverifikasi'],
            ];

        return array_merge($first, [
            ['Checkout berhasil', 'Bukti checkout dari toko sudah dikirim'],
            ['Tiba di warehouse', 'Foto & berat sudah dicek admin'.($this->batch_id ? '. Barang ikut batch kirim '.$batchTgl.'.' : '')],
            ['Dijadwalkan kirim', $this->batch_id ? 'Ikut pengiriman tanggal '.$batchTgl : 'Tanggal kirim muncul setelah masuk jadwal'],
            ['Tiba di Indonesia', 'Pesanan disiapkan di Shopee'],
            ['Bayar di Shopee', $this->shopee_reported_at ? 'Checkout dilaporkan (No. '.$this->shopee_order_no.'), admin sedang cek' : 'Checkout ongkir & packing lewat link, lalu lapor nomor pesanannya'],
            ['Dikirim ke alamatmu', 'Resi muncul di aplikasi Shopee'],
        ]);
    }

    /** Indeks langkah yang sedang berjalan (9 = semua selesai). */
    public function trackCurrent(): int
    {
        return $this->stage >= self::DONE ? 9 : (int) $this->stage;
    }

    /** Teks banner status: [teks, warna] */
    public function banner(): array
    {
        if ($this->cancelled_at) {
            return ['Dibatalkan'.($this->cancel_reason ? ' · '.$this->cancel_reason : ''), 'gray'];
        }
        if ($this->offerExpired()) {
            return ['Penawaran sudah hangus · minta penawaran baru', 'gray'];
        }

        return match ((int) $this->stage) {
            self::REQUEST => ['Request diterima · admin sedang cek stok', 'blue'],
            self::OFFERED => $this->payment_proof
                ? ['Pembayaran terkirim · admin cek stok & bukti transfer', 'blue']
                : ($this->payment_rejected_at ? ['Bukti transfer belum cocok · upload ulang', 'red'] : ['Penawaran siap · setujui & bayar', 'amber']),
            self::PAID => ['Sudah dibayar · menunggu checkout di toko', 'blue'],
            self::CHECKED_OUT => ['Sudah checkout · menunggu tiba di warehouse', 'blue'],
            self::WAREHOUSE => ['Sudah di warehouse · menunggu jadwal kirim', 'blue'],
            self::SCHEDULED => ($this->batch && $this->batch->status >= 1)
                ? ['Dalam perjalanan ke Indonesia', 'blue']
                : ['Dijadwalkan kirim · batch '.tgl($this->batch?->ship_date), 'blue'],
            self::ARRIVED => ['Tiba di Indonesia · pesanan disiapkan di Shopee', 'amber'],
            self::SHOPEE => $this->shopee_reported_at
                ? ['Checkout Shopee dilaporkan · admin sedang cek', 'blue']
                : ['Tiba di Indonesia · bayar ongkir & packing di Shopee', 'amber'],
            default => ['Selesai · terima kasih sudah titip!', 'green'],
        };
    }

    /** Kartu di halaman Akun saya. */
    public function card(): array
    {
        $c = fn ($status, $color, $sub, $action = '', $href = '') => compact('status', 'color', 'sub', 'action', 'href');
        $track = route('track', ['no' => $this->code]);
        if ($this->cancelled_at) {
            return $c('Dibatalkan', 'gray', $this->refund_amount ? 'Refund '.rp($this->refund_amount).($this->refunded_at ? ' sudah ditransfer' : ' diproses') : ($this->cancel_reason ?: 'Penawaran ditolak'));
        }
        if ($this->offerExpired()) {
            return $c('Penawaran hangus', 'gray', 'Kurs hanya dikunci '.config('jastip.kurs_dikunci_jam').' jam', 'Minta penawaran baru', $this->payUrl());
        }

        return match ((int) $this->stage) {
            self::REQUEST => $c('Menunggu penawaran', 'blue', 'Admin sedang cek stok'),
            self::OFFERED => $this->payment_proof
                ? $c('Dicek admin', 'blue', 'Stok & bukti transfer '.rp($this->tahap1()).' sedang dicek')
                : ($this->payment_rejected_at
                    ? $c('Bukti ditolak', 'red', $this->payment_reject_reason ?: 'Bukti transfer perlu dikirim ulang', 'Upload ulang bukti', $this->payUrl())
                    : $c('Penawaran siap', 'amber', $this->offer_expires_at ? 'Kurs dikunci sampai '.tgl($this->offer_expires_at, 'j M H:i') : 'Setujui & bayar '.rp($this->tahap1()), 'Lihat & bayar penawaran', $this->payUrl())),
            self::PAID => $c('Sudah dibayar', 'blue', 'Menunggu checkout di toko'),
            self::CHECKED_OUT => $c('Sudah checkout', 'blue', 'Menunggu tiba di warehouse'),
            self::WAREHOUSE => $c('Di warehouse', 'blue', 'Menunggu jadwal kirim'),
            self::SCHEDULED => $c(($this->batch && $this->batch->status >= 1) ? 'Dalam perjalanan' : 'Dijadwalkan kirim', 'blue', 'Ikut batch '.tgl($this->batch?->ship_date)),
            self::ARRIVED => $c('Tiba di Indonesia', 'amber', 'Pesanan sedang disiapkan di Shopee'),
            self::SHOPEE => $this->shopee_reported_at
                ? $c('Dicek admin', 'blue', 'Checkout Shopee No. '.$this->shopee_order_no.' sedang dicek')
                : $c('Tiba di Indonesia', 'amber', 'Bayar ongkir & packing di Shopee, lalu lapor', 'Bayar & lapor', $track.'#bayar'),
            default => $c('Selesai', 'green', 'Selesai '.tgl($this->completed_at ?? $this->updated_at)),
        };
    }

    /** Label & warna pill di daftar admin. */
    public function adminPill(): array
    {
        if ($this->cancelled_at) {
            return ['Dibatalkan', 'gray'];
        }

        return match ((int) $this->stage) {
            self::REQUEST => ['Kirim penawaran', 'amber'],
            self::OFFERED => $this->payment_proof ? ['Konfirmasi pembayaran', 'red'] : ['Menunggu bayar', 'gray'],
            self::PAID => ['Checkout di toko', 'green'],
            self::CHECKED_OUT => ['Foto warehouse', 'blue'],
            self::WAREHOUSE => ['Jadwal kirim', 'blue'],
            self::SCHEDULED => ['Dalam pengiriman', 'blue'],
            self::ARRIVED => ['Upload Shopee', 'amber'],
            self::SHOPEE => $this->shopee_reported_at ? ['Lapor checkout Shopee', 'red'] : ['Menunggu checkout Shopee', 'gray'],
            default => ['Selesai', 'green'],
        };
    }

    /** Status untuk tabel keuangan. */
    public function financeStatus(): array
    {
        return match (true) {
            $this->stage >= self::DONE => ['Selesai', 'green'],
            $this->stage >= self::ARRIVED => ['Menunggu Shopee', 'amber'],
            $this->stage >= self::CHECKED_OUT => ['Dalam pengiriman', 'blue'],
            default => ['Diproses', 'gray'],
        };
    }

    /** Hari sejak link Shopee dikirim tanpa laporan checkout. */
    public function shopeeWaitDays(): ?int
    {
        if ($this->stage !== self::SHOPEE || $this->shopee_reported_at || ! $this->shopee_sent_at) {
            return null;
        }

        return (int) $this->shopee_sent_at->diffInDays(now());
    }
}
