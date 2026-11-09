<?php

namespace App\Http\Middleware;

use App\Support\AdminLevel;
use Closure;
use Illuminate\Http\Request;

/**
 * An activity screen of the Admin works on one level (App\Support\AdminLevel):
 * the one its menu link carries (?level=<id>, remembered), or the one last
 * chosen. ?level=0 shows every level.
 */
class AdminLevelScope
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->query->has('level')) {
            session([AdminLevel::SESSION => (int) $request->query('level') ?: null]);
        }
        AdminLevel::use(session(AdminLevel::SESSION));

        try {
            return $next($request);
        } finally {
            AdminLevel::use(null);
        }
    }
}
