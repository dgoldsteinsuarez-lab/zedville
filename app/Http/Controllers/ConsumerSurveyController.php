<?php

namespace App\Http\Controllers;

use App\Models\Avatar;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Mascot;
use App\Models\NotificationPreference;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Services\BalanceService;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The monthly consumer profile survey: the form page and the submit
 * handler that posts a month's groceries/lunch/transport/activities/
 * restaurants as real ledger debits. Extracted out of ProfileController
 * (planning.md Phase 3 #4) - fully self-contained.
 */
class ConsumerSurveyController extends Controller
{
    public function consumerProfileSurvey(): View
    {
        $user = Auth::user();

        $avatar = Avatar::where('status', 1)->get();
        $mascots = Mascot::where('status', 1)->get();
        $grades = SchoolClass::where('status', 1)->get();

        // Get latest consumer survey
        $Consumersurvey = ConsumerSurvey::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        // Fix for groceries format (string or array)
        $groceriesRaw = $Consumersurvey->groceries ?? '[]';

        $groceryItems = is_array($groceriesRaw)
            ? $groceriesRaw
            : json_decode($groceriesRaw, true);

        $itemNames = collect($groceryItems)->pluck('name')->implode(', ');

        $selectedAvatar = $avatar->firstWhere('id', $user->avatar);
        $hasBankAccount = BankAccount::where('student_id', $user->id)->exists();

        // Settings > Notifications tab: existing prefs, defaulting to
        // enabled for any category the user hasn't touched yet.
        $savedPrefs = NotificationPreference::where('user_id', $user->id)
            ->pluck('enabled', 'category');
        $notificationPreferences = collect(NotificationPreference::CATEGORIES)
            ->mapWithKeys(fn ($category) => [$category => $savedPrefs->get($category, true)]);

        return view('profile.consumer-profile-survey', [
            'user' => $user,
            'mascots' => $mascots,
            'avatar' => $avatar,
            'selectedAvatar' => $selectedAvatar,
            'grades' => $grades,
            'itemNames' => $itemNames,
            'hasBankAccount' => $hasBankAccount,
            'notificationPreferences' => $notificationPreferences,
        ]);
    }

    public function storesurvey(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();

        /* ============================
           1️⃣ Find first free survey month
        ============================ */
        $month = Carbon::now()->startOfMonth();

        while (true) {
            $surveyMonth = $month->format('M-Y');

            $exists = ConsumerSurvey::where('user_id', $userId)
                ->where('survey_month', $surveyMonth)
                ->exists();

            if (!$exists) {
                break;
            }

            $month->addMonth();
        }

        /* ============================
           2️⃣ Get available balance
        ============================ */
        $latestTxn = Transaction1::where('user_id', $userId)
            ->latest('id')
            ->first();

        $availableBalance = $latestTxn ? $latestTxn->balance : 0;

        /* ============================
           3️⃣ Calculate TOTAL required amount
        ============================ */
        $totalRequired = 0;

        // groceries
        if (is_array($request->groceries)) {
            foreach ($request->groceries as $item) {
                $totalRequired += $item['total_price'] ?? 0;
            }
        }

        // other expenses
        $totalRequired += ($request->lunch ?? 0);
        $totalRequired += ($request->transport ?? 0);
        $totalRequired += ($request->activities ?? 0);
        $totalRequired += ($request->restaurants ?? 0);

        /* ============================
           4️⃣ BLOCK if insufficient balance
        ============================ */
        if ($availableBalance < $totalRequired) {
            return response()->json([
                'status' => false,
                'message' => 'Your Survey not completed. Insufficient balance. Your available balance is '
                    . number_format($availableBalance, 2)
                    . ' Z. Required amount is '
                    . number_format($totalRequired, 2)
                    . ' Z.'
            ], 422);
        }

        /* ============================
           5️⃣ Begin DB Transaction
        ============================ */
        DB::beginTransaction();

        try {

            /* ============================
               ✅ Authoritative balance re-check under row lock — the check
               above happened before the transaction opened, so it can't
               prevent a concurrent spend from racing in between. Re-verify
               with a locked read and use that value as the running balance.
            ============================ */
            $availableBalance = BalanceService::lockedBalance($userId);

            if ($availableBalance < $totalRequired) {
                throw new \Exception('Insufficient balance');
            }

            /* ============================
               6️⃣ Store survey
            ============================ */
            $survey = ConsumerSurvey::create([
                'user_id' => $userId,
                'sid' => $user->sid,
                'diet' => $request->diet,
                'groceries' => $request->groceries,
                'basket_cost' => $request->basketCost,
                'lunch' => $request->lunch,
                'transport' => $request->transport,
                'activities' => $request->activities,
                'restaurants' => $request->restaurants,
                'total_cost' => $request->totalCost,
                'survey_month' => $surveyMonth,
            ]);

            /* ============================
               7️⃣ Prepare transaction data
            ============================ */
            $bankAccount = BankAccount::where('student_id', $userId)->first();
            $now = Carbon::now();

            /* ============================
            8️⃣ Store GROCERIES (SINGLE ENTRY)
            ============================ */
            $groceryTotal = 0;

            if (is_array($request->groceries)) {
                foreach ($request->groceries as $item) {
                    $groceryTotal += $item['total_price'] ?? 0;
                }
            }

            if ($groceryTotal > 0) {
                LedgerService::post(
                    $userId,
                    'debit',
                    'Needs',
                    (float) $groceryTotal,
                    'Grocery – ' . ucfirst($request->diet),
                    [
                        'sid' => $user->sid,
                        'bank_account_id' => $bankAccount->id ?? null,
                        'transaction_date' => $now,
                    ]
                );
            }

            /* ============================
               9️⃣ Store OTHER expenses
            ============================ */
            $otherExpenses = [
                'lunch' => ['School Lunch', 'Needs'],
                'transport' => ['Transportation', 'Needs'],
                'activities' => ['Activities & Hobbies', 'Wants'],
                'restaurants' => ['Eating Out', 'Wants'],
            ];

            foreach ($otherExpenses as $field => [$description, $category]) {

                $amount = $request->$field ?? 0;

                if ($amount > 0) {
                    LedgerService::post(
                        $userId,
                        'debit',
                        $category,
                        (float) $amount,
                        $description,
                        [
                            'sid' => $user->sid,
                            'bank_account_id' => $bankAccount->id ?? null,
                            'transaction_date' => $now,
                        ]
                    );
                }
            }

            /* ============================
               🔟 Commit
            ============================ */
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Survey stored for $surveyMonth",
                'data' => $survey,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Survey failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
