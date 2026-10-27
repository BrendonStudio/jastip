<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyCost extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'shopee_pct' => 'float',
            'sewa' => 'integer',
            'iklan' => 'integer',
            'gaji' => 'integer',
            'lain' => 'integer',
        ];
    }

    public static function forMonth(string $month): self
    {
        return static::firstOrNew(['month' => $month], ['shopee_pct' => 0, 'sewa' => 0, 'iklan' => 0, 'gaji' => 0, 'lain' => 0]);
    }

    public function ops(): int
    {
        return (int) ($this->sewa + $this->iklan + $this->gaji + $this->lain);
    }
}
