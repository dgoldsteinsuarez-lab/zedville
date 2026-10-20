<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CurriculumAttempt;
use App\Models\CurriculumAttemptAnswer;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Services\ParticipationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Academy" - a student's Grade Level curriculum (Activities, Exams,
 * Graphics, + level-specific topics) and PISA Practice. New, separate from
 * the existing Educational Finance Department (bugs.md, 2026-10-20 -
 * PISA/Grade Level curriculum).
 */
class AcademyController extends Controller
{
    private function studentGradeLevelId(): ?int
    {
        $user = Auth::user();

        return optional(optional(optional($user->gradeRelation)->grade)->level)->id;
    }

    public function index()
    {
        $gradeLevelId = $this->studentGradeLevelId();

        $categories = $gradeLevelId
            ? CurriculumCategory::where('grade_level_id', $gradeLevelId)
                ->withCount('items')
                ->orderBy('sort_order')
                ->get()
            : collect();

        return view('education.academy.index', compact('categories', 'gradeLevelId'));
    }

    public function category(CurriculumCategory $category)
    {
        abort_unless($category->grade_level_id === $this->studentGradeLevelId(), 403);

        $studentId = Auth::id();
        $items = $category->items()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (CurriculumItem $item) use ($studentId) {
                $item->best_score = CurriculumAttempt::where('item_id', $item->id)
                    ->where('student_id', $studentId)
                    ->orderByDesc('score')
                    ->first();

                return $item;
            });

        return view('education.academy.category', compact('category', 'items'));
    }

    public function item(CurriculumItem $item)
    {
        abort_unless($item->category->grade_level_id === $this->studentGradeLevelId(), 403);

        if ($item->type !== 'quiz') {
            return view('education.academy.content', compact('item'));
        }

        $questions = $item->questions()->with('options')->get()->map(function ($question) {
            // Never expose is_correct to the client - grading happens
            // server-side only, on submit() below.
            $question->options->makeHidden('is_correct');

            return $question;
        });

        return view('education.academy.item', compact('item', 'questions'));
    }

    public function submit(Request $request, CurriculumItem $item)
    {
        abort_unless($item->category->grade_level_id === $this->studentGradeLevelId(), 403);
        abort_unless($item->type === 'quiz', 404);

        $questions = $item->questions()->with('options')->get();
        $answers = $request->input('answers', []);

        $score = 0;
        $rows = [];

        foreach ($questions as $question) {
            $optionId = $answers[$question->id] ?? null;
            $selected = $optionId ? $question->options->firstWhere('id', (int) $optionId) : null;
            $isCorrect = (bool) ($selected->is_correct ?? false);

            if ($isCorrect) {
                $score++;
            }

            $rows[] = [
                'question_id' => $question->id,
                'option_id' => $selected->id ?? null,
                'is_correct' => $isCorrect,
            ];
        }

        $attempt = DB::transaction(function () use ($item, $rows, $score, $questions) {
            $attempt = CurriculumAttempt::create([
                'item_id' => $item->id,
                'student_id' => Auth::id(),
                'score' => $score,
                'total_questions' => $questions->count(),
                'completed_at' => now(),
            ]);

            foreach ($rows as $row) {
                CurriculumAttemptAnswer::create($row + ['attempt_id' => $attempt->id]);
            }

            return $attempt;
        });

        // Retakes are unlimited for practice - only the first completion
        // per item per month earns the participation point (dedup inside
        // award()/alreadyEarned(), mirroring the street_sign_press case).
        app(ParticipationService::class)->award(Auth::id(), 'curriculum_item_complete', (string) $item->id);

        $attempt->load('answers.question', 'answers.option');

        return view('education.academy.results', compact('item', 'attempt'));
    }

    public function pisaShortcut()
    {
        $gradeLevelId = $this->studentGradeLevelId();

        $category = $gradeLevelId
            ? CurriculumCategory::where('grade_level_id', $gradeLevelId)->where('is_pisa_practice', true)->first()
            : null;

        if (!$category) {
            return view('education.academy.pisa-empty');
        }

        return redirect()->route('academy.category', $category);
    }
}
