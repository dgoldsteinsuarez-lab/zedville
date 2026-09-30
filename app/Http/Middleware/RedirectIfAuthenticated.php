<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                // Match the role-aware redirect used at fresh login
                // (AuthenticatedSessionController) — otherwise a Tutor/
                // Admin hitting /login while already authenticated lands
                // on the student dashboard instead of /admin/dashboard.
                if ($user && method_exists($user, 'isAdminOrTutor') && $user->isAdminOrTutor()) {
                    return redirect('/admin/dashboard');
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
