<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

/** Peran staf dan menu yang boleh dibuka tiap peran. */
class Akses
{
    public const ROLES = [
        'owner' => 'Pemilik',
        'admin' => 'Admin pesanan',
        'keu' => 'Keuangan',
        'wh' => 'Warehouse',
    ];

    public const ROLE_COLOR = [
        'owner' => ['fg' => '#15161C', 'bg' => '#F0EEE9'],
        'admin' => ['fg' => '#1A3F7F', 'bg' => '#EAF0FA'],
        'keu' => ['fg' => '#16653F', 'bg' => '#E6F4EC'],
        'wh' => ['fg' => '#5B3A9E', 'bg' => '#EDE7F6'],
    ];

    public const ROLE_DESC = [
        'admin' => 'Proses pesanan, pembayaran, war, dan jadwal kirim.',
        'keu' => 'Lihat pembayaran, harga, dan laporan keuangan.',
        'wh' => 'Hanya portal warehouse: tandai paket tiba, upload foto & berat.',
    ];

    /** key => [label, route] — urutan = urutan menu. */
    public const MENUS = [
        'ringkasan' => ['Ringkasan', 'admin.dashboard'],
        'pesanan' => ['Pesanan', 'admin.orders'],
        'pembayaran' => ['Pembayaran', 'admin.payments'],
        'war' => ['Jasa war', 'admin.war'],
        'jadwal' => ['Jadwal kirim', 'admin.batches'],
        'harga' => ['Pengaturan harga', 'admin.pricing'],
        'keuangan' => ['Keuangan', 'admin.finance'],
        'pelanggan' => ['Pelanggan', 'admin.customers'],
        'tim' => ['Tim & akses', 'admin.team'],
        'gudang' => ['Portal warehouse', 'warehouse'],
    ];

    /** Awalan nama route → menu. */
    protected const ROUTES = [
        'admin.dashboard' => 'ringkasan', 'admin.orders' => 'pesanan', 'admin.payments' => 'pembayaran',
        'admin.war' => 'war', 'admin.batches' => 'jadwal', 'admin.pricing' => 'harga', 'admin.finance' => 'keuangan',
        'admin.customers' => 'pelanggan', 'admin.team' => 'tim', 'warehouse' => 'gudang',
    ];

    public const DEFAULT_PERM = [
        'admin' => ['ringkasan', 'pesanan', 'pembayaran', 'war', 'jadwal', 'pelanggan'],
        'keu' => ['ringkasan', 'pembayaran', 'harga', 'keuangan'],
        'wh' => ['gudang'],
    ];

    protected static ?array $perm = null;

    public static function perm(): array
    {
        if (self::$perm !== null) {
            return self::$perm;
        }
        $saved = json_decode((string) Setting::query()->where('key', 'perm')->value('value'), true);
        $p = self::DEFAULT_PERM;
        foreach (array_keys($p) as $r) {
            if (is_array($saved[$r] ?? null)) {
                $p[$r] = array_values(array_intersect(array_keys(self::MENUS), $saved[$r]));
            }
        }

        return self::$perm = $p;
    }

    public static function savePerm(array $p): void
    {
        Setting::put('perm', json_encode($p));
        self::$perm = null;
    }

    public static function can(?User $u, string $menu): bool
    {
        if (! $u || ! $u->is_admin || ! $u->role) {
            return false;
        }
        if ($u->role === 'owner') {
            return $menu !== 'gudang';
        }

        return in_array($menu, self::perm()[$u->role] ?? [], true);
    }

    public static function menuForRoute(?string $name): ?string
    {
        if (! $name) {
            return null;
        }
        foreach (self::ROUTES as $prefix => $menu) {
            if ($name === $prefix || str_starts_with($name, $prefix.'.')) {
                return $menu;
            }
        }

        return null;
    }

    /** Halaman pertama yang boleh dibuka. */
    public static function home(User $u): string
    {
        foreach (self::MENUS as $key => [, $route]) {
            if (self::can($u, $key)) {
                return route($route);
            }
        }

        return route('home');
    }

    public static function roleLabel(User $u): string
    {
        $l = self::ROLES[$u->role] ?? 'Staf';
        if ($u->role === 'wh' && $u->wh_country) {
            $l .= ' · '.negara($u->wh_country)['nama'];
        }

        return $l;
    }
}
