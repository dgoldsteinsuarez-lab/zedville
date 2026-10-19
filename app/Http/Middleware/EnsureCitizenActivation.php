<?php

namespace App\Http\Middleware;

use App\Services\CitizenActivationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CitizenActivationService::getNextRoute() was only ever checked once, at
 * login (AuthenticatedSessionController::store) - nothing stopped a student
 * from then clicking any sidebar link (City Mailbox, City, EFD, PISA,
 * Calendar, ...) or typing a URL to skip straight past the onboarding
 * wizard (Bank Account / Salary Authorization / Auto Debit / Consumer
 * Profile) before finishing it. Re-checked here on every request instead.
 */
class EnsureCitizenActivation
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || (int) $user->role !== 4) {
            return $next($request);
        }

        // The wizard itself, and logging out, must stay reachable.
        if ($request->routeIs('CitizenActivation.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Only gate full page loads (what sidebar navigation actually does).
        // Widgets baked into the shared layout - the mood meter, cart,
        // notifications - also render on the wizard pages themselves and
        // fire their own AJAX POSTs there; redirecting those breaks them,
        // since the frontend expects JSON back, not a redirect to an HTML
        // page (see bugs.md, 2026-10-19 - "Server error, please try again"
        // on the mood meter while mid-onboarding).
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        $nextRoute = app(CitizenActivationService::class)->getNextRoute($user);

        if ($nextRoute) {
            return redirect($nextRoute);
        }

        return $next($request);
    }
}
