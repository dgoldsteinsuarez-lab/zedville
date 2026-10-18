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

    // Get answered question IDs
    $answeredIds = LoginQuizAttempt::where('user_id', $userId)
        ->pluck('question_id');

    // Doc: "a monthly set of questions, determined by that month's School
    // Month Settings" - resolve the student's Level (Class -> Grade ->
    // Level) and the school's current progression month (start_month +
    // elapsed calendar months), then only pick from questions that match
    // both. sid is an optional override (a question with sid set only
    // applies to that one school); sid null applies to every school at
    // that Level/month.
    $gradeLevelId = optional(optional(optional($user->gradeRelation)->grade)->level)->id;
    $progressionMonth = SchoolMonthSetting::currentProgressionMonthNumber($user->sid);
    $monthId = $progressionMonth ? Month::where('name', "Month {$progressionMonth}")->value('id') : null;

    $query = LoginQuestion::with('options')->whereNotIn('id', $answeredIds);

    if ($gradeLevelId && $monthId) {
        $question = (clone $query)
            ->where('grade_level_id', $gradeLevelId)
            ->where('month_id', $monthId)
            ->where(fn ($q) => $q->whereNull('sid')->orWhere('sid', $user->sid))
            ->inRandomOrder()
            ->first();
    } else {
        $question = null;
    }

    // Fall back to any unanswered question if nothing matches the
    // Level/month yet (e.g. content hasn't been entered for it) - keeps
    // the feature from going silent rather than leaving students with
    // nothing, until real content exists for every Level/month.
    if (!$question) {
        $question = $query->inRandomOrder()->first();
    }

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

        LoginQuizAttempt::create([
            'user_id'     => Auth::id(),
            'question_id' => $request->question_id,
            'option_id'   => $request->option_id,
            'is_correct'  => $isCorrect,
        ]);

         // ✅ ADD POINT ONLY IF CORRECT
        if ($isCorrect) {
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