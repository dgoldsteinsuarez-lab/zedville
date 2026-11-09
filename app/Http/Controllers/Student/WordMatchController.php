<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EasyActivity;
use App\Services\EasyActivityService;
use Illuminate\Http\Request;

/**
 * "Match the words" (Hybrid Classes > Language Activities, 2026-10-29): the
 * words in the language the class learns, big, and their English meanings;
 * the student taps a word, then its meaning. All matched while the activity
 * runs for the class = done, for the Engagement badge. Outside its dates it
 * is practice and doesn't count.
 */
class WordMatchController extends Controller
{
    public function show(EasyActivity $activity, EasyActivityService $easy)
    {
        abort_unless($activity->type === 'word_match', 404);
        $user = auth()->user();
        $running = EasyActivity::runningFor($user->grade, 'word_match')->firstWhere('id', $activity->id);
        $pairs = $activity->pairs();

        return view('engagement.word-match', [
            'activity' => $running ?? $activity,
            'running' => (bool) $running,
            'done' => $running ? $easy->isDone((int) $user->id, $running) : false,
            'pairs' => $pairs,
            // the meanings mixed, in an order that stays the same for this student
            'meanings' => collect($pairs)->pluck(1)->sortBy(fn ($meaning) => crc32($meaning . '|' . $user->id))->values()->all(),
        ]);
    }

    public function done(Request $request, EasyActivity $activity, EasyActivityService $easy)
    {
        abort_unless($activity->type === 'word_match', 404);
        $data = $request->validate(['answers' => ['required', 'array'], 'answers.*' => ['nullable', 'string', 'max:60']]);

        return response()->json($easy->wordsMatched($request->user(), $activity, $data['answers']));
    }
}
