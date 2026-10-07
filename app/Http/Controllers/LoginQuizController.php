<?php

namespace App\Http\Controllers;

use App\Services\FinheroPointService;
use Illuminate\Http\Request;
use App\Models\LoginQuestion;
use App\Models\LoginOption;
use App\Models\LoginQuizAttempt;
use Illuminate\Support\Facades\Auth;

class LoginQuizController extends Controller
{
    public function getTodayQuestion()
{
    $userId = Auth::id();

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

    // Get ONE random question with its options
    $question = LoginQuestion::with('options')
        ->whereNotIn('id', $answeredIds)
        ->inRandomOrder()
        ->first();

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