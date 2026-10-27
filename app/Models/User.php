<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'shopee_username', 'whatsapp',
        'favorite_group', 'refund_account', 'admin_note', 'is_flagged', 'email_verified_at',
        'role', 'wh_country', 'invite_token', 'invited_at', 'last_seen_at', 'notify_war',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_flagged' => 'boolean',
            'notify_war' => 'boolean',
            'invited_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function warEntries(): HasMany
    {
        return $this->hasMany(WarEntry::class);
    }

    /** Staf yang menerima email kabar pesanan / pembayaran baru. */
    public static function notifyStaff()
    {
        return static::where('is_admin', true)->whereIn('role', ['owner', 'admin'])->whereNotNull('password')->get();
    }

    public function profileComplete(): bool
    {
        return filled($this->name) && filled($this->shopee_username);
    }

    public function displayName(): string
    {
        return $this->name ?: strtok($this->email, '@');
    }

    /** Refund (DP war kalah + refund pesanan) yang belum ditransfer. */
    public function refundBalance(): int
    {
        return (int) $this->warEntries()->where('result', 'kalah')->whereNull('refunded_at')->sum('refund_amount')
            + (int) $this->orders()->where('refund_amount', '>', 0)->whereNull('refunded_at')->sum('refund_amount');
    }
}
