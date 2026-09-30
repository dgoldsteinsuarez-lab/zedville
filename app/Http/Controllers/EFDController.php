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


class EFDController extends Controller
{
    /*public function index()
 {
     $user = auth()->user();
     // Fetch all transactions
     $transactions = Transaction1::where('user_id', $user->id)
     ->whereMonth('created_at', date('m'))
     ->whereYear('created_at', date('Y'))
     ->orderBy('id')
     ->get();

     // Convert DB fields → your JSON template format
     $template = $transactions->map(function($t) {
        return [
                 'id'       => (string) $t->id,
                 'name'     => $t->description,
                 'amount'   => $t->type === 'debit'
                 ? - (float) $t->amount
                 : (float) $t->amount,
                 'category' => strtolower($t->category),
                 'type'     => $t->type === 'credit' ? 'income' : 'expense'
             ];
     });
     $templateJson = $template->toJson(JSON_PRETTY_PRINT);
     //return view('education.index', compact('user', 'templateJson'));
     return view('education.index', [
         'user' => $user,
         'template' => $template   // SEND ARRAY, NOT JSON STRING
     ]);
 }*/
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
        $lbaPosition = DB::table('mba_position')
            //->where('user_id', Auth::id())
            ->where('id', 4)   // ✅ Low Budget Activity
            ->where('sid', $user->sid)
            ->where('cid', $user->grade)
            ->first();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        $isBankAccountOpen = $bankAccount !== null;
        //Emmargency Fund
        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $emmsavingsPct = 20;
        $emmsavingsAmount = round($salaryamount * ($emmsavingsPct / 100), 2);
        $emmengercyfundintrest = $this->emmengercyfundintrest($user->id);
        $emmengercyfundtransactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->where('description', 'Auto Transfer to Savings Account')
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        //Select fin hero Task
        $taskActivities = DB::table('finhero_activity_registry')
            ->where('type', 'task')
            ->where('sid', $user->sid)
            ->where('cid', $user->grade)
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get();
        //Complease activity
        $completedActivities = DB::table('activity_completions')
            ->where('student_id', $user->id)
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->pluck('activity_type')
            ->toArray();
        return view('education.educational-finance-departmentex', compact('posters', 'mbaPosition', 'emmfundPosition', 'bankAccount', 'emmsavingsAmount', 'emmengercyfundintrest', 'emmengercyfundtransactions', 'isBankAccountOpen', 'hbaPosition', 'lbaPosition', 'taskActivities', 'completedActivities'));
    }
    public function emmengercyfundintrest($userId)
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
    public function storemba(Request $request)
    {
        $userId = Auth::id();

        // Validate that we receive a JSON object
        $request->validate([
            'budget' => 'required|array'
        ]);


        // Save or update the record for this user
        $mba = UserMba::updateOrCreate(
            ['user_id' => $userId],     // condition (update if exists)
            ['budget' => $request->budget] // data to save
        );
        //Cal Point under participation_logs
        $eventId = 1;
        app(ParticipationService::class)->handleActivity($userId, 'mba', $eventId);
        return response()->json([
            'status' => true,
            'message' => 'MBA budget saved successfully!',
            'data' => $mba
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
            ->get(['id', 'citizenId', 'name']);

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
            [$txn, $newBalance] = DB::transaction(function () use ($user, $bank, $request) {
                // ✅ Authoritative balance check under row lock — prevents two
                // concurrent donation requests from both reading the same stale
                // balance and both passing the insufficient-funds check.
                $lastBalance = \App\Services\BalanceService::lockedBalance($user->id);

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
                $newBalance = $lastBalance - $request->amount;
                //Store Trasection
                // Optionally, create a transaction record
                Transaction1::create([
                    'user_id' => $user->id,
                    'bank_account_id' => $bank->id,
                    'transaction_date' => now(), // ✅ current timestamp
                    'description' => 'Donation to ' . $request->npo_name, // optional descriptive text
                    'type' => 'debit',
                    'category' => 'Donation',
                    'amount' => $request->amount,
                    'balance' => $newBalance,
                ]);

                return [$txn, $newBalance];
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

        /*$activitys = DB::table('calendar_events')
            ->where('classId', $user->grade)
            ->select('activityType') // only ID
            ->first();*/
        $activitys = DB::table('calendar_events')
            ->where('classId', $user->grade)
            //->orderBy('id', 'desc') // latest ID first
            ->orderBy('created_at', 'desc')
            ->select('activityType')
            ->first();

        $activityType = $activitys->activityType ?? null;


        return view('spendingActivities.high-spending-activities', compact('activitys', 'activityType'));
    }
    public function low_spending_activities()
    {

        return view('spendingActivities.low-spending-activities');
    }
    //City Hall
    public function city_hall()
    {
        return view('education.city-hall.city-hall');
    }
    public function main_hall()
    {
        return view('education.city-hall.main-hall');
    }
    public function civic_chamber()
    {
        $studentId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Referendums
        |--------------------------------------------------------------------------
        */

        $openReferendums = Referendum::where('status', 'open')
            ->orderBy('id', 'DESC')
            ->get();

        $closedReferendums = Referendum::where('status', 'closed')
            ->orderBy('id', 'DESC')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Petitions
        |--------------------------------------------------------------------------
        */

        // Only show approved petitions within 3 months (and student's own pending petitions)
        $threeMonthsAgo = now()->subMonths(3);

        $activePetitions = Petition::where('status', 'approved')
            ->where('created_at', '>=', $threeMonthsAgo)
            ->orWhere(function ($query) use ($studentId, $threeMonthsAgo) {
                $query->where('status', 'pending')
                    ->where('created_by', $studentId)
                    ->where('created_at', '>=', $threeMonthsAgo);
            })
            ->orderBy('id', 'DESC')
            ->get();

        $pastPetitions = Petition::whereIn('status', [
            'rejected',
            'closed'
        ])
            ->orWhere('created_at', '<', $threeMonthsAgo)
            ->orderBy('id', 'DESC')
            ->get();

        return view(
            'education.city-hall.civic-chamber',
            compact(
                'studentId',
                'openReferendums',
                'closedReferendums',
                'activePetitions',
                'pastPetitions'
            )
        );
    }
    public function submitPetition(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        Petition::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => Auth::id(),
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

        return response()->json([
            'success' => true
        ]);
    }

    public function signPetition($id)
    {
        $petition = Petition::findOrFail($id);

        $studentId = Auth::id();

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