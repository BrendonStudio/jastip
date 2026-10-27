<?php

namespace App\Http\Middleware;

use App\Support\Akses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Halaman staf: harus staf, dan perannya boleh membuka menu ini (lihat Tim & akses). */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();
        if (! $u) {
            return redirect()->route('admin.login');
        }
        if (! $u->is_admin || ! $u->role) {
            return redirect()->route('home')->with('flash', 'Halaman itu khusus tim admin.');
        }
        if (! $u->last_seen_at || $u->last_seen_at->lt(now()->subMinutes(5))) {
            $u->forceFill(['last_seen_at' => now()])->saveQuietly();
        }
        $menu = Akses::menuForRoute($request->route()?->getName());
        if ($menu && ! Akses::can($u, $menu)) {
            $home = Akses::home($u);
            if ($request->isMethod('get') && $home !== $request->url()) {
                return redirect($home)->with('error', 'Peranmu tidak punya akses ke menu '.Akses::MENUS[$menu][0].'.');
            }
            abort(403);
        }

        return $next($request);
    }
}
