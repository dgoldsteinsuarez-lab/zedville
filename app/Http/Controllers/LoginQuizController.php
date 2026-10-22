<?php

namespace App\Http\Controllers;

use App\Services\FinheroPointService;
use Illuminate\Http\Request;
use App\Models\LoginQuestion;
use App\Models\LoginOption;
use App\Models\LoginQuizAttempt;
use App\Models\Month;
use App\Models\SchoolMonthSetting;
use Illuminate\Support\Facades\Auth;

class LoginQuizController extends Controller
{
    public function getTodayQuestion()
{
    $user = Auth::user();
    $userId = $user->id;

    // Check if already answered today
    $alreadyAnswered = LoginQuizAttempt::where('user_id', $userId)
        ->whereDate('created_at', now()->toDateString())
        ->exists();

    if ($alreadyAnswered) {
        return response()->json(['show' => false]);
    }

    $question = $this->pickQuestion($user);

    if (!$question) {
        return response()->json(['show' => false]);
    }

    $options = $question->options;

    return response()->json([
        'show'     => true,
        'id'       => $question->id,
        'question' => $question->question,
        'type'     => $question->type,
        'options'  => $options->map(function ($opt) {
            return [
                'id'   => $opt->id,
                'text' => $opt->option_text,
            ];
        }),
    ]);
}

    /**
     * Doc: "a monthly set of questions, determined by that month's School
     * Month Settings" - the student's Level (Class -> Grade -> Level) and
     * the school's current progression month (start_month + elapsed
     * calendar months). sid null applies to every school; a question with
     * sid set only applies to that school.
     *
     * Daniel, 2026-10-21: questions should match the activity students are
     * doing that month. A month has 10 questions but more school days, so
     * once they're used up this stays on the student's own Level and on
     * months they've already reached - catch-up on earlier months first,
     * then re-ask ones they got wrong, then review - instead of jumping to
     * future months' topics.
     */
    private function pickQuestion($user): ?LoginQuestion
    {
        $answeredIds = LoginQuizAttempt::where('user_id', $user->id)->pluck('question_id');

        $gradeLevelId = optional(optional(optional($user->gradeRelation)->grade)->level)->id;
        $progressionMonth = SchoolMonthSetting::currentProgressionMonthNumber($user->sid);

        if ($gradeLevelId && $progressionMonth) {
            $reachedMonthIds = Month::whereIn('name', array_map(fn ($n) => "Month {$n}", range(1, $progressionMonth)))
                ->pluck('id', 'name');
            $currentMonthId = $reachedMonthIds["Month {$progressionMonth}"] ?? null;

            $forLevel = fn () => LoginQuestion::with('options')
                ->where('grade_level_id', $gradeLevelId)
                ->where(fn ($q) => $q->whereNull('sid')->orWhere('sid', $user->sid));

            $correctIds = LoginQuizAttempt::where('user_id', $user->id)->where('is_correct', true)->pluck('question_id');
            $neverRightIds = LoginQuizAttempt::where('user_id', $user->id)->where('is_correct', false)
                ->pluck('question_id')->diff($correctIds);

            $question = $forLevel()->where('month_id', $currentMonthId)->whereNotIn('id', $answeredIds)->inRandomOrder()->first()
                ?? $forLevel()->whereIn('month_id', $reachedMonthIds)->whereNotIn('id', $answeredIds)->inRandomOrder()->first()
                ?? $forLevel()->whereIn('month_id', $reachedMonthIds)->whereIn('id', $neverRightIds)->inRandomOrder()->first()
                ?? $forLevel()->where('month_id', $currentMonthId)->inRandomOrder()->first()
                ?? $forLevel()->whereIn('month_id', $reachedMonthIds)->inRandomOrder()->first();

            if ($question) {
                return $question;
            }
        }

        // Nothing written for this Level yet - any unanswered question, so
        // the feature doesn't go silent until every Level has content.
        return LoginQuestion::with('options')->whereNotIn('id', $answeredIds)->inRandomOrder()->first();
    }

    public function submit(Request $request)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'option_id'   => 'required|integer',
        ]);

        $question = LoginQuestion::with('options')
            ->findOrFail($request->question_id);

        $selected = $question->options
            ->firstWhere('id', $request->option_id);

        if (!$selected) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid option.'
            ], 422);
        }

        $isCorrect = (bool) $selected->is_correct;

        // FinHero Point Values doc: "1 pt per correct first attempt" - a
        // question re-asked for review doesn't earn the point again.
        $firstAttempt = !LoginQuizAttempt::where('user_id', Auth::id())
            ->where('question_id', $request->question_id)
            ->exists();

        LoginQuizAttempt::create([
            'user_id'     => Auth::id(),
            'question_id' => $request->question_id,
            'option_id'   => $request->option_id,
            'is_correct'  => $isCorrect,
        ]);

        if ($isCorrect && $firstAttempt) {
            app(FinheroPointService::class)->addPoints(auth()->id(), 'quiz', 'daily_quiz', 1);
        }

        // Get correct option text to show user
        $correctOption = $question->options->firstWhere('is_correct', true);

        return response()->json([
            'success'      => true,
            'is_correct'   => $isCorrect,
            'correct_text' => $correctOption ? $correctOption->option_text : '',
        ]);
    }
}