<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');

        // Zona waktu & bahasa untuk Indonesia (WIB).
        $tz = env('APP_TIMEZONE', 'Asia/Jakarta');
        config(['app.timezone' => $tz]);
        date_default_timezone_set($tz);
    }

    public function boot(): void
    {
        // Disk upload langsung di public/uploads supaya tidak perlu storage:link (aman di Windows).
        config(['filesystems.disks.uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => rtrim(config('app.url'), '/').'/uploads',
            'visibility' => 'public',
            'throw' => false,
        ]]);

        \Carbon\Carbon::setLocale('id');
        Paginator::defaultView('pagination::simple-default');
    }
}
