<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarEntry extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'kurs_modal' => 'float',
            'kurs_jual' => 'float',
            'qty' => 'integer',
            'fee' => 'integer',
            'total' => 'integer',
            'dp_amount' => 'integer',
            'refund_amount' => 'integer',
            'settle_deadline' => 'datetime',
            'settle_submitted_at' => 'datetime',
            'settled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'dp_verified_at' => 'datetime',
        ];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(WarDrop::class, 'war_drop_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function code(): string
    {
        return 'WAR-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function payUrl(): string
    {
        return route('pay.war', $this);
    }

    /** Ada tagihan yang harus dibayar penitip lewat halaman Bayar? */
    public function needsPayment(): bool
    {
        return (! $this->dp_proof && ! $this->result)
            || ($this->result === 'menang' && ! $this->settle_proof && ! $this->order_id);
    }

    public function remaining(): int
    {
        return max(0, $this->total - $this->dp_amount);
    }

    /** Kartu di halaman Akun saya (sebelum jadi pesanan). */
    public function card(): array
    {
        $d = $this->drop;
        $href = route('war.entry', $this);
        $c = fn ($status, $color, $sub, $action = '', $href = '') => compact('status', 'color', 'sub', 'action', 'href');

        if ($this->result === 'menang') {
            if ($this->settle_proof && ! $this->settled_at) {
                return $c('Menang', 'blue', 'Bukti pelunasan sedang dicek admin');
            }

            if ($this->settle_reject_reason) {
                return $c('Bukti ditolak', 'red', $this->settle_reject_reason, 'Upload ulang bukti', $this->payUrl());
            }

            return $c('Menang', 'red', 'Lunasi sisa '.(100 - $d->dp_pct).'% sebelum '.tgl($this->settle_deadline, 'j M H:i'), 'Bayar sisa '.rp($this->remaining()), $this->payUrl());
        }
        if ($this->result === 'kalah') {
            return $c('Kalah', 'gray', $this->refunded_at ? 'DP dikembalikan '.tgl($this->refunded_at) : 'DP '.rp($this->refund_amount).' akan dikembalikan');
        }

        if (! $this->dp_proof) {
            return $c('DP ditolak', 'red', $this->dp_reject_reason ?: 'Upload ulang bukti DP', 'Upload ulang DP', $this->payUrl());
        }
        if (! $this->dp_verified_at) {
            return $c('Terdaftar', 'blue', 'Antrian #'.$this->queue_no.' · DP sedang dicek admin');
        }

        return $c('Terdaftar', 'blue', 'Antrian #'.$this->queue_no.' · rilis '.tgl($d->release_at, 'j M H:i').' WIB');
    }

    /** Masih aktif (belum kalah dan belum jadi pesanan). */
    public function isActive(): bool
    {
        if ($this->order_id) {
            return false;
        }
        if ($this->result === 'kalah') {
            return ! $this->refunded_at;
        }

        return true;
    }
}
