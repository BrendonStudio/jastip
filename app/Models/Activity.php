<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['message', 'user_id'];

    /** Catat aktivitas. Teks di antara **…** ditebalkan di Ringkasan admin. */
    public static function log(string $message): void
    {
        try {
            $u = auth()->user();
            static::create(['message' => mb_substr($message, 0, 500), 'user_id' => $u?->is_admin ? $u->id : null]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function html(): string
    {
        $safe = e($this->message);
        // Aksi staf ("menandai …") diawali nama pelakunya
        if ($this->user_id && preg_match('/^\p{Ll}/u', $this->message)) {
            $safe = '<strong>'.e($this->user?->displayName() ?? 'Admin').'</strong> '.$safe;
        }

        return preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $safe);
    }
}
