<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A Tutor changing the language of one of their own Classes (Daniel,
 * 2026-10-22: the language is chosen per Grade by an admin, and "my
 * decision can be changed by the tutor"). Only Classes the Tutor is
 * assigned to (class_tutor pivot).
 */
class ClassLanguageController extends Controller
{
    public function update(Request $request, int $classId)
    {
        $tutor = $request->user();
        abort_unless($tutor->isTutor(), 403);

        $class = $tutor->tutoredClasses()->where('classes.id', $classId)->firstOrFail();

        $request->validate([
            'default_language' => ['required', Rule::in(array_keys(config('zedville.languages')))],
            'language_mode' => 'nullable|in:free,locked',
            'language_level' => 'required|in:standard,beginner,hybrid',
        ]);

        $class->update([
            'language_mode' => $request->input('language_mode', 'free'),
            'default_language' => $request->input('default_language'),
            'language_level' => $request->input('language_level'),
        ]);

        return redirect(route('admin.tutor.my-students') . '#class-' . $class->id)
            ->with('success', 'Language saved for ' . $class->name . '.');
    }
}
