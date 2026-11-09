<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumAttempt;
use App\Models\CurriculumItem;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-22: "the real exam ... only the tutor and school admin
 * can see the result." Students only see that they handed it in
 * (education/academy/results.blade.php); the scores live here.
 *
 * Who sees what:
 *  - Super Admin: every school, or the school picked in the admin header.
 *  - School Admin: their own school.
 *  - Tutor: only the students in the classes they are assigned to.
 */
class RealExamResultsController extends Controller
{
    public function index(Request $request)
    {
        $exams = $this->visibleExams($request->user())->orderBy('title')->get(['id', 'title']);
        $examId = $request->integer('exam') ?: null;

        $attempts = $this->visibleAttempts($request->user())
            ->when($examId, fn ($q) => $q->where('item_id', $examId))
            ->with(['item:id,title', 'student:id,name,nickname,grade', 'student.gradeRelation:id,name'])
            ->orderByDesc('completed_at')
            ->paginate(50)
            ->withQueryString();

        // The real exams for this viewer's classes, with how many of their
        // students handed each in and a printable copy (Daniel, 2026-10-31:
        // "the tutor should be able to print the exam in case they don't want
        // to do it online").
        $examCards = $this->visibleExams($request->user())
            ->with('category')->withCount('questions')->orderBy('title')->get()
            ->each(function ($exam) use ($request) {
                $exam->handed_in = $this->visibleAttempts($request->user())->where('item_id', $exam->id)->count();
            });

        return view('admin.exam-results.index', compact('exams', 'examId', 'attempts', 'examCards'));
    }

    /**
     * The exam on paper, in the language picked: the questions with their
     * picture and answers to tick - or, with ?key=1, the same with the right
     * answers marked, for whoever corrects it.
     */
    public function print(Request $request, CurriculumItem $item)
    {
        abort_unless($this->visibleExams($request->user())->whereKey($item->id)->exists(), 404);

        $lang = in_array($request->query('lang'), ['en', 'es', 'fr', 'de'], true) ? $request->query('lang') : 'en';
        app()->setLocale($lang);
        $item->load('questions.options');
        $key = $request->boolean('key');

        return view('admin.exam-results.print', compact('item', 'key', 'lang'));
    }

    /**
     * One real exam, class by class: open it for a day with a password, close
     * it, release the marks to the students (Daniel, 2026-10-31).
     */
    public function classes(Request $request, CurriculumItem $item)
    {
        abort_unless($this->visibleExams($request->user())->whereKey($item->id)->exists(), 404);

        $classes = $this->examClasses($request->user(), $item)->get();
        $sessions = ExamSession::where('item_id', $item->id)->whereIn('class_id', $classes->pluck('id'))->get()->keyBy('class_id');
        foreach ($classes as $class) {
            $class->students_count = User::where('role', 4)->where('grade', $class->id)->count();
            $class->handed_in = CurriculumAttempt::where('item_id', $item->id)
                ->whereHas('student', fn ($q) => $q->where('grade', $class->id))->count();
        }

        return view('admin.exam-results.classes', compact('item', 'classes', 'sessions'));
    }

    /** Opens (or moves) the exam for one class: the day and the password. */
    public function open(Request $request, CurriculumItem $item, SchoolClass $class)
    {
        abort_unless($this->examClasses($request->user(), $item)->whereKey($class->id)->exists(), 404);
        $data = $request->validate([
            'exam_date' => ['required', 'date', 'after_or_equal:today'],
            'password' => ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
        ], ['password.regex' => 'The password can only have letters and numbers.']);

        $session = ExamSession::updateOrCreate(['item_id' => $item->id, 'class_id' => $class->id], [
            'exam_date' => $data['exam_date'],
            'password' => strtoupper($data['password']),
            'opened_by' => $request->user()->id,
            'closed_at' => null,
        ]);

        // the class's calendar shows the exam on its day
        $title = 'exam-' . $item->title;
        DB::table('calendar_events')->updateOrInsert(
            ['sid' => $class->sid, 'classId' => $class->id, 'title' => $title],
            ['position' => 'required', 'backgroundColor' => '#dc3545', 'borderColor' => '#dc3545', 'repeatActivity' => '0',
                'description' => $item->description ?? '', 'start' => $session->exam_date->copy()->startOfDay(),
                'end' => $session->exam_date->copy()->addDay()->startOfDay(), 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', $item->title . ' is open for ' . $class->name . ' on ' . $session->exam_date->format('d M Y')
            . ' with the password ' . $session->password . '.');
    }

    public function close(Request $request, ExamSession $session)
    {
        abort_unless($this->examClasses($request->user(), $session->item)->whereKey($session->class_id)->exists(), 404);
        $session->update(['closed_at' => now()]);

        return back()->with('success', 'The exam is closed for ' . ($session->schoolClass->name ?? 'the class') . '.');
    }

    /** The students of the class see their mark (and the right answers) from now on. */
    public function release(Request $request, ExamSession $session)
    {
        abort_unless($this->examClasses($request->user(), $session->item)->whereKey($session->class_id)->exists(), 404);
        $session->update(['results_released_at' => now(), 'closed_at' => $session->closed_at ?? now()]);

        return back()->with('success', 'Results released to ' . ($session->schoolClass->name ?? 'the class') . '.');
    }

    /** The classes this viewer runs the exam for: their own, in the exam's Grade Level. */
    private function examClasses(User $viewer, CurriculumItem $item)
    {
        $level = $item->category->grade_level_id;
        if ($viewer->isSuperAdmin()) {
            $query = SchoolClass::query()->when(session('selected_school'), fn ($q, $sid) => $q->where('sid', $sid));
        } elseif ($viewer->isSchoolAdmin()) {
            $query = SchoolClass::where('sid', $viewer->sid);
        } else {
            $query = SchoolClass::whereIn('id', $viewer->tutoredClasses()->pluck('classes.id'));
        }

        return $query->when($level, fn ($q) => $q->whereHas('grade', fn ($g) => $g->where('grade_level_id', $level)))->orderBy('name');
    }

    /** Real exams for the viewer's classes: their Grade Level's, and the PISA one every class has. */
    private function visibleExams(User $viewer): Builder
    {
        $query = CurriculumItem::where('is_real_exam', true);
        if ($viewer->isSuperAdmin()) {
            return $query;
        }

        $classes = $viewer->isSchoolAdmin()
            ? SchoolClass::where('sid', $viewer->sid)
            : $viewer->tutoredClasses();
        $levels = Grade::whereIn('id', $classes->pluck('grade_id'))->pluck('grade_level_id')->filter()->unique();

        return $query->whereHas('category', fn ($q) => $q->where(fn ($w) => $w->whereNull('grade_level_id')->orWhereIn('grade_level_id', $levels)));
    }

    public function show(Request $request, CurriculumAttempt $attempt)
    {
        abort_unless($this->visibleAttempts($request->user())->whereKey($attempt->id)->exists(), 404);

        $attempt->load('item', 'student.gradeRelation', 'answers.question.options', 'answers.option');

        return view('admin.exam-results.show', compact('attempt'));
    }

    /** Real-exam attempts the signed-in staff member may see. */
    private function visibleAttempts(User $viewer): Builder
    {
        $query = CurriculumAttempt::query()->whereHas('item', fn ($q) => $q->where('is_real_exam', true));

        if ($viewer->isSuperAdmin()) {
            $sid = session('selected_school');

            return $sid ? $query->whereHas('student', fn ($q) => $q->where('sid', $sid)) : $query;
        }

        if ($viewer->isSchoolAdmin()) {
            return $query->whereHas('student', fn ($q) => $q->where('sid', $viewer->sid));
        }

        if ($viewer->isTutor()) {
            $classIds = $viewer->tutoredClasses()->pluck('classes.id');

            return $query->whereHas('student', fn ($q) => $q->whereIn('grade', $classIds));
        }

        return $query->whereRaw('1 = 0');
    }
}
