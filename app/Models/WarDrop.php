<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class WarDrop extends Model
{
    public const STEPS = ['Pendaftaran', 'Hari war', 'Input hasil', 'Pelunasan & refund'];
    public const STEP_STATUS = [['Pendaftaran buka', 'green'], ['Hari war', 'red'], ['Input hasil', 'amber'], ['Pelunasan & refund', 'blue']];
    public const NEXT_LABEL = ['Tutup pendaftaran & mulai war', 'War selesai, input hasil', 'Kirim hasil & tagihan'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'release_at' => 'datetime',
            'results_sent_at' => 'datetime',
            'variants' => 'array',
            'price' => 'float',
            'fee' => 'integer',
            'quota' => 'integer',
            'close_hours' => 'integer',
            'dp_pct' => 'integer',
            'settle_hours' => 'integer',
            'max_per_person' => 'integer',
            'step' => 'integer',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WarEntry::class);
    }

    public function neg(): array
    {
        return negara($this->country);
    }

    /** Waktu tutup pendaftaran (WIB). */
    public function closesAt(): Carbon
    {
        return $this->release_at->copy()->subHours($this->close_hours);
    }

    /** Jam rilis dalam zona lokal (JST/KST = WIB + 2 jam). */
    public function localRelease(): Carbon
    {
        return $this->release_at->copy()->addHours(2);
    }

    public function slotsLeft(): int
    {
        $used = $this->relationLoaded('entries') ? $this->entries->count() : $this->entries()->count();

        return max(0, $this->quota - $used);
    }

    /** Langkah drop: yang ditandai admin, atau otomatis dari jam tutup / jam rilis / hasil terkirim. */
    public function step(): int
    {
        $s = (int) $this->step;
        if ($this->results_sent_at) {
            $s = max($s, 3);
        } elseif (now()->gte($this->release_at)) {
            $s = max($s, 2);
        } elseif (now()->gte($this->closesAt())) {
            $s = max($s, 1);
        }

        return min(3, $s);
    }

    public function isOpen(): bool
    {
        return (int) $this->step === 0 && now()->lt($this->closesAt()) && $this->slotsLeft() > 0;
    }

    public function closingSoon(): bool
    {
        return $this->isOpen() && now()->diffInHours($this->closesAt()) < 24;
    }

    /** [label, warna] status pendaftaran. */
    public function statusPill(): array
    {
        if ($this->isOpen()) {
            return $this->closingSoon() ? ['Segera tutup', 'amber'] : ['Pendaftaran buka', 'green'];
        }

        return ['Pendaftaran tutup', 'gray'];
    }

    public function timeLeftLabel(): string
    {
        $diff = now()->diff($this->closesAt());
        if ($diff->days > 0) {
            return $diff->days.' hari '.$diff->h.' jam lagi';
        }

        return sprintf('%02d:%02d lagi', $diff->h, $diff->i);
    }

    public function variantList(): array
    {
        $v = array_values(array_filter(array_map('trim', (array) $this->variants)));

        return $v ?: ['Reguler'];
    }
}
