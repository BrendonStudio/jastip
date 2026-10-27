<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Support\Harga;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Akun admin + harga awal. Data contoh: php artisan db:seed --class=DemoSeeder */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@jastip.test');
        $admin = User::firstOrNew(['email' => $email]);
        if (! $admin->exists) {
            $admin->password = env('ADMIN_PASSWORD', 'admin12345');
        }
        $admin->fill([
            'name' => $admin->name ?: 'Admin',
            'is_admin' => true,
            'role' => $admin->role ?: 'owner',
            'shopee_username' => $admin->shopee_username ?: 'admin',
            'email_verified_at' => now(),
        ])->save();

        foreach (Harga::DEFAULTS as $k => $v) {
            if (! Setting::find($k)) {
                Setting::put($k, $v);
            }
        }
    }
}
