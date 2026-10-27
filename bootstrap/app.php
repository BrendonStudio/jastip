<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'profil' => \App\Http\Middleware\EnsureProfileComplete::class,
        ]);

        // Tamu yang membuka halaman admin diarahkan ke login admin,
        // selain itu ke halaman masuk penitip.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));

        $middleware->redirectUsersTo(fn (Request $request) => ($request->user()?->is_admin && $request->user()->role)
            ? \App\Support\Akses::home($request->user())
            : route('account'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
