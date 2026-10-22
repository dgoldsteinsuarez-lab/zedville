<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LoginPracticeAttempt;
use App\Models\LoginQuestion;
use App\Models\Month;
use App\Models\SchoolMonthSetting;
use App\Services\ParticipationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Academy "Monthly Questions". Daniel, 2026-10-21: "add this question in
 * academic and only open the new question for each month, but adding the
 * month, for example, in month two, he can decide to answer both month
 * questions, at the end, a mix of all questions."
 *
 * Reads the same Log In questions admins edit (login_questions), so each
 * question exists once. A month's set opens when the school reaches that
 * progression month and stays open; the final review (random questions
 * from every month) opens with the last month that has questions.
 */
class MonthlyQuestionsController extends Controller
{
    private const MIX_SIZE = 20;

    private function levelId(): ?int
    {
        return optional(optional(optional(Auth::user()->gradeRelation)->grade)->level)->id;
    }

    /** The school's current progression month - Month 1 if the school hasn't set its start month. */
    private function reachedMonth(): int
    {
        return SchoolMonthSetting::currentProgressionMonthNumber(Auth::user()->sid) ?? 1;
    }

    private function questions()
    {
        $sid = Auth::user()->sid;

        return LoginQuestion::with(['options', 'month'])
            ->where('grade_level_id', $this->levelId())
            ->where(fn ($q) => $q->whereNull('sid')->orWhere('sid', $sid));
    }

    /** Month number => question count, for the months of this level that have questions. */
    private function monthsWithQuestions(): Collection
    {
        if (!$this->levelId()) {
            return collect();
        }

        return $this->questions()->get()
            ->groupBy(fn (LoginQuestion $q) => (int) str_replace('Month ', '', optional($q->month)->name ?? ''))
            ->filter(fn ($group, $n) => $n > 0)
            ->map->count()
            ->sortKeys();
    }

    private function isOpen(int $month): bool
    {
        return $month <= $this->reachedMonth() && $this->monthsWithQuestions()->has($month);
    }

    private function finalReviewOpensIn(): ?int
    {
        return $this->monthsWithQuestions()->keys()->max();
    }

    private function finalReviewOpen(): bool
    {
        $lastMonth = $this->finalReviewOpensIn();

        return $lastMonth !== null && $this->reachedMonth() >= $lastMonth;
    }

    public function index()
    {
        $months = $this->monthsWithQuestions();
        $reachedMonth = $this->reachedMonth();
        $finalReviewOpensIn = $this->finalReviewOpensIn();
        $finalReviewOpen = $this->finalReviewOpen();
        $mixSize = self::MIX_SIZE;

        $best = LoginPracticeAttempt::where('student_id', Auth::id())
            ->orderByDesc('score')
            ->get()
            ->groupBy(fn ($attempt) => $attempt->month_number ?? 'final')
            ->map->first();

        return view('education.academy.monthly.index', compact(
            'months', 'reachedMonth', 'finalReviewOpensIn', 'finalReviewOpen', 'best', 'mixSize'
        ));
    }

    public function show(int $month)
    {
        abort_unless($this->isOpen($month), 404);

        return view('education.academy.monthly.quiz', [
            'title' => __('academy.month_n', ['n' => $month]),
            'questions' => $this->monthQuestions($month),
            'action' => route('academy.monthly.submit', $month),
            'sendQuestionIds' => false,
        ]);
    }

    public function submit(Request $request, int $month)
    {
        abort_unless($this->isOpen($month), 404);

        return $this->grade($request, $this->monthQuestions($month), $month);
    }

    public function finalReview()
    {
        abort_unless($this->finalReviewOpen(), 404);

        return view('education.academy.monthly.quiz', [
            'title' => __('academy.final_review'),
            'questions' => $this->questions()->inRandomOrder()->limit(self::MIX_SIZE)->get(),
            'action' => route('academy.monthly.final.submit'),
            'sendQuestionIds' => true,
        ]);
    }

    public function submitFinalReview(Request $request)
    {
        abort_unless($this->finalReviewOpen(), 404);

        // The questions that were served (random each time), limited to this
        // student's own level by questions().
        $ids = collect($request->input('question_ids', []))->map(fn ($id) => (int) $id)->unique()->take(self::MIX_SIZE)->values();
        $questions = $this->questions()->whereIn('id', $ids)->get()
            ->sortBy(fn ($q) => $ids->search($q->id))
            ->values();
        abort_if($questions->isEmpty(), 422);

        return $this->grade($request, $questions, null);
    }

    private function monthQuestions(int $month): Collection
    {
        $monthId = Month::where('name', "Month {$month}")->value('id');

        return $this->questions()->where('month_id', $monthId)->orderBy('id')->get();
    }

    private function grade(Request $request, Collection $questions, ?int $month)
    {
        $answers = $request->input('answers', []);

        $rows = $questions->map(function (LoginQuestion $question) use ($answers) {
            $chosen = $question->options->firstWhere('id', (int) ($answers[$question->id] ?? 0));
            $correct = $question->options->firstWhere('is_correct', true);

            return [
                'question' => $question->question,
                'chosen' => optional($chosen)->option_text,
                'correct' => optional($correct)->option_text,
                'is_correct' => $chosen && $correct && $chosen->id === $correct->id,
            ];
        });

        $score = $rows->where('is_correct', true)->count();

        LoginPracticeAttempt::create([
            'student_id' => Auth::id(),
            'month_number' => $month,
            'score' => $score,
            'total' => $rows->count(),
            'completed_at' => now(),
        ]);

        // Same as any other Academy practice: the first completion of each
        // set per month earns an Engagement participation point.
        app(ParticipationService::class)->award(Auth::id(), 'monthly_questions_complete', $month ? "month_{$month}" : 'final_review');

        return view('education.academy.monthly.results', [
            'title' => $month ? __('academy.month_n', ['n' => $month]) : __('academy.final_review'),
            'rows' => $rows,
            'score' => $score,
            'retryUrl' => $month ? route('academy.monthly.show', $month) : route('academy.monthly.final'),
        ]);
    }
}
