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

        // City Map, Phase 1 (2026-10-19): the student's own street+number,
        // auto-assigned on first view - null if they have no Class yet or
        // their Grade's address pool is exhausted.
        $myAddress = $user->isStudent()
            ? \App\Services\AddressService::assignForStudent($user)?->load('street')
            : null;


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
        $moodData = app(MoodController::class)->getMoodData();
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
            'myAddress' => $myAddress,
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

        // Daniel's real spec (engagement_badge_spec_v2, §7 Edge Cases):
        // "No Required activities assigned that month → No badge can be
        // earned that month. The Required category cannot be satisfied if
        // the calendar is empty." 0 is correct here, not a bug - reverted
        // an earlier incorrect "fix" that assumed otherwise before this
        // doc was available (bugs.md, 2026-10-19).
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