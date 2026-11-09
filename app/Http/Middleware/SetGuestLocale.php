<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * The log-in page in the visitor's language (Daniel, 2026-10-25: "todo tiene
 * que estar en sus respectivos idiomas"). Before logging in we don't know the
 * student's class yet, so it's the language they picked on the page (?lang=,
 * remembered for the visit) or else their browser's. After logging in,
 * SetStudentLocale takes over.
 */
class SetGuestLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            $languages = array_keys(config('zedville.languages'));

            if (in_array($request->query('lang'), $languages, true)) {
                $request->session()->put('guest_locale', $request->query('lang'));
            }

            App::setLocale($request->session()->get('guest_locale') ?? $request->getPreferredLanguage($languages) ?? config('app.locale'));
        }

        return $next($request);
    }
}
