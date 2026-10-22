<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\ParticipationService;
use App\Models\BankAccount;
use App\Models\DirectDeposite;
use App\Models\AutoDebitRequest;
use App\Models\Transaction;
use App\Models\UserMba;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction1;
use App\Models\Donation;
use Illuminate\Http\Request;
use App\Helpers\MailboxScheduler;
use Illuminate\Support\Facades\Auth;
use App\Models\Npo;
use App\Models\WellbeingPost;
use App\Models\Referendum;
use App\Models\Petition;
use App\Models\ReferendumVote;
use App\Models\PetitionSignature;
use App\Models\CityProposal;
use App\Services\FinheroPointService;


class EFDController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /*$transactions = Transaction1::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->get();*/
        $start = now()->subMonth()->startOfMonth();
        $end = now()->subMonth()->endOfMonth();

        $transactions = Transaction1::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('id', 'desc')
            ->get();
        $template = collect();

        /* ===============================
           PENALTY (SUM ALL)
        =============================== */
        $penaltyTransactions = $transactions->filter(function ($t) {
            return strtolower($t->category) === 'penalty';
        });

        if ($penaltyTransactions->isNotEmpty()) {
            $penaltyTotal = $penaltyTransactions->sum(function ($t) {
                return $t->type === 'debit'
                    ? -(float) $t->amount
                    : (float) $t->amount;
            });

            $template->push([
                'id' => 'penalty',
                'name' => 'Penalty: All Items',
                'amount' => $penaltyTotal,
                'category' => 'penalty',
                'type' => 'expense',
            ]);
        }

        /* ===============================
           ALL OTHER TRANSACTIONS
        =============================== */
        $transactions
            ->filter(function ($t) {
                return strtolower($t->category) !== 'penalty';
            })
            ->each(function ($t) use ($template) {
                $template->push([
                    'id' => (string) $t->id,
                    'name' => $t->description,
                    'amount' => $t->type === 'debit'
                        ? -(float) $t->amount
                        : (float) $t->amount,
                    'category' => strtolower($t->category),
                    'type' => $t->type === 'credit' ? 'income' : 'expense',
                ]);
            });

        return view('education.index', [
            'user' => $user,
            'template' => $template->values(),
        ]);
    }


    public function educational_finance_department()
    {
        $user = auth()->user();
        //app(ParticipationService::class)->award($user->id, 'poster_room_enter');
        $posters = DB::table('room_poster')->orderBy('id', 'DESC')->get();
        //$mbaPosition = DB::table('mba_position')->orderBy('id', 'DESC')->get()->first();
        $mbaPosition = DB::table('mba_position')
            //->where('user_id', Auth::id())
            //->where('id', 1)   // ✅ MBA
            ->where('activity_key', 'mba')
            ->where('sid', $user->sid)
            ->where('cid', $user->grade)
            ->first();
        $emmfundPosition = DB::table('mba_position')
            //->where('user_id', Auth::id())
            //->where('id', 2)   // ✅ Emergency Fund
            ->where('activity_key', 'emeaccount')
            ->where('sid', $user->sid)
            ->where('cid', $user->grade)
            ->first();
        //Check Event Avilable or not
        $today = Carbon::today();
        $hasHBAActivity = DB::table('calendar_events')
            ->where('activitys', 1)
            ->where('classId', $user->grade) // ✅ class filter
            ->whereDate('end', '>=', $today)   // ✅ not expired
            ->exists();
        $hbaPosition = null;
        if ($hasHBAActivity) {
            //Check Event Avilable or not
            $hbaPosition = DB::table('mba_position')
                //->where('user_id', Auth::id())
                //->where('id', 3)   // ✅ High Budget Activity
                ->where('activity_key', 'hba_position')
                ->where('sid', $user->sid)
                ->where('cid', $user->grade)
                ->first();
        }
        // lbaPosition (Low Budget Activity) removed (Daniel, 2026-10-21:
        // "remove low spending activities from the system") - this was
        // computed but never actually read by the blackboard view below,
        // so nothing downstream needs it.
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        $isBankAccountOpen = $bankAccount !== null;
        //Emmargency Fund
        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $emmsavingsPct = 20;
        $emmsavingsAmount = round($salaryamount * ($emmsavingsPct / 100), 2);
        $emergencyFundInterest = $this->emergencyFundInterest($user->id);
        $emmengercyfundtransactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->where('description', 'Auto Transfer to Savings Account')
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        //Select fin hero Task
        // Daniel, 2026-10-21: "classes have the same tasks, should be for
        // grade... one grade can have many classes" - a task assigned to
        // the whole grade (grade_id set, cid null) must reach every
        // class under it, not just an exact class-id match.
        $userGradeId = DB::table('classes')->where('id', $user->grade)->value('grade_id');
        $taskActivities = DB::table('finhero_activity_registry')
            ->where('type', 'task')
            ->where('sid', $user->sid)
            ->where(function ($q) use ($user, $userGradeId) {
                $q->where('cid', $user->grade);
                if ($userGradeId) {
                    $q->orWhere('grade_id', $userGradeId);
                }
            })
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get();
        // Daniel, 2026-10-21: "5. do it" - FinHero Library activities were
        // never read here at all before (only type='task' was), so an
        // admin-created Library item had nowhere to show up as a Required
        // Activity. Same scope rule as taskActivities above.
        $libraryActivities = DB::table('finhero_activity_registry')
            ->where('type', 'library')
            ->where('sid', $user->sid)
            ->where(function ($q) use ($user, $userGradeId) {
                $q->where('cid', $user->grade);
                if ($userGradeId) {
                    $q->orWhere('grade_id', $userGradeId);
                }
            })
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get();
        $libraryModalModules = $this->libraryModalModules($libraryActivities->pluck('activity_key')->all());
        //Complease activity
        $completedActivities = DB::table('activity_completions')
            ->where('student_id', $user->id)
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->pluck('activity_type')
            ->toArray();
        return view('education.educational-finance-departmentex', compact('posters', 'mbaPosition', 'emmfundPosition', 'bankAccount', 'emmsavingsAmount', 'emergencyFundInterest', 'emmengercyfundtransactions', 'isBankAccountOpen', 'hbaPosition', 'taskActivities', 'libraryActivities', 'libraryModalModules', 'completedActivities'));
    }

    /**
     * The Library pop-up's data, in the shape the old hardcoded
     * public/asset/front/js/modules.js had, plus question/option ids so
     * "Check Answers" can send the answers to the server, and whether the
     * module is assigned to this student (only assigned modules earn
     * points). The pop-up writes these strings into innerHTML, so every
     * piece of text is HTML-escaped here.
     */
    private function libraryModalModules(array $assignedKeys): array
    {
        $subtopic = fn (array $section) => [
            'title' => e($section['title'] ?? ''),
            'text' => array_map(fn ($line) => e($line), $section['lines'] ?? []),
        ];

        return \App\Models\FinheroLibraryModule::with('questions.options')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($module) => [
                'id' => $module->id,
                'activity_key' => $module->module_key,
                'assigned' => in_array($module->module_key, $assignedKeys, true),
                'title' => e($module->title),
                'icon' => e($module->icon),
                'color' => 'from-emerald-400 to-teal-500',
                'content' => [
                    'subtopic1' => $subtopic($module->lesson[0] ?? []),
                    'subtopic2' => $subtopic($module->lesson[1] ?? []),
                    'questions' => $module->questions->map(fn ($question) => [
                        'qid' => $question->id,
                        'q' => e($question->question_text),
                        'options' => $question->options->map(fn ($option) => e($option->option_text))->values()->all(),
                        'option_ids' => $question->options->pluck('id')->values()->all(),
                        'correct' => $question->options->search(fn ($option) => $option->is_correct),
                    ])->values()->all(),
                ],
            ])
            ->values()
            ->all();
    }
    public function emergencyFundInterest($userId)
    {
        // 1️⃣ Get previous month info
        $previousMonth = Carbon::now()->subMonth();
        $monthName = $previousMonth->format('F');
        $year = $previousMonth->year;

        // 2️⃣ Interest config
        $annualInterestRate = 0.02; // 2% per annum
        $daysInMonth = $previousMonth->daysInMonth;
        $daysInYear = $previousMonth->isLeapYear() ? 366 : 365;
        $dailyRate = $annualInterestRate / $daysInYear;

        // 3️⃣ Get ending balance (latest txn before end of month)
        $endingBalance = Transaction1::where('user_id', $userId)
            ->where('description', 'Auto Transfer to Savings Account')
            ->where('transaction_date', '<=', $previousMonth->endOfMonth())
            ->orderBy('transaction_date', 'desc')
            ->value('balance');


        if (is_null($endingBalance)) {
            $endingBalance = 0;
        }

        // 4️⃣ Calculate monthly estimated interest
        $grossMonthlyInterest = $endingBalance * $dailyRate * $daysInMonth;

        // 5️⃣ Annual projection (approx)
        $annualProjection = $endingBalance * $annualInterestRate;
        $nextPaymentDate = Carbon::now()->addMonth()->setDay(1)->format('F j, Y');
        // 6️⃣ Return for dashboard or API
        return [
            'month' => $monthName,
            'year' => $year,
            'ending_balance' => round($endingBalance, 2),
            'estimated_interest' => round($grossMonthlyInterest, 2),
            'annual_projection' => round($annualProjection, 2),
            'annual_rate' => $annualInterestRate * 100,
            'next_payment_date' => $nextPaymentDate,
        ];
    }
    public function multipleChoiceEmail()
    {
        return view('education.multiple-choice-email');
    }
    public function quizMultipleChoiceQuestion()
    {
        return view('education.quiz-multiple-question');
    }
    public function budgetQuiz()
    {
        return view('education.budget-quiz');
    }
    public function budgetQuiz2()
    {
        return view('education.budget-quiz-2');
    }
    /**
     * Daniel, 2026-10-21: "the Match Needs and Wants, is the one called
     * Monthly Budget Activity. You can move it to Finhero Badge Activity...
     * 3 points if done all correct the first time, and let two attempts
     * before loosing one point."
     *
     * This game never lets a wrong drop land (see mba.js's handleDrop) -
     * there's no single "submit and grade" moment, so "first time" is
     * measured as the total count of wrong-category drops across the whole
     * session, sent as `mistakes`. Tiered (stated explicitly since the
     * exact thresholds were my call, not spelled out): 0 mistakes = 3
     * points, 1-2 mistakes ("two attempts" of leeway) = 2 points, 3+ = 1
     * point - never zero for actually finishing the activity.
     *
     * Only the first completion each month earns FinHero points (explicit
     * month/year check, not FinheroPointService's own per-day dedup, which
     * would let a same-month replay re-award) - re-saving later the same
     * month still updates the stored layout, just doesn't pay out again.
     */
    public function storemba(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'budget' => 'required|array',
            'mistakes' => 'nullable|integer|min:0',
        ]);

        // Save or update the record for this user
        $mba = UserMba::updateOrCreate(
            ['user_id' => $userId],     // condition (update if exists)
            ['budget' => $validated['budget']] // data to save
        );
        // Completion (against this class's calendar event) + participation point.
        app(ParticipationService::class)->handleActivity($userId, 'mba', 'Needs, Wants & Savings Match');

        $pointsAwarded = 0;
        $alreadyAwardedThisMonth = DB::table('finhero_student_points')
            ->where('student_id', $userId)
            ->where('source_type', 'activity')
            ->where('source_key', 'needs_wants_savings_match')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->exists();

        if (!$alreadyAwardedThisMonth) {
            $mistakes = (int) ($validated['mistakes'] ?? 0);
            $pointsAwarded = $mistakes === 0 ? 3 : ($mistakes <= 2 ? 2 : 1);
            app(FinheroPointService::class)->addPoints($userId, 'activity', 'needs_wants_savings_match', $pointsAwarded);
        }

        return response()->json([
            'status' => true,
            'message' => 'Needs, Wants & Savings Match saved successfully!',
            'data' => $mba,
            'pointsAwarded' => $pointsAwarded,
        ]);
    }
    public function getMbaData(Request $request)
    {
        $userId = Auth::id();

        // Get current month range
        //$startOfMonth = now()->startOfMonth();
        //$endOfMonth = now()->endOfMonth();

        // Previous month range
        $startOfMonth = now()->subMonth()->startOfMonth();
        $endOfMonth = now()->subMonth()->endOfMonth();

        // Fetch latest record for this month
        $mba = UserMba::where('user_id', $userId)
            //->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->first();

        if (!$mba) {
            return response()->json([
                'status' => false,
                'message' => 'No MBA data found for this month.',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'MBA data fetched successfully.',
            'data' => $mba->budget  // full JSON array
        ]);
    }
    //City Hall
    public function city_mall()
    {
        app(\App\Services\ParticipationService::class)->award(auth()->id(), 'building_enter', 'city_mall');
        return view('education.city-mall');
    }
    public function spending_tracker($type)
    {
        $user = auth()->user();
        $hasBankAccount = BankAccount::where('student_id', $user->id)->exists();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();

        $classmates = User::where('id', '!=', $user->id)
            ->where('role', '4')
            ->where('sid', $user->sid)
            ->where('grade', $user->grade)
            ->orderBy('citizenId')
            ->get(['id', 'citizenId', 'nickname']);

        // URL → DB mapping
        $storeMap = [
            'accessories' => 'Accessories',
            'bespirit-sport-shop' => 'BeSpirit - Sport Shop',
            'beats-music-store' => 'Beats - Music Store',
            'comfort-zone' => 'Comfort Zone',
            'beu-beLuxury' => 'BeU - BeLuxury',
            'tech-hub' => 'Tech Hub',
            'daily-essentials' => 'Daily Essentials',
            'stationery-store' => 'Stationery Store',
            'the-basics-co' => 'The Basics Co.',
            'basicco' => 'The Basics Co.',
            'stationary' => 'The Stanary.',
            'citymall' => 'The City Mall.',
        ];

        // Validate URL
        if (!array_key_exists($type, $storeMap)) {
            abort(404);
        }

        // Actual DB value
        $dbType = $storeMap[$type];

        // Fetch products using DB value
        $products = Product::where('type', $dbType)
            ->orderBy('product_name')
            ->get();

        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        $npos = Npo::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        return view(
            'education.spending-tracker-basicco',
            compact(
                'user',
                'bankAccount',
                'classmates',
                'products',
                'type',      // URL value (basicco)
                'dbType',    // DB value (The Basics Co.)
                'lastBalance',
                'hasBankAccount',
                'npos'
            )
        );
    }
    public function calendar()
    {
        $user = DB::table('users')
            ->join('classes', 'users.grade', '=', 'classes.id')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'classes.id as class_id',
                'classes.name as class_name'
            )
            ->where('users.id', auth()->id())
            ->first();
        return view('calendar.index', compact('user'));
    }

    public function getEventsByUserClass()
    {
        $userId = auth()->id();

        // Safety check
        if (!$userId) {
            return response()->json([], 401);
        }

        // Get class_id from users table
        $classId = DB::table('users')
            ->where('id', $userId)
            ->value('grade'); // or class_id if your column name is class_id

        // If user has no class assigned
        if (!$classId) {
            return response()->json([]);
        }

        // Get events for that class
        $events = DB::table('calendar_events')
            ->where('classId', $classId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($events);
    }
    public function npos()
    {
        app(\App\Services\ParticipationService::class)->award(auth()->id(), 'building_enter', 'npo');

        $npos = \App\Models\Npo::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('npos.index', compact('npos'));
    }
    public function nposDonate($slug)
    {
        $userId = Auth::id();

        if ($slug == 'animal-shelter') {
            $name = 'Animal Shelter';
            $accNO = '1234-5678-9012';
        } elseif ($slug == 'senior-care-home') {
            $name = 'Senior Care Home';
            $accNO = '2345-6789-0123';
        } elseif ($slug == 'childrens-orphanage') {
            $name = "Children's Orphanage";
            $accNO = '3456-7890-1234';
        } elseif ($slug == 'food-bank') {
            $name = 'Food Bank';
            $accNO = '4567-8901-2345';
        } elseif ($slug == 'local-library') {
            $name = 'Local Library';
            $accNO = '5678-9012-3456';
        } elseif ($slug == 'community-center') {
            $name = 'Community Center';
            $accNO = '6789-0123-4567';
        } elseif ($slug == 'ecofun') {
            $name = 'EcoFun';
            $accNO = '7890-1234-5678';
        } else {
            $name = 'Organization';
        }

        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $userId)
            ->first();

        return view('npos.donate', compact('slug', 'name', 'accNO', 'bankAccount'));
    }
    public function storeDonation(Request $request)
    {
        $user = auth()->user();
        $bank = BankAccount::where('student_id', auth()->id())->first();

        // Check PIN with strict comparison
        if (!$bank || (string) $bank->card_pin !== (string) $request->pin) {
            return response()->json([
                'status' => false
            ]);
        }
        try {
            $txn = DB::transaction(function () use ($user, $bank, $request) {
                // ✅ Authoritative balance check under row lock — prevents two
                // concurrent donation requests from both reading the same stale
                // balance and both passing the insufficient-funds check.
                $lastBalance = \App\Services\LedgerService::lockedBalance($user->id);

                if ($request->amount > $lastBalance) {
                    throw new \RuntimeException('INSUFFICIENT_BALANCE');
                }
                // First store donation
                $donation = Donation::create([
                    'user_id' => $user->id,
                    'npo_name' => $request->npo_name,
                    'account_no' => $request->account_no,
                    'amount' => $request->amount
                ]);

                // Generate transaction ID
                $txn = 'TXN-' . date('Y') . '-' . str_pad($donation->id, 6, '0', STR_PAD_LEFT);

                // Update record with transaction id
                $donation->transaction_ref = $txn;
                $donation->save();

                \App\Services\LedgerService::post(
                    $user->id,
                    'debit',
                    'Donation',
                    (float) $request->amount,
                    'Donation to ' . $request->npo_name,
                    [
                        'sid' => $user->sid,
                        'bank_account_id' => $bank->id,
                        'transaction_date' => now(),
                    ]
                );

                return $txn;
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance.'
                ]);
            }
            throw $e;
        }
        // Call Participation Logs
        app(ParticipationService::class)->award($user->id, 'donation', $request->npo_name);
        // Call Participation Logs
        return response()->json([
            'status' => true,
            'txn' => $txn
        ]);
    }
    public function high_spending_activities()
    {
        $user = Auth::user();

        // Daniel, 2026-10-21: "did you find all the activities for
        // Spending activities?" - this used to read calendar_events.
        // activityType, a generic field nothing ever actually wrote with
        // spending-activity intent, so no activity was ever really
        // assigned. Now resolves the real assignment the same way this
        // file's own educational_finance_department() already does above
        // (mba_position by activity_key + sid + cid).
        $position = DB::table('mba_position')
            ->where('activity_key', 'hba_position')
            ->where('sid', $user->sid)
            ->where('cid', $user->grade)
            ->first();

        $activity = $position && $position->activity_id
            ? \App\Models\Activity::find($position->activity_id)
            : null;

        return view('spendingActivities.high-spending-activities', compact('activity'));
    }
    // low_spending_activities() removed (Daniel, 2026-10-21: "remove low
    // spending activities from the system") - see bugs.md.
    //City Hall
    public function city_hall()
    {
        app(\App\Services\ParticipationService::class)->award(auth()->id(), 'building_enter', 'city_hall');
        return view('education.city-hall.city-hall');
    }
    public function main_hall()
    {
        $user = Auth::user();
        $gradeId = optional($user->gradeRelation)->grade_id;

        $cityName = CityProposal::current('name', $gradeId) ?? 'Zedville';
        $cityQuote = CityProposal::current('quote', $gradeId);

        return view('education.city-hall.main-hall', compact('cityName', 'cityQuote'));
    }
    public function civic_chamber()
    {
        $studentId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Referendums
        |--------------------------------------------------------------------------
        */

        $openReferendums = Referendum::visibleToStudent(auth()->user())->where('status', 'open')
            ->orderBy('id', 'DESC')
            ->get();

        $closedReferendums = Referendum::visibleToStudent(auth()->user())->where('status', 'closed')
            ->orderBy('id', 'DESC')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Petitions
        |--------------------------------------------------------------------------
        */

        // Only show approved petitions within 3 months (and student's own pending petitions)
        $threeMonthsAgo = now()->subMonths(3);

        // Everything is limited to the student's own school/class. Pending and
        // rejected petitions are only ever visible to the student who wrote
        // them (a rejected one may be exactly the content the Tutor blocked).
        $viewer = auth()->user();

        $activePetitions = Petition::inStudentScope($viewer)
            ->where('created_at', '>=', $threeMonthsAgo)
            ->where(function ($query) use ($studentId) {
                $query->where('status', 'approved')
                    ->orWhere(function ($q) use ($studentId) {
                        $q->where('status', 'pending')->where('created_by', $studentId);
                    });
            })
            ->orderBy('id', 'DESC')
            ->get();

        $pastPetitions = Petition::inStudentScope($viewer)
            ->where(function ($query) use ($studentId, $threeMonthsAgo) {
                $query->where('status', 'closed')
                    ->orWhere(function ($q) use ($threeMonthsAgo) {
                        $q->where('status', 'approved')->where('created_at', '<', $threeMonthsAgo);
                    })
                    ->orWhere(function ($q) use ($studentId) {
                        $q->where('status', 'rejected')->where('created_by', $studentId);
                    })
                    ->orWhere(function ($q) use ($studentId, $threeMonthsAgo) {
                        $q->where('status', 'pending')
                          ->where('created_by', $studentId)
                          ->where('created_at', '<', $threeMonthsAgo);
                    });
            })
            ->orderBy('id', 'DESC')
            ->get();

        $topic = request('topic'); // 'city-name' | 'city-quote' - deep-linked from main-hall.blade.php

        $myCityProposals = CityProposal::where('proposed_by', $studentId)
            ->where('status', 'pending')
            ->get()
            ->keyBy('type');

        return view(
            'education.city-hall.civic-chamber',
            compact(
                'studentId',
                'openReferendums',
                'closedReferendums',
                'activePetitions',
                'pastPetitions',
                'topic',
                'myCityProposals'
            )
        );
    }

    /**
     * Admin Roles & Permissions doc §2.4: students "Propose a city name and
     * city quote." One pending proposal per type per student at a time -
     * the Tutor approves/rejects via Admin\CivicChamberController, matching
     * the same submit-as-student / approve-as-staff split petitions use.
     */
    public function submitCityProposal(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:name,quote',
            'value' => 'required|string|max:255',
        ]);

        $student = Auth::user();
        $gradeId = optional($student->gradeRelation)->grade_id;
        abort_unless($gradeId, 422, 'You must be assigned to a Class before proposing a city name or quote.');

        $alreadyPending = CityProposal::where('proposed_by', $student->id)
            ->where('type', $data['type'])
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending proposal of this type.',
            ]);
        }

        CityProposal::create([
            'sid' => $student->sid,
            'grade_id' => $gradeId,
            'type' => $data['type'],
            'value' => $data['value'],
            'proposed_by' => $student->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proposal submitted - your Tutor will review it.',
        ]);
    }
    public function submitPetition(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'scope' => 'nullable|in:class,grade',
        ]);

        $student = Auth::user();
        $wholeGrade = $request->input('scope') === 'grade';
        $gradeId = optional($student->gradeRelation)->grade_id;

        Petition::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $student->id,
            'sid' => $student->sid,
            // "My class only" sets class_id; "whole Grade" leaves class_id
            // null and sets grade_id instead - see Petition::scopeInStudentScope.
            'class_id' => $wholeGrade ? null : $student->grade,
            'grade_id' => $gradeId,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Petition submitted successfully.'
        ]);
    }

    public function castVote(Request $request, $id)
    {
        $request->validate([
            'vote' => 'required|in:yes,no',
        ]);

        $user = Auth::user();

        // Enforce that only students (role 4) can vote on referendums
        if ($user->role != 4 && $user->role != '4') {
            return response()->json([
                'success' => false,
                'message' => 'Only students are eligible to vote in referendums.'
            ], 403);
        }

        $studentId = $user->id;

        // Must exist, be open, and be visible to this student's school/class.
        $referendum = Referendum::visibleToStudent($user)
            ->where('status', 'open')
            ->find($id);

        if (!$referendum) {
            return response()->json([
                'success' => false,
                'message' => 'This referendum is not available for voting.'
            ], 404);
        }

        $exists = ReferendumVote::where('referendum_id', $id)
            ->where('student_id', $studentId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already voted.'
            ]);
        }

        ReferendumVote::create([
            'referendum_id' => $id,
            'student_id' => $studentId,
            'vote' => $request->vote,
        ]);

        app(\App\Services\ParticipationService::class)->award($studentId, 'voting');

        return response()->json([
            'success' => true
        ]);
    }

    public function signPetition($id)
    {
        $student = Auth::user();

        if ($student->role != 4 && $student->role != '4') {
            return response()->json([
                'success' => false,
                'message' => 'Only students can sign petitions.'
            ], 403);
        }

        // Only approved petitions inside this student's own school/class can
        // be signed - pending ones are invisible to other students by design.
        $petition = Petition::inStudentScope($student)
            ->where('status', 'approved')
            ->find($id);

        if (!$petition) {
            return response()->json([
                'success' => false,
                'message' => 'This petition is not available to sign.'
            ], 404);
        }

        $studentId = $student->id;

        if ($petition->created_by == $studentId) {

            return response()->json([
                'success' => false,
                'message' => 'You cannot sign your own petition.'
            ]);

        }

        $signed = PetitionSignature::where('petition_id', $id)
            ->where('student_id', $studentId)
            ->exists();

        if ($signed) {

            return response()->json([
                'success' => false,
                'message' => 'Already signed.'
            ]);

        }

        PetitionSignature::create([
            'petition_id' => $id,
            'student_id' => $studentId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Petition signed successfully.'
        ]);
    }


    public function referendumResult($id)
    {
        // Results are only readable by people who may see the referendum itself.
        $viewer = auth()->user();
        $visible = $viewer->isStudent()
            ? Referendum::visibleToStudent($viewer)
            : ($viewer->isSuperAdmin() ? Referendum::query() : Referendum::where('sid', $viewer->sid));
        abort_unless($visible->where('id', $id)->exists(), 404);

        $yes = ReferendumVote::where('referendum_id', $id)
            ->where('vote', 'yes')
            ->count();

        $no = ReferendumVote::where('referendum_id', $id)
            ->where('vote', 'no')
            ->count();

        return response()->json([
            'yes' => $yes,
            'no' => $no,
            'total' => $yes + $no,
        ]);
    }
    public function well_being_room()
    {
        $featured = WellbeingPost::where('status', 1)
            ->where('featured', 1)
            ->first();

        $articles = WellbeingPost::where('status', 1)
            ->where('type', 'article')
            ->latest()
            ->get();

        $videos = WellbeingPost::where('status', 1)
            ->where('type', 'video')
            ->latest()
            ->get();

        return view('education.city-hall.well-being-room', compact(
            'featured',
            'articles',
            'videos'
        ));
    }
}