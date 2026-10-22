<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Daniel, 2026-10-21: the student-facing half of the language setting -
 * SetStudentLocale middleware reads what this writes. Blocked when the
 * student's Class is 'locked', mirroring the middleware's own logic
 * exactly so a locked student can't bypass it by posting here directly.
 */
class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $validated = $request->validate(['language' => 'required|in:en,es']);

        $user = Auth::user();
        $class = optional($user->gradeRelation);

        abort_if(($class->language_mode ?? 'free') === 'locked', 403, 'Your class has this set to one language.');

        $user->update(['preferred_language' => $validated['language']]);

        return back();
    }
}
