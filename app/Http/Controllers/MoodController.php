<?php

namespace App\Http\Controllers;

use App\Models\MoodLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Daily mood logging and the "City Mood" summary page. Extracted out of
 * ProfileController (planning.md Phase 3 #4). getMoodData() is public
 * (was a private ProfileController method) because edit() still calls
 * it via app(MoodController::class)->getMoodData() to show the "today's
 * mood" chip on the main profile page. city_mood() and the old
 * ProfileController::getMoodData() were byte-for-byte identical except
 * for the final return (a view vs. the raw array) - consolidated into
 * one method instead of keeping both.
 */
class MoodController extends Controller
{
    public function saveMode(Request $request)
    {
        $request->validate([
            'mood' => 'required|string|max:255'
        ]);
        $alreadyLogged = MoodLog::where('user_id', auth()->id())
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyLogged) {
            return response()->json(['message' => 'Mood already submitted today']);
        }
        $data = $this->getMoodCoordinates($request->mood);
        // Save mood in DB (assuming user is logged in)
        $moodlog = MoodLog::create([
            'user_id' => auth()->id(),
            'sid' => auth()->user()->sid,
            'mood' => $request->mood,
            'energy' => $data['energy'],
            'pleasantness' => $data['pleasantness'],
            'created_at' => now()
        ]);

        return response()->json(['message' => 'Mood saved successfully']);
    }

    public function getMoodCoordinates($mood)
    {
        $map = [
            'furious' => ['energy' => 9, 'pleasantness' => 2],
            'nervous' => ['energy' => 9, 'pleasantness' => 4],
            'worried' => ['energy' => 8, 'pleasantness' => 3],
            'angry' => ['energy' => 8, 'pleasantness' => 2],
            'lonely' => ['energy' => 3, 'pleasantness' => 2],
            'sad' => ['energy' => 3, 'pleasantness' => 3],
            'hopeless' => ['energy' => 2, 'pleasantness' => 1],
            'disappointed' => ['energy' => 2, 'pleasantness' => 2],
            'at_ease' => ['energy' => 4, 'pleasantness' => 8],
            'content' => ['energy' => 3, 'pleasantness' => 8],
            'ecstatic' => ['energy' => 9, 'pleasantness' => 9],
            'excited' => ['energy' => 9, 'pleasantness' => 8],
            'happy' => ['energy' => 8, 'pleasantness' => 9],
            'serene' => ['energy' => 7, 'pleasantness' => 8],
            'calm' => ['energy' => 6, 'pleasantness' => 9],
        ];

        return $map[strtolower($mood)] ?? ['energy' => 5, 'pleasantness' => 5];
    }

    public function city_mood(): View
    {
        return view('profile.city-mood', $this->getMoodData());
    }

    public function getMoodData()
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();   // e.g. "2026-03-30"

        /* ══════════════════════════════════════════════════════════
           1. MY LOGS  — all rows for the logged-in user,
              flattened to one row per calendar day
              (loginCount = how many times they logged that day)
        ══════════════════════════════════════════════════════════ */
        $myLogs = DB::table('mood_logs')
            ->selectRaw('
            DATE(created_at)        AS date,
            mood,
            AVG(energy)             AS energy,
            AVG(pleasantness)       AS pleasantness,
            COUNT(*)                AS loginCount
        ')
            ->where('user_id', $userId)
            ->groupByRaw('DATE(created_at), mood')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn($r) => [
                'date' => $r->date,
                'mood' => $r->mood,
                'energy' => round($r->energy, 1),
                'pleasantness' => round($r->pleasantness, 1),
                'loginCount' => (int) $r->loginCount,
            ])
            ->values()
            ->toArray();

        /* ══════════════════════════════════════════════════════════
           2. TODAY'S LOG  — the most recent entry for the current
              user today (used in "Today" summary chip)
        ══════════════════════════════════════════════════════════ */
        $todayRaw = DB::table('mood_logs')
            ->selectRaw('mood, AVG(energy) AS energy, AVG(pleasantness) AS pleasantness, COUNT(*) AS loginCount')
            ->where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->groupBy('mood')
            ->orderByRaw('MAX(created_at) DESC')
            ->first();

        $todayMyLog = $todayRaw ? [
            'mood' => $todayRaw->mood,
            'energy' => round($todayRaw->energy, 1),
            'pleasantness' => round($todayRaw->pleasantness, 1),
            'loginCount' => (int) $todayRaw->loginCount,
        ] : null;

        /* ══════════════════════════════════════════════════════════
           3. CITY MONTHS  — one row per calendar month across ALL
              users; used in the "City Mood" tab.

              - avgEnergy / avgPleasantness = mean over every log row
              - daysLogged = distinct calendar days that had at least
                one log entry
              - totalLogs  = raw row count
        ══════════════════════════════════════════════════════════ */
        $user = Auth::user();
        $cityMonths = DB::table('mood_logs')
            ->join('users', 'users.id', '=', 'mood_logs.user_id')
            ->selectRaw("
                YEAR(mood_logs.created_at) AS year,
                MONTH(mood_logs.created_at) AS month,
                ROUND(AVG(mood_logs.energy),1) AS avgEnergy,
                ROUND(AVG(mood_logs.pleasantness),1) AS avgPleasantness,
                COUNT(DISTINCT DATE(mood_logs.created_at)) AS daysLogged,
                COUNT(*) AS totalLogs
            ")
            ->where('users.sid', $user->sid)
            ->where('users.grade', $user->grade)
            ->groupByRaw('YEAR(mood_logs.created_at), MONTH(mood_logs.created_at)')
            ->orderByRaw('YEAR(mood_logs.created_at) DESC, MONTH(mood_logs.created_at) DESC')
            ->get()
            ->map(function ($month) use ($user) {

                $dominantMood = DB::table('mood_logs')
                    ->join('users', 'users.id', '=', 'mood_logs.user_id')
                    ->select('mood_logs.mood', DB::raw('COUNT(*) as total'))
                    ->where('users.sid', $user->sid)
                    ->where('users.grade', $user->grade)
                    ->whereYear('mood_logs.created_at', $month->year)
                    ->whereMonth('mood_logs.created_at', $month->month)
                    ->groupBy('mood_logs.mood')
                    ->orderByDesc('total')
                    ->value('mood_logs.mood');

                return [
                    'year' => (int) $month->year,
                    'month' => (int) $month->month,
                    'dominantMood' => $dominantMood,
                    'avgEnergy' => (float) $month->avgEnergy,
                    'avgPleasantness' => (float) $month->avgPleasantness,
                    'daysLogged' => (int) $month->daysLogged,
                    'totalLogs' => (int) $month->totalLogs,
                ];
            })
            ->values()
            ->toArray();

        return compact('myLogs', 'todayMyLog', 'cityMonths');
    }
}
