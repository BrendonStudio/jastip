<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    public const STATUS = ['Mengumpulkan', 'Dikirim', 'Tiba di Indonesia', 'Upload Shopee'];
    public const STATUS_SUB = ['Isi paket', 'Isi no. resi', 'Cek paket', 'Kirim link'];
    public const STATUS_COLOR = ['amber', 'blue', 'teal', 'green'];
    public const NEXT_LABEL = ['Tandai dikirim', 'Tandai tiba di Indonesia', 'Lanjut upload ke Shopee'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cutoff_date' => 'date',
            'ship_date' => 'date',
            'eta_date' => 'date',
            'capacity_kg' => 'float',
            'status' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function label(): string
    {
        return 'Batch '.($this->ship_date ? tgl($this->ship_date) : '#'.$this->id);
    }

    public function routeLabel(): string
    {
        return $this->route === 'laut' ? 'Laut' : 'Udara';
    }

    public function statusPill(): array
    {
        return pill(self::STATUS_COLOR[$this->status] ?? 'gray') + ['label' => self::STATUS[$this->status] ?? '—'];
    }

    public function totalWeight(): float
    {
        return (float) $this->orders->sum('weight');
    }
}
