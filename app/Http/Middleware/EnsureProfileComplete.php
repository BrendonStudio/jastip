<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Titip & war butuh nama + username Shopee. */
class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();
        if ($u && ! $u->profileComplete()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->route('profile');
        }

        return $next($request);
    }
}
