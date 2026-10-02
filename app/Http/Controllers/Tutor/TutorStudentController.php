<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TutorStudentController extends Controller
{
    /**
     * A Tutor's own student list, grouped by class — only classes this
     * Tutor is actually assigned to (via the class_tutor pivot), never
     * other Tutors' classes. Shows both nickname and real name, since the
     * Tutor already has a legitimate need to know who's who in their own
     * class (approving petitions, granting extensions, etc. per the Admin
     * Roles & Permissions doc) — never shown to students themselves.
     */
    public function index(Request $request)
    {
        $tutor = $request->user();

        abort_unless($tutor->isTutor(), 403);

        $classes = $tutor->tutoredClasses()
            ->with(['level', 'tutors' => function ($q) {
                $q->select('users.id', 'users.name', 'users.nickname');
            }])
            ->get();

        foreach ($classes as $class) {
            $class->setRelation(
                'students',
                \App\Models\User::where('role', 4)
                    ->where('grade', $class->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'nickname', 'citizenId', 'created_at'])
            );
        }

        // Grade level first, then class (classes with no level go last).
        $levels = $classes->sortBy('name')->groupBy(fn ($c) => $c->level->name ?? '')
            ->sortBy(fn ($group, $name) => $name === '' ? 'zzz' : $name);

        return view('tutor.students.index', compact('levels'));
    }
}
