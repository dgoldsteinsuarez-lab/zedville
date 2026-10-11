<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Mascot;
use App\Models\SchoolClass;
use App\Models\Avatar;
use App\Models\MoodLog;
use App\Models\ConsumerSurvey;
use App\Models\BankAccount;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $now = now(); // ✅ month-safe, no Carbon import issues

        // 🔹 Avatars & mascots
        $avatar = Avatar::where('status', 1)->get();
        $mascots = Mascot::where('status', 1)->get();
        $selectedAvatar = $avatar->firstWhere('id', $user->avatar);
        // 🔹 Bank account check
        $hasBankAccount = BankAccount::where('student_id', $user->id)->exists();


        // 🔹 Grades
        $grades = SchoolClass::where('status', 1)->get();

        // 🔹 CURRENT MONTH consumer survey ONLY
        $Consumersurvey = ConsumerSurvey::where('user_id', $user->id)
            //->whereMonth('created_at', $now->month)
            //->whereYear('created_at', $now->year)
            ->orderBy('id', 'desc')
            ->first();

        $ConsumersurveyExists = (bool) $Consumersurvey;

        // 🔹 Groceries (string / array safe)
        $groceriesRaw = $Consumersurvey->groceries ?? '[]';

        $groceryItems = is_array($groceriesRaw)
            ? $groceriesRaw
            : json_decode($groceriesRaw, true);

        $itemNames = collect($groceryItems)->pluck('name')->implode(', ');

        // 🔹 Default values
        $basketTotal = 0;
        $lunch = 0;
        $transport = 0;
        $activities = 0;
        $restaurants = 0;
        $total = 0;

        if ($Consumersurvey) {
            $basketTotal = $Consumersurvey->basket_cost ?? 0;
            $lunch = $Consumersurvey->lunch ?? 0;
            $transport = $Consumersurvey->transport ?? 0;
            $activities = $Consumersurvey->activities ?? 0;
            $restaurants = $Consumersurvey->restaurants ?? 0;

            $total = $Consumersurvey->total_cost
                ?? ($basketTotal + $lunch + $transport + $activities + $restaurants);
        }
        $moodData = $this->getMoodData();
        $testMode = false;
        if ($testMode) {
            // ✅ HARD CODE TEST DATA
            $engagement = [
                'monthly' => 'PLATINUM',
                'yearly' => 'GOLD',
                'academicStartYear' => 2025,
                'history' => [
                    "8" => "BRONZE",
                    "9" => "SILVER",
                    "10" => "GOLD",
                    "11" => "PLATINUM",
                    "0" => "GOLD",
                    "1" => "SILVER",
                    "2" => "BRONZE",
                    "3" => "NONE",
                    "4" => null,
                    "5" => null,
                ]
            ];

            $finhero = [
                'monthly' => 'LEGEND',
                'yearly' => 'CHAMPION',
                'academicStartYear' => 2025,
                'history' => [
                    "8" => "ROOKIE",
                    "9" => "FINHERO",
                    "10" => "CHAMPION",
                    "11" => "LEGEND",
                    "0" => "FINHERO",
                    "1" => "ROOKIE",
                    "2" => "NONE",
                    "3" => null,
                    "4" => null,
                    "5" => null,
                ]
            ];
        } else {
            $engagement = $this->getEngagementData($user->id);
            $finhero = $this->getFinHeroData($user->id);

        }
        //Durable goods / Non Durable 
        $userId = Auth::id();

        // Durable Goods
        $durableProducts = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.product_name', '=', 'order_items.name')
            ->where('orders.user_id', $userId)
            ->where('products.goods_type', 'Durable Goods')
            ->select('products.product_name')
            ->distinct()
            ->pluck('product_name');
        //dd($durableProducts);
        // Non Durable Goods
        $nonDurableProducts = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.product_name', '=', 'order_items.name')
            ->where('orders.user_id', $userId)
            ->where('products.goods_type', 'Not Durable Goods')
            ->select('products.product_name')
            ->distinct()
            ->pluck('product_name');
        //Durable goods / Non Durable goods

        // Settings > Notifications tab: existing prefs, defaulting to
        // enabled for any category the user hasn't touched yet.
        $savedNotificationPrefs = \App\Models\NotificationPreference::where('user_id', $user->id)
            ->pluck('enabled', 'category');
        $notificationPreferences = collect(\App\Models\NotificationPreference::CATEGORIES)
            ->mapWithKeys(fn ($category) => [$category => $savedNotificationPrefs->get($category, true)]);

        return view('profile.edit', [
            'notificationPreferences' => $notificationPreferences,
            'user' => $user,
            'mascots' => $mascots,
            'avatar' => $avatar,
            'selectedAvatar' => $selectedAvatar,
            'grades' => $grades,

            // 🔹 Survey data (MONTH-WISE)
            'ConsumersurveyExists' => $ConsumersurveyExists,
            'Consumersurvey' => $Consumersurvey,
            'basketTotal' => $basketTotal,
            'lunch' => $lunch,
            'transport' => $transport,
            'activities' => $activities,
            'restaurants' => $restaurants,
            'total' => $total,
            'itemNames' => $itemNames,
            'hasBankAccount' => $hasBankAccount,
            // ✅ ADD THESE
            'myLogs' => $moodData['myLogs'],
            'todayMyLog' => $moodData['todayMyLog'],
            'cityMonths' => $moodData['cityMonths'],
            'engagement' => $engagement, // ✅ ADD THIS
            'finhero' => $finhero, // ✅ ADD THIS
            'durableProducts' => $durableProducts, // ✅ ADD THIS
            'nonDurableProducts' => $nonDurableProducts, // ✅ ADD THIS

        ]);
    }


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'exists:avatars,id'],
        ]);

        $user = $request->user();
        $user->avatar = $request->avatar;
        $user->save();

        return redirect()->back()->with('status', 'avatar-updated');
    }
    /*public function saveMode(Request $request)
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
        $user = Auth::user();
        $moodlog = MoodLog::create([
            'user_id' => $user->id,
            'sid' => $user->sid,
            'mood' => $request->mood,
            'energy' => $data['energy'],                // Add this
            'pleasantness' => $data['pleasantness'],    // Add this
            'created_at' => now()
        ]);

        return response()->json(['message' => 'Mood saved successfully']);
    }*/
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
            'energy' => $data['energy'],                // Add this
            'pleasantness' => $data['pleasantness'],    // Add this
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

        return view('profile.city-mood', compact('myLogs', 'todayMyLog', 'cityMonths'));
    }
    private function getMoodData()
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
        //return view('profile.city-mood', compact('myLogs', 'todayMyLog', 'cityMonths'));
        return compact('myLogs', 'todayMyLog', 'cityMonths');
    }
    //Student engagement  badges
    private function calculateMonthlyEngagement($studentId, $month, $year)
    {
        // 🔹 REQUIRED EVENTS (calendar_events)
        $assignedRequired = DB::table('calendar_events')
            ->whereMonth('start', $month + 1)
            ->whereYear('start', $year)
            ->where('activityType', 'required')
            ->count();

        $completedRequired = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('action_type', 'required')
            ->count();

        $requiredPct = $assignedRequired > 0
            ? ($completedRequired / $assignedRequired) * 100
            : 0;

        // 🔹 OPTIONAL COUNT
        $optionalCount = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('action_type', 'optional')
            ->count();

        // 🔹 PARTICIPATION POINTS (CAPPED AT 15)
        $participationPts = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('points_earned');

        $participationPts = min($participationPts, 15); // ✅ CAP APPLIED

        // 🔹 BADGE LOGIC
        if ($requiredPct == 100 && $optionalCount >= 4 && $participationPts >= 15) {
            return 'PLATINUM';
        } elseif ($requiredPct == 100 && $optionalCount >= 3 && $participationPts >= 10) {
            return 'GOLD';
        } elseif ($requiredPct == 100 && $optionalCount >= 2 && $participationPts >= 7) {
            return 'SILVER';
        } elseif ($requiredPct == 100 && $optionalCount >= 1 && $participationPts >= 5) {
            return 'BRONZE';
        }

        return 'NONE';
    }
    private function getEngagementData($studentId)
    {
        $now = now();

        // Academic year starts in September
        $academicYearStartMonth1Based = config('zedville.academic_year_start_month', 4);
        $academicStartMonth = $academicYearStartMonth1Based - 1; // JS style (0-based)
        $academicStartYear = $now->month >= $academicYearStartMonth1Based ? $now->year : $now->year - 1;

        $pointsMap = [
            'PLATINUM' => 4,
            'GOLD' => 3,
            'SILVER' => 2,
            'BRONZE' => 1,
            'NONE' => 0
        ];

        $history = [];
        $totalPts = 0;

        $month = $academicStartMonth;
        $year = $academicStartYear;

        // 🔹 Loop 10 academic months
        for ($i = 0; $i < 10; $i++) {

            $badge = $this->calculateMonthlyEngagement($studentId, $month, $year);

            $history[$month] = $badge;
            $totalPts += $pointsMap[$badge];

            $month++;
            if ($month > 11) {
                $month = 0;
                $year++;
            }
        }

        // 🔹 CURRENT MONTH
        /*$currentMonth = $now->month - 1; // convert to 0-based
        $currentYear = $now->year;

        $monthly = $this->calculateMonthlyEngagement($studentId, $currentMonth, $currentYear);*/
        // 🔹 LAST COMPLETED MONTH
        // 🔹 CURRENT MONTH (DB uses 0-11 format)
        $currentMonth = $now->month - 1;
        $currentYear = $now->year;

        $monthly = $this->calculateMonthlyEngagement(
            $studentId,
            $currentMonth,
            $currentYear
        );

        // 🔹 YEARLY BADGE
        if ($totalPts >= 32)
            $yearly = 'PLATINUM';
        elseif ($totalPts >= 24)
            $yearly = 'GOLD';
        elseif ($totalPts >= 16)
            $yearly = 'SILVER';
        elseif ($totalPts >= 8)
            $yearly = 'BRONZE';
        else
            $yearly = 'NONE';

        /*return [
            'monthly' => $monthly,
            'yearly' => $yearly,
            'history' => $history,
            'academicStartYear' => $academicStartYear
        ];*/
        return [
            'monthly' => $monthly,
            'yearly' => $yearly,
            'history' => $history,
            'academicStartYear' => $academicStartYear,
            'academicStartMonth' => $academicStartMonth,
            'currentMonth' => $currentMonth - 1,
            'currentYear' => $currentYear,
        ];
    }
    private function calculateMonthlyFinHero($studentId, $month, $year)
    {
        // 🔹 GET MONTH SETTINGS (thresholds + active module)
        $settings = DB::table('finhero_monthly_settings')
            ->where('month', $month)
            ->where('year', $year)
            ->where('badge_active', 1)
            ->first();

        if (!$settings || !$settings->badge_active) {
            return 'NONE';
        }

        // 🔹 QUIZ POINTS (MAX 10)
        $quizPts = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('source_type', 'quiz')
            ->sum('points_earned');

        $quizPts = min($quizPts, 10);

        // 🔹 LIBRARY POINTS (MAX 10, ONLY ACTIVE MODULE)
        $libraryPts = 0;

        if ($settings->active_library_module_id) {
            $libraryPts = DB::table('finhero_student_points')
                ->where('student_id', $studentId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('source_type', 'library')
                ->where('source_key', $settings->active_library_module_id)
                ->sum('points_earned');

            $libraryPts = min($libraryPts, 10);
        }

        // 🔹 ACTIVITY POINTS (ONLY ACTIVE ACTIVITIES)
        $activeActivities = DB::table('finhero_activity_registry')
            ->where('is_active', 1)
            ->pluck('max_points', 'activity_key');

        $activityPts = 0;
        $totalActivityMax = 0;

        foreach ($activeActivities as $key => $maxPoints) {

            $earned = DB::table('finhero_student_points')
                ->where('student_id', $studentId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('source_type', 'activity')
                ->where('source_key', $key)
                ->sum('points_earned');

            $activityPts += min($earned, $maxPoints);
            $totalActivityMax += $maxPoints;
        }

        // 🔹 TOTALS
        $totalEarned = $quizPts + $libraryPts + $activityPts;

        $totalAvailable = 10 + ($settings->active_library_module_id ? 10 : 0) + $totalActivityMax;
        /*$totalAvailable = 10
            + ($settings->active_library_module_id ? 10 : 0)
            + ($activityPts > 0 ? $totalActivityMax : 0);*/

        if ($totalAvailable == 0) {
            return 'NONE';
        }

        $pct = ($totalEarned / $totalAvailable) * 100;

        // 🔹 BADGE LOGIC (FROM SETTINGS)
        if ($pct >= $settings->threshold_legend) {
            return 'LEGEND';
        } elseif ($pct >= $settings->threshold_champion) {
            return 'CHAMPION';
        } elseif ($pct >= $settings->threshold_finhero) {
            return 'FINHERO';
        } elseif ($pct >= $settings->threshold_rookie) {
            return 'ROOKIE';
        }
        /*if ($month == 6) {
            dd([
                'quizPts' => $quizPts,
                'libraryPts' => $libraryPts,
                'activityPts' => $activityPts,
                'totalEarned' => $totalEarned,
                'totalAvailable' => $totalAvailable,
                'percentage' => $pct,
                'thresholds' => [
                    'legend' => $settings->threshold_legend,
                    'champion' => $settings->threshold_champion,
                    'finhero' => $settings->threshold_finhero,
                    'rookie' => $settings->threshold_rookie,
                ]
            ]);
        }*/
        return 'NONE';
    }
    private function getFinHeroData($studentId)
    {
        $now = now();

        // Academic year starts September
        $academicYearStartMonth1Based = config('zedville.academic_year_start_month', 4);
        $academicStartMonth = $academicYearStartMonth1Based - 1; // JS style (0-based)
        $academicStartYear = $now->month >= $academicYearStartMonth1Based ? $now->year : $now->year - 1;

        $pointsMap = [
            'LEGEND' => 4,
            'CHAMPION' => 3,
            'FINHERO' => 2,
            'ROOKIE' => 1,
            'NONE' => 0
        ];

        $history = [];
        $totalPts = 0;

        $month = $academicStartMonth;
        $year = $academicStartYear;

        // 🔹 LOOP 10 MONTHS
        for ($i = 0; $i < 10; $i++) {

            $badge = $this->calculateMonthlyFinHero($studentId, $month, $year);

            $history[$month] = $badge;
            $totalPts += $pointsMap[$badge];

            $month++;
            if ($month > 11) {
                $month = 0;
                $year++;
            }
        }

        // 🔹 CURRENT MONTH
        /*$currentMonth = $now->month - 1;
        $currentYear = $now->year;

        $monthly = $this->calculateMonthlyFinHero($studentId, $currentMonth, $currentYear);*/
        // 🔹 LAST COMPLETED MONTH
        // 🔹 CURRENT MONTH (DB uses 0-11 format)
        // Current month according to database (0 based)
        $currentMonth = $now->month - 1;
        $currentYear = $now->year;


        // If current month is not completed,
// show previous month badge
        if ($now->day < 31) {
            $currentMonth--;

            if ($currentMonth < 0) {
                $currentMonth = 11;
                $currentYear--;
            }
        }


        $monthly = $this->calculateMonthlyFinHero(
            $studentId,
            $currentMonth,
            $currentYear
        );
        // 🔹 YEARLY BADGE (ACCORDING TO YOUR TABLE)
        if ($totalPts >= 32)
            $yearly = 'LEGEND';
        elseif ($totalPts >= 24)
            $yearly = 'CHAMPION';
        elseif ($totalPts >= 16)
            $yearly = 'FINHERO';
        elseif ($totalPts >= 8)
            $yearly = 'ROOKIE';
        else
            $yearly = 'NONE';

        /*return [
            'monthly' => $monthly,
            'yearly' => $yearly,
            'history' => $history,
            'academicStartYear' => $academicStartYear
        ];*/
        return [
            'monthly' => $monthly,
            'yearly' => $yearly,
            'history' => $history,
            'academicStartYear' => $academicStartYear,
            'academicStartMonth' => $academicStartMonth,
            'currentMonth' => $currentMonth - 1,
            'currentYear' => $currentYear,
        ];
    }
}