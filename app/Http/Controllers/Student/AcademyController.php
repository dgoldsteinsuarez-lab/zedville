<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CurriculumAttempt;
use App\Models\CurriculumAttemptAnswer;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Services\FinheroPointService;
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
    private function studentGradeLevel()
    {
        $user = Auth::user();

        return optional(optional($user->gradeRelation)->grade)->level;
    }

    private function studentGradeLevelId(): ?int
    {
        return optional($this->studentGradeLevel())->id;
    }

    /**
     * Daniel, 2026-10-21: "for PISA i don't need to divide in Zedville
     * levels, only the PISA levels... independent from if a student is
     * Junior, Rookie or else, they will have the same activities." A
     * category with a null grade_level_id is global (PISA Practice,
     * consolidated into one category regardless of Zedville Level) - every
     * authenticated student can reach it. Every other category is still
     * gated to the student's own Grade Level exactly as before.
     */
    private function canAccessCategory(CurriculumCategory $category): bool
    {
        return $category->grade_level_id === null || $category->grade_level_id === $this->studentGradeLevelId();
    }

    /**
     * Daniel, 2026-10-21: "Pisa should not be there. Pisa should only be in
     * PISA" - the PISA Practice category still exists underneath (its
     * CurriculumItems are now reached via the dedicated Student\PisaController/
     * routes/pisa.php instead), it's just excluded from this generic grid so
     * it isn't offered as a second, redundant entry point.
     */
    public function index()
    {
        $gradeLevel = $this->studentGradeLevel();
        $gradeLevelId = optional($gradeLevel)->id;

        $categories = $gradeLevelId
            ? CurriculumCategory::where('grade_level_id', $gradeLevelId)
                ->where('is_pisa_practice', false)
                ->withCount('items')
                ->orderBy('sort_order')
                ->get()
            : collect();

        // The Log In questions, practised month by month (MonthlyQuestionsController).
        $hasMonthlyQuestions = $gradeLevelId
            && \App\Models\LoginQuestion::where('grade_level_id', $gradeLevelId)->exists();

        return view('education.academy.index', compact('categories', 'gradeLevel', 'hasMonthlyQuestions'));
    }

    public function category(CurriculumCategory $category)
    {
        abort_unless($this->canAccessCategory($category), 403);

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
        abort_unless($this->canAccessCategory($item->category), 403);

        if ($item->type !== 'quiz') {
            return view('education.academy.content', compact('item'));
        }

        // A real exam only gets one completed attempt (bugs.md, 2026-10-21
        // - "evidence of learning" reads oddly if it can be retaken to
        // 100%). Land back on the existing result instead of the quiz form.
        if ($item->is_real_exam) {
            $existingAttempt = CurriculumAttempt::where('item_id', $item->id)
                ->where('student_id', Auth::id())
                ->with('answers.question', 'answers.option')
                ->first();

            if ($existingAttempt) {
                return view('education.academy.results', ['item' => $item, 'attempt' => $existingAttempt]);
            }
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
        abort_unless($this->canAccessCategory($item->category), 403);
        abort_unless($item->type === 'quiz', 404);

        if ($item->is_real_exam) {
            $alreadyCompleted = CurriculumAttempt::where('item_id', $item->id)
                ->where('student_id', Auth::id())
                ->exists();
            abort_if($alreadyCompleted, 403, "You've already completed this exam.");
        }

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

        // A real exam is on the calendar (CurriculumController::
        // syncRealExamCalendar) - taking it finishes that activity.
        if ($item->is_real_exam) {
            app(\App\Services\ActivityCompletionService::class)
                ->complete(Auth::id(), 'exam_' . $item->id, 'exam-' . $item->title);
        }

        // Daniel, 2026-10-21, revising the flat "1 point/month" version:
        // "1 point might be little, as there are many... one point for
        // each category, for example, the question (multiple choice), the
        // graphic and exam, one point each if done it... if he has done
        // more than 4, he already gets one point, and thats it."
        //
        // Per category (not hardcoded to Activities/Graphics/Exams - the
        // same rule applies uniformly to any category, including PISA
        // Practice and the level-specific extras like Insurance/Credit,
        // since there's no principled reason those should behave
        // differently and no stable "this is one of the 3 examples"
        // signal to gate on anyway): once a student has more than 4
        // perfect-scored, *distinct* items completed in that category this
        // month, award 1 FinHero point for it - capped there ("that's
        // it"), not re-awarded for a 5th, 6th, etc. Real exams don't
        // count - they're not voluntary practice, and already have their
        // own locked, one-attempt evidence-of-learning treatment.
        //
        // Reads "done" as "done correctly," carrying over the original
        // request's "if right" - flagging this plainly since this message
        // described a volume threshold without repeating that condition,
        // so it's a real assumption, not a certainty.
        if ($score === $questions->count() && $questions->count() > 0 && !$item->is_real_exam) {
            $categoryKey = 'academy_cat_' . $item->category_id;

            $alreadyAwardedThisMonth = DB::table('finhero_student_points')
                ->where('student_id', Auth::id())
                ->where('source_type', 'activity')
                ->where('source_key', $categoryKey)
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->exists();

            if (!$alreadyAwardedThisMonth) {
                $perfectItemsThisCategoryThisMonth = CurriculumAttempt::query()
                    ->join('curriculum_items', 'curriculum_items.id', '=', 'curriculum_attempts.item_id')
                    ->where('curriculum_attempts.student_id', Auth::id())
                    ->where('curriculum_items.category_id', $item->category_id)
                    ->whereColumn('curriculum_attempts.score', 'curriculum_attempts.total_questions')
                    ->whereYear('curriculum_attempts.completed_at', now()->year)
                    ->whereMonth('curriculum_attempts.completed_at', now()->month)
                    ->distinct()
                    ->count('curriculum_attempts.item_id');

                if ($perfectItemsThisCategoryThisMonth > 4) {
                    app(FinheroPointService::class)->addPoints(Auth::id(), 'activity', $categoryKey, 1);
                }
            }
        }

        $attempt->load('answers.question', 'answers.option');

        return view('education.academy.results', compact('item', 'attempt'));
    }
}
