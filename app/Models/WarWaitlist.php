<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarWaitlist extends Model
{
    protected $table = 'war_waitlist';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['notified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(WarDrop::class, 'war_drop_id');
    }

    /** Kabari daftar tunggu kalau slot drop ini terbuka lagi. */
    public static function notifyOpen(WarDrop $drop): int
    {
        if (! $drop->isOpen()) {
            return 0;
        }
        $n = 0;
        foreach (static::with('user')->where('war_drop_id', $drop->id)->whereNull('notified_at')->get() as $w) {
            \App\Support\Notify::send($w->user, 'Slot war '.$drop->name.' terbuka lagi', [
                'Ada '.$drop->slotsLeft().' slot kosong. Daftar sekarang sebelum penuh lagi.',
            ], 'Daftar war', route('war.register', $drop), null, ['tag' => 'DAFTAR TUNGGU', 'c' => 'red']);
            $w->update(['notified_at' => now()]);
            $n++;
        }

        return $n;
    }
}
