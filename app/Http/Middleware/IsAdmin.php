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
    if (auth()->check() && auth()->user()->isAdminOrTutor()) {
        // Only a Super Admin may switch between schools. School Admins and
        // Tutors are pinned to their own school on every request; the model
        // global scopes read this session value, so this is what keeps one
        // school's data away from another school's staff.
        if (!auth()->user()->isSuperAdmin()) {
            session(['selected_school' => auth()->user()->sid]);
        }
        return $next($request);
    }

    abort(403, 'Unauthorized');
}

}
