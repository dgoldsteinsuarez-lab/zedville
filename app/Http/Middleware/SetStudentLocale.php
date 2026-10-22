<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * Daniel, 2026-10-21: "I want that the school admin, tutor, can decide if
 * the student can switch languages... the tutor might decide to only have
 * this in that specific language for beginners, to force the students to
 * understand it." Resolves the active locale per request, scoped by the
 * student's own Class:
 *   - 'locked' Class -> always Class.default_language, no override.
 *   - 'free' Class (the default for every pre-existing Class) ->
 *     the student's own saved preferred_language, falling back to the
 *     Class's default_language, falling back to config('app.locale').
 *
 * Only applies to students (role 4) - Admin/Tutor screens aren't
 * translated in this pass, so forcing a locale on those roles would do
 * nothing but silently change which (currently nonexistent) translation
 * strings get looked up.
 */
class SetStudentLocale
{
    private const SUPPORTED = ['en', 'es'];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && (int) $user->role === 4) {
            $class = optional($user->gradeRelation);

            $locale = ($class->language_mode ?? 'free') === 'locked'
                ? $class->default_language
                : ($user->preferred_language ?? $class->default_language ?? config('app.locale'));

            if (in_array($locale, self::SUPPORTED, true)) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
