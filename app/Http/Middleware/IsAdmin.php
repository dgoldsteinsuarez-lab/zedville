<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, \Closure $next)
{
    // Legacy Admin (0), Super Admin (1), School Admin (2), Tutor (3) — matches the
    // role set AuthenticatedSessionController already redirects to /admin/dashboard.
    if (auth()->check() && in_array(auth()->user()->role, [0, 1, 2, 3, '0', '1', '2', '3', 'admin', 'tutor'])) {
        return $next($request);
    }

    abort(403, 'Unauthorized');
}

}
