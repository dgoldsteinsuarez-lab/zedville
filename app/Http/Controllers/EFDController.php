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
use App\Support\Deadline;


class EFDController extends Controller
{
    /**
     * Needs, Wants & Savings Match: the student sorts last month's bank
     * statement into Income / Needs / Wants / Savings.
     *
     * Daniel, 2026-10-22: a student in their first month (or who joined
     * later in the school year) has no statement for last month yet, so
     * they get a simulated typical Zedville month instead, with a note
     * that it's a simulation because they just opened their account.
     */
    public function index()
    {
        $user = auth()->user();

        // last month, counted from the 1st (on the 31st subMonth() gave this month)
        $start = now()->startOfMonth()->subMonth();
        $end = $start->copy()->endOfMonth();

        $transactions = Transaction1::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->where('amount', '!=', 0)   // "Autopilot - [Month]" history lines move no money
            ->orderBy('id', 'desc')
            ->get();
        $template = collect();

        /* ===============================
           PENALTY (SUM ALL)
        =============================== */
        $penaltyTransactions = $transactions->filter(function ($t) {
            return strtolower($t->category) === 'penalty';
        });

        // The month-end penalty is one "Entertainment" line (Daniel,
        // 2026-10-22); penalties from before that were one line per item.
        if ($penaltyTransactions->isNotEmpty()) {
            $penaltyTotal = $penaltyTransactions->sum(function ($t) {
                return $t->type === 'debit'
                    ? -(float) $t->amount
                    : (float) $t->amount;
            });

            $template->push([
                'id' => 'penalty',
                'name' => txn_text($penaltyTransactions->count() === 1 ? $penaltyTransactions->first()->description : 'Entertainment'),
                'amount' => $penaltyTotal,
                'category' => 'wants',
                'type' => 'expense',
            ]);
        }

        // Orders placed before one-line-per-order have one line per item
        // ("Purchase: Chicken (Order #4)"): each order is one line, named like
        // new ones - "Compra en el supermercado", "Compra: fiesta de
        // cumpleaños" (App\Support\OrderLine, Daniel 2026-11-02).
        $transactions = \App\Support\OrderLine::fold($transactions);

        /* ===============================
           ALL OTHER TRANSACTIONS
        =============================== */
        // Not part of the budget to sort (bank review, 2026-10-29): money
        // to or from classmates, money coming back from the student's own
        // savings, and an admin's corrections. Moving money into savings is
        // sorted as savings (budgetCategory) - it counted as a "want".
        // A fine for an activity not done in time (ActivityFineService) is neither: not sorted either.
        $notBudget = ['penalty', 'fund transfer', 'reversal', 'admin adjustment', 'fine'];
        $transactions
            ->filter(function ($t) use ($notBudget) {
                $category = strtolower((string) $t->category);

                return !in_array($category, $notBudget, true) && !($category === 'own transfer' && $t->type === 'credit');
            })
            ->each(function ($t) use ($template) {
                $template->push([
                    'id' => (string) ($t->order_key ?? $t->id),
                    'name' => txn_text($t->description),
                    'amount' => $t->type === 'debit'
                        ? -(float) $t->amount
                        : (float) $t->amount,
                    'category' => $t->type === 'credit' ? 'income' : self::budgetCategory($t->category, $t->description),
                    'type' => $t->type === 'credit' ? 'income' : 'expense',
                ]);
            });

        $simulated = $template->isEmpty();
        if ($simulated) {
            $template = $this->simulatedStatement($user);
        }

        return view('education.index', [
            'user' => $user,
            'template' => $template->values(),
            'simulated' => $simulated,
        ]);
    }

    /**
     * Which budget column a spending line belongs in. Shop purchases carry
     * the product's own category ("Sports", "Music Instruments"...), so
     * anything that isn't a need or a saving is a want.
     */
    public static function budgetCategory(?string $category, ?string $description = ''): string
    {
        $category = strtolower(trim((string) $category));
        $description = strtolower((string) $description);

        if (in_array($category, ['savings', 'own transfer'], true) || str_contains($category, 'emergency') || str_contains($description, 'savings account')) {
            return 'savings';
        }
        if ($category === 'bill payment') {
            return 'needs';
        }

        $needs = ['needs', 'groceries', 'food', 'personal care & household', 'household', 'health', 'transport', 'utilities', 'rent', 'education'];
        if (in_array($category, $needs, true)) {
            return 'needs';
        }
        foreach (['rent', 'school', 'utility', 'internet', 'electricity', 'water'] as $keyword) {
            if (str_contains($description, $keyword)) {
                return 'needs';
            }
        }

        return 'wants';
    }

    /**
     * A typical Zedville month for a student with no statement yet: their
     * own salary (or the city's base salary), the fixed bills every citizen
     * has, a few everyday needs and wants, and the 20% that goes to the
     * Emergency Fund. Names come from lang/<language>/mba.php.
     */
    private function simulatedStatement($user)
    {
        $salary = (float) (Transaction1::where('user_id', $user->id)->where('category', 'Income')->where('type', 'credit')
            ->latest('id')->value('amount') ?: config('zedville.monthly_salary', 3952.40));

        // Mixed order, like a real statement - grouped by column it would
        // give the answers away.
        $lines = [
            ['salary', $salary, 'income'],
            ['rent', -300.00, 'needs'],
            ['cinema', -25.00, 'wants'],
            ['groceries', -210.00, 'needs'],
            ['emergency_fund', -round($salary * 0.20, 2), 'savings'],
            ['video_game', -60.00, 'wants'],
            ['internet', -20.00, 'needs'],
            ['school_fees', -1000.00, 'needs'],
            ['restaurant', -45.00, 'wants'],
            ['electricity', -86.00, 'needs'],
            ['bus_pass', -48.00, 'needs'],
            ['basketball', -70.00, 'wants'],
            ['water', -59.00, 'needs'],
            ['household', -15.00, 'needs'],
        ];

        return collect($lines)->map(fn ($line) => [
            'id' => 'sim-' . $line[0],
            'name' => __('mba.sample.' . $line[0]),
            'amount' => $line[1],
            'category' => $line[2],
            'type' => $line[2] === 'income' ? 'income' : 'expense',
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
        // On the board only while it runs, with its days left (Daniel, 2026-11-03:
        // "reset all the activities that is showing when the deadline already ended").
        $running = fn ($row) => $row && Deadline::running($row->start_date, $row->end_date) ? $row : null;
        $mbaPosition = $running($mbaPosition);
        $emmfundPosition = $running($emmfundPosition);
        // «Resuelve tu imprevisto», the student's own, Required, with its 30 days (App\Services\EmergencyService)
        $emergency = \App\Services\EmergencyService::open($user->id);
        // the monthly donation (Daniel, 2026-11-08): on the board until the standing order is set up
        $donationPosition = \App\Services\DonationService::active($user->id) ? null : $running(DB::table('mba_position')
            ->where('activity_key', \App\Services\DonationService::KEY)->where('sid', $user->sid)->where('cid', $user->grade)->first());
        //Check Event Avilable or not
        $today = Carbon::today();
        $hasHBAActivity = DB::table('calendar_events')
            ->where('activitys', 1)
            ->where('classId', $user->grade) // ✅ class filter
            ->whereDate('end', '>=', $today)   // ✅ not expired
            ->exists();
        $hbaPosition = null;
        $lbaPosition = null;
        if ($hasHBAActivity) {
            // and the Low Spending one (Daniel, 2026-10-31)
            $lbaPosition = $running(DB::table('mba_position')->where('activity_key', 'lba_position')
                ->where('sid', $user->sid)->where('cid', $user->grade)->first());
            //Check Event Avilable or not
            $hbaPosition = $running(DB::table('mba_position')
                //->where('user_id', Auth::id())
                //->where('id', 3)   // ✅ High Budget Activity
                ->where('activity_key', 'hba_position')
                ->where('sid', $user->sid)
                ->where('cid', $user->grade)
                ->first());
        }
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        $isBankAccountOpen = $bankAccount !== null;
        //Emmargency Fund
        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $emmsavingsPct = 20;
        $emmsavingsAmount = round($salaryamount * ($emmsavingsPct / 100), 2);
        // The fund's own interest and movements (bank review, 2026-10-29: this read
        // the current account's balance from the opening line, and listed only
        // the opening deposit - not the monthly 20%, moves in and out, interest).
        $emergencyFundInterest = app(\App\Http\Controllers\SavingsAccountController::class)->emergencyFundInterest($user->id);
        $emmengercyfundtransactions = \App\Models\SavingsTransaction::where('user_id', $user->id)
            ->where('account', 'emergency')
            ->latest('id')
            ->take(10)
            ->get();
        $efGoals = \App\Support\EmergencyFundGoal::for($user->id, (float) optional($bankAccount)->emergency_fund_account_amount);
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
            ->get()
            ->filter(fn ($task) => Deadline::running($task->start_date, $task->end_date))
            ->values();
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
            ->get()
            ->filter(fn ($library) => Deadline::running($library->start_date, $library->end_date))
            ->values();
        //Complease activity
        $completedActivities = DB::table('activity_completions')
            ->where('student_id', $user->id)
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->pluck('activity_type')
            ->toArray();
        $libraryModalModules = $this->libraryModalModules($libraryActivities->pluck('activity_key')->all(), $completedActivities);
        // Easy activities (Engagement) running for the class, not done yet.
        $easyActivities = app(\App\Services\EasyActivityService::class)->forStudent($user)->reject(fn ($activity) => $activity->done);
        // The Library's level badge: the student's real Level (Rookie,
        // Intermediate...), same lookup as the Academy heading.
        $libraryLevel = optional(optional(optional($user->gradeRelation)->grade)->level)->name;
        // what a Required activity not done in time costs (App\Services\ActivityFineService)
        $fineAmount = \App\Models\ActivityFineSetting::current()->amount;
        return view('education.educational-finance-departmentex', compact('posters', 'mbaPosition', 'emmfundPosition', 'emergency', 'donationPosition','bankAccount', 'emmsavingsAmount', 'emergencyFundInterest', 'emmengercyfundtransactions', 'isBankAccountOpen', 'hbaPosition', 'lbaPosition', 'taskActivities', 'libraryActivities', 'libraryModalModules', 'completedActivities', 'libraryLevel', 'easyActivities', 'efGoals', 'fineAmount'));
    }

    /**
     * The Library pop-up's data, in the shape the old hardcoded
     * public/asset/front/js/modules.js had, plus question/option ids so
     * "Check Answers" can send the answers to the server, and whether the
     * module is assigned to this student (only assigned modules earn
     * points), and whether it was already completed this month. The pop-up
     * writes these strings into innerHTML, so every piece of text is
     * HTML-escaped here.
     */
    private function libraryModalModules(array $assignedKeys, array $completedActivities = []): array
    {
        $subtopic = fn (array $section) => [
            'title' => e(ct('library', $section['title'] ?? '')),
            'text' => array_map(fn ($line) => e(ct('library', $line)), $section['lines'] ?? []),
        ];

        return \App\Models\FinheroLibraryModule::with('questions.options')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($module) => [
                'id' => $module->id,
                'activity_key' => $module->module_key,
                'assigned' => in_array($module->module_key, $assignedKeys, true),
                'done' => in_array('library_' . $module->module_key, $completedActivities, true),
                'title' => e(ct('library', $module->title)),
                'icon' => \App\Support\ModuleIcon::html($module->icon), // an emoji, escaped, or a drawn icon
                'color' => 'from-emerald-400 to-teal-500',
                'content' => [
                    'subtopic1' => $subtopic($module->lesson[0] ?? []),
                    'subtopic2' => $subtopic($module->lesson[1] ?? []),
                    'questions' => $module->questions->map(fn ($question) => [
                        'qid' => $question->id,
                        'q' => e(ct('library', $question->question_text)),
                        'options' => $question->options->map(fn ($option) => e(ct('library', $option->option_text)))->values()->all(),
                        'option_ids' => $question->options->pluck('id')->values()->all(),
                        'correct' => $question->options->search(fn ($option) => $option->is_correct),
                    ])->values()->all(),
                ],
            ])
            ->values()
            ->all();
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
     * point - never zero for actually finishing the activity. The top
     * score is the "Needs, Wants & Savings Match" row in Admin > FinHero
     * Point Defaults (3 unless changed there); each tier is one less, never
     * below 1.
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
            $top = (int) (\App\Models\FinheroActivityDefault::where('activity_key', 'needs_wants_savings_match')->value('max_points') ?: 3);
            $pointsAwarded = max(1, $mistakes === 0 ? $top : ($mistakes <= 2 ? $top - 1 : $top - 2));
            app(FinheroPointService::class)->addPoints($userId, 'activity', 'needs_wants_savings_match', $pointsAwarded);
        }

        return response()->json([
            'status' => true,
            'message' => __('mba.saved'),
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
        $startOfMonth = now()->startOfMonth()->subMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

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
            ->get(['id', 'citizenId', 'nickname', 'name']);

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
        // the Intermediate's new prices, for the page only (App\Services\PriceRise)
        \App\Services\PriceRise::reprice($user, $products, 'products');

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
    /**
     * The student calendar, option A (Daniel, 2026-11-04: "Make option A for the
     * calendar"): month, week and day without hours or scrolling, from the
     * student's own activities (App\Support\StudentCalendar). "Today" is the
     * school's day, in Berlin time, like the salary and the deadlines.
     */
    public function calendar()
    {
        return view('calendar.index', [
            'calendar' => [
                'locale' => substr(app()->getLocale(), 0, 2),
                'today' => Carbon::now('Europe/Berlin')->toDateString(),
                'activities' => \App\Support\StudentCalendar::activities(auth()->user()),
            ],
            'fineAmount' => \App\Models\ActivityFineSetting::current()->amount,
            'hybrid' => \App\Support\Hybrid::on(),
        ]);
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
            ->get()
            ->map(function ($event) {
                $event->description = \App\Support\CalendarTitle::descriptionForStudent($event->title, $event->description);
                $event->title = \App\Support\CalendarTitle::forStudent($event->title);
                return $event;
            });

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

        // Was a hard-coded list of the first 7 NPOs - any NPO an admin added
        // later crashed this page (undefined $accNO). Read it from the table.
        $npo = \App\Models\Npo::where('slug', $slug)->where('status', 1)->firstOrFail();
        $name = $npo->name;
        $accNO = trim((string) $npo->account_number);

        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $userId)
            ->first();

        return view('npos.donate', compact('slug', 'name', 'accNO', 'bankAccount', 'npo'));
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
        // A zero or negative amount would be a debit of nothing - or, if
        // negative, would add money to the account.
        if (!is_numeric($request->amount) || (float) $request->amount <= 0) {
            return response()->json([
                'status' => false,
                'message' => __('npos.server.invalid_amount'),
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
                    'message' => __('npos.server.insufficient_balance')
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
        // High and Low Spending, one of each (Daniel, 2026-10-31)
        $assigned = collect(\App\Models\Activity::SLOTS)->map(function ($slot) use ($user) {
            $position = DB::table('mba_position')->where('activity_key', $slot[0])
                ->where('sid', $user->sid)->where('cid', $user->grade)->first();

            return $position && $position->activity_id ? \App\Models\Activity::find($position->activity_id) : null;
        })->filter()->values();
        $activity = $assigned->first();

        return view('spendingActivities.high-spending-activities', compact('activity', 'assigned'));
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
        abort_unless($gradeId, 422, __('cityhall.server.no_class'));

        $alreadyPending = CityProposal::where('proposed_by', $student->id)
            ->where('type', $data['type'])
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return response()->json([
                'success' => false,
                'message' => __('cityhall.server.proposal_pending'),
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
            'message' => __('cityhall.server.proposal_submitted'),
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
            'message' => __('cityhall.server.petition_submitted')
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
                'message' => __('cityhall.server.only_students_vote')
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
                'message' => __('cityhall.server.referendum_unavailable')
            ], 404);
        }

        $exists = ReferendumVote::where('referendum_id', $id)
            ->where('student_id', $studentId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('cityhall.server.already_voted')
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
                'message' => __('cityhall.server.only_students_sign')
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
                'message' => __('cityhall.server.petition_unavailable')
            ], 404);
        }

        $studentId = $student->id;

        if ($petition->created_by == $studentId) {

            return response()->json([
                'success' => false,
                'message' => __('cityhall.server.own_petition')
            ]);

        }

        $signed = PetitionSignature::where('petition_id', $id)
            ->where('student_id', $studentId)
            ->exists();

        if ($signed) {

            return response()->json([
                'success' => false,
                'message' => __('cityhall.server.already_signed')
            ]);

        }

        PetitionSignature::create([
            'petition_id' => $id,
            'student_id' => $studentId,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('cityhall.server.petition_signed')
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