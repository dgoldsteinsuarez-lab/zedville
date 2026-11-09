<?php

namespace App\Http\Middleware;

use App\Support\BeginnerTranslationLoader;
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
 * Daniel, 2026-10-22: every language also has a beginner version (A2/B1),
 * set per class by the teacher (classes.language_level) - students can't
 * change it. Beginner texts live in lang/<code>_easy/ and only need the
 * texts written more simply; the rest comes from the standard version
 * (BeginnerTranslationLoader).
 *
 * Daniel, 2026-10-29: a third level, "hybrid", for students who speak
 * little of the class language: it is mixed with English text by text
 * (App\Support\Hybrid), on the beginner wording.
 *
 * Only applies to students (role 4) - Admin/Tutor screens aren't
 * translated.
 */
class SetStudentLocale
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $beginnerTexts = false;
        $hybrid = false;

        if ($user && (int) $user->role === 4) {
            $class = optional($user->gradeRelation);

            $language = ($class->language_mode ?? 'free') === 'locked'
                ? $class->default_language
                : ($user->preferred_language ?? $class->default_language ?? config('app.locale'));

            if (array_key_exists($language, config('zedville.languages'))) {
                App::setLocale($language);
            }

            $level = $class->language_level ?? 'standard';
            $beginnerTexts = in_array($level, ['beginner', 'hybrid'], true);
            $hybrid = $level === 'hybrid';
        }

        \App\Support\Hybrid::enable($hybrid);

        $translator = app('translator');
        $loader = $translator->getLoader();
        if ($loader instanceof BeginnerTranslationLoader && $loader->useBeginnerTexts($beginnerTexts)) {
            $translator->setLoaded([]);
        }

        return $next($request);
    }
}
