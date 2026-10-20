<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Daniel, 2026-10-20: "if i am in tutor mode [viewing as another user], i
 * should not be able to change something." Pairs with the audit trail in
 * ImpersonateController/impersonation_logs - together they match how real
 * platforms gate "view as" support tooling (read-only + logged), since a
 * Super Admin already has full DB access regardless; this is about not
 * casually mutating another user's data while looking at their account,
 * not about restricting what can technically be seen.
 *
 * Blocks every write request (anything but GET/HEAD) while
 * session('impersonator_id') is set - the one thing that must always
 * still work is returning to your own account. Registered in the `web`
 * middleware group (Kernel.php) so it covers both the admin area and the
 * student-facing site, since impersonating a Student leaves the admin
 * area entirely.
 */
class BlockWritesWhileImpersonating
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('impersonator_id') || $request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        // impersonate.stop must always work (the escape hatch). admin
        // .impersonate.start is also let through here on purpose: it has
        // its own, more specific "already impersonating" guard
        // (ImpersonateController::start()) that gives a clearer error than
        // this generic one would - letting it through doesn't weaken
        // anything, that guard still rejects it.
        if ($request->routeIs('impersonate.stop', 'admin.impersonate.start', 'logout')) {
            return $next($request);
        }

        $message = 'You are viewing as another user in read-only mode - return to your own account to make changes.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return back()->with('error', $message);
    }
}
