<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BankAccount;
use App\Services\StatementGenerator;
use App\Models\BankStatement;
use App\Models\ManagePayeeBiller;
use App\Models\AutoDebitRequest;
use App\Models\Transaction;
use App\Models\Transaction1;
use App\Models\Mailbox;
use App\Models\ConsumerSurvey;
class CitizenActivationController extends Controller
{
    /**
     * Step 1 - Welcome: the Mayor's announcement (Daniel, 2026-10-23). A
     * live city broadcast, the Mayor explaining the salary and the bills
     * it must cover, a first needs-vs-wants decision, the citizenship
     * letter to sign and the mission briefing - what Zedville is and what
     * it trains for real life. Personalised with the student's own name,
     * citizen number, street and salary.
     *
     * Shown until the bank account is open (so "Back" from that step can
     * replay it) and again any time after activation; mid-activation it
     * sends the student on to the step they're on.
     */
    public function index()
    {
        $user = Auth::user();
        $nextRoute = app(\App\Services\CitizenActivationService::class)->getNextRoute($user);

        if ($nextRoute && BankAccount::where('student_id', $user->id)->exists()) {
            return redirect($nextRoute);
        }

        $address = $user->isStudent() ? \App\Services\AddressService::assignForStudent($user)?->load('street') : null;
        $salary = ConsumerSurvey::salaryFor($user->id);
        // The fixed bills every citizen has (same as the simulated first
        // bank statement in the Needs, Wants & Savings Match).
        $bills = ['rent' => 300.00, 'school_fees' => 1000.00, 'electricity' => 86.00, 'water' => 59.00, 'internet' => 20.00];

        return view('citizen-activation.index', [
            'user' => $user,
            'firstName' => \Illuminate\Support\Str::before(trim((string) ($user->nickname ?: $user->name)), ' ') ?: $user->name,
            'citizenId' => $user->citizenId ?: str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'address' => $address ? $address->house_number . ' ' . $address->street->name : null,
            'salary' => $salary,
            'bills' => $bills,
            'leftAfterBills' => $salary - array_sum($bills),
        ]);
    }

    /** The student accepted the mission at the end of the welcome. */
    public function acceptWelcome()
    {
        $user = Auth::user();

        if (!$user->welcome_seen_at) {
            $user->forceFill(['welcome_seen_at' => now()])->save();
        }

        return redirect(app(\App\Services\CitizenActivationService::class)->getNextRoute($user) ?? route('dashboard'));
    }

    /**
     * Step 2 - Bank Account
     */
    public function bankAccount()
    {
        if ($redirect = $this->redirectIfCompleted()) {
            return $redirect;
        }
        $user = Auth::user();
        // Daniel, 2026-10-25: the address field was empty - every student
        // already has a street and number in their Grade's city, so it is
        // filled in (like the Citizen ID) instead of typed.
        $cityAddress = $this->cityAddressFor($user);

        return view('citizen-activation.bank-account', compact('user', 'cityAddress'));
    }

    /** The student's Zedville address, e.g. "1 Adam Smith Avenue, Zedville" (null without a Class). */
    private function cityAddressFor($user): ?string
    {
        return $user->isStudent()
            ? \App\Services\AddressService::assignForStudent($user)?->load('street')->label()
            : null;
    }
    //Post Bank account
    public function store(Request $request, StatementGenerator $generator)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->withErrors(__('citizen.flash.login_required'));
        }

        //$salary=3952.40; // Set your salary amount here
        $salary = 0.00; // Set your salary amount here
        $emergencyFund = $salary * 0.20;
        $moneyMarketFund = $salary * 0.30;
        $validated = $request->validate([
            'email' => 'required|email|unique:bank_accounts,student_email',
            'homeAddress' => 'nullable|string|max:255',
            'rcvaccemail' => 'nullable|string',
            'tandc' => 'nullable|string',
        ]);
        // The city address always wins over what the form sent (the field is
        // read-only); the typed one is only for a student with no Class yet.
        $validated['homeAddress'] = $this->cityAddressFor($user) ?? ($validated['homeAddress'] ?? null);
        // ✅ Update user address if not already set
        if (empty($user->address) && !empty($validated['homeAddress'])) {
            $user->address = $validated['homeAddress'];
            $user->save();
        }
        $data = [
            'student_id' => $user->id,
            'sid' => $user->sid,
            // Daniel, 2026-10-19: he already typed his real name at
            // registration - asking again here just wastes time and risks
            // the bank account ending up with a different name than the
            // account itself. Always use the registered name; a typo gets
            // fixed in Settings (profile.update), not re-typed here.
            'student_name' => $user->name,
            //'student_dob' => Carbon::parse($validated['dob'])->format('Y-m-d'),
            'student_email' => $validated['email'],
            //'student_phone' => $validated['phone'],
            'student_cityzen_id' => $user->citizenId,
            'student_address' => $validated['homeAddress'],
            //'student_prefered_name' => $validated['AccPreName'],

            'bank_name' => 'Universal Bank',
            'primary_savings_account' => 'Primary Savings Account',
            // Was '123000-' . 6 digits (13 chars) - always overflowed the
            // column (varchar(10)), so no student could ever open a bank
            // account. Matches the same 10-digit pattern the emergency
            // fund / money market account numbers already use correctly.
            'primary_savings_account_number' => '123000-' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'primary_savings_account_amount' => $salary,

            'emergency_fund_account' => 'Emergency Fund Account',
            'emergency_fund_account_number' => str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'emergency_fund_account_amount' => 0.00,

            'money_market_account' => 'Money Market Account',
            'money_market_account_number' => str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'money_market_account_amount' => 0.00,

            'card_name' => 'Virtual Debit Card',
            'card_type' => 'DEBIT',
            'card_number' => '2000' . str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
            'card_valid' => now()->addYear()->format('Y-m-d'),
            'card_cvv' => str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT),
            /*'card_iban' => 'UB' . mt_rand(10, 99) . ' UNIB ' .
                str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT) . ' ' .
                str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT) . ' ' .
                str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT) . ' ' .
                str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT),*/
            'card_iban' => 'ZV' . str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),

            'card_swift' => 'UNIB' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4)),
            'card_pin' => implode('', array_map(fn() => mt_rand(1, 9), range(1, 4))),
            'student_accountstatement' => $validated['rcvaccemail'] ?? null,
            'student_trem' => $validated['tandc'] ?? null,
        ];

        $bankAccount = BankAccount::create($data);

        return redirect()->route('CitizenActivation.salaryAuthorization')->with('success', __('citizen.flash.bank_created'));
    }
    /**
     * Step 3 - Salary Authorization
     */
    public function salaryAuthorization()
    {
        if ($redirect = $this->redirectIfCompleted()) {
            return $redirect;
        }
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('citizen-activation.salary-authorization', compact('user', 'bankAccount'));
    }
    //Post salary
    public function authorize_direct_deposit(Request $request, StatementGenerator $generator)
    {
        $user = Auth::user();
        // 💰 Update bank account amount
        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        if ($bankAccount) {
            $salary = config('zedville.monthly_salary', 3952.40);// or dynamic amount

            $bankAccount->update([
                'primary_savings_account_amount' => $salary
            ]);
        }

        // 📄 Generate bank statement AFTER balance update
        $generator->generateForUser($user->id);
        /*$timezone = $request->input('timezone', 'UTC');
        $content = view('mailbox_templates.auto-debit', ['user' => $user])->render();

        Mailbox::create([
            'student_id' => $user->id,
            'subject' => 'Simplify Bills with Auto-Debit',
            'content' => $content,
            'type' => 'primary',
            'read' => 0,
            'created_at' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
        ]);*/
        return redirect()
            ->route('CitizenActivation.autoDebit')
            ->with('success', __('citizen.flash.salary_created'));
    }
    /**
     * Step 4 - Auto Debit
     */
    public function autoDebit()
    {
        if ($redirect = $this->redirectIfCompleted()) {
            return $redirect;
        }

        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        $billers = ManagePayeeBiller::whereIn('name', [
            'City School',
            'Internet Service Provider',
            'Utility - Electricity',
            'Utility - Water'
        ])->where('status', 1)->get()->keyBy('id');
        return view('citizen-activation.auto-debit', compact('user', 'bankAccount', 'billers'));
    }
    //Post auto debit
    public function store_auto_debit_authorization_form(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'services' => 'required|array|min:1',
            'bankaccountnumber' => 'required',
        ]);

        // 'fullname'/'signature' below use $user->name (real name), not
        // nickname - same reasoning as the Bank Account "Full Name" field
        // (bugs.md, 2026-10-19): this is a legal authorization document,
        // and nickname can be null for an admin-staged account that logged
        // in directly without ever claiming/registering, which crashed this
        // insert outright ('fullname' NOT NULL).

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ SAVE AUTO DEBIT AUTHORIZATION (NO CHANGE)
        |--------------------------------------------------------------------------
        */
        // The address lives on the bank account, not the user ($user->student_address
        // never exists - every auto-debit was saved without one, found 2026-10-25).
        $serviceAddress = BankAccount::where('student_id', $user->id)->value('student_address') ?: $user->address;

        foreach ($request->services as $key => $accountNumber) {

            $meta = $request->services_meta[$key] ?? null;
            if (!$meta)
                continue;

            $alreadySubmitted = AutoDebitRequest::where('user_id', $user->id)
                ->where('type', $meta['name'])
                ->exists();

            if ($alreadySubmitted) {
                return redirect()
                    ->back()
                    ->with('error', __('citizen.flash.auto_debit_exists'));
            }

            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => $meta['name'],
                'accountno' => $meta['account'],
                'fullname' => $user->name,
                'email' => $user->email,
                'serviceaddress' => $serviceAddress,
                'bankname' => $request->bankname,
                'bankaccountnumber' => $request->bankaccountnumber,
                'amount' => $meta['amount'] ?? 0,
                'startDate' => now(),
                'signature' => $user->name,
                'date' => now(),
                'tandagree' => 1,
            ]);
        }
        // ✅ FORCE ADD RENT (MANDATORY – NO CHECKBOX)
        $rentExists = AutoDebitRequest::where('user_id', $user->id)
            ->where('type', 'Rent')
            ->exists();

        if (!$rentExists) {
            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => 'Rent',
                'accountno' => 'RENT-AUTO', // static / dummy
                'fullname' => $user->name,
                'email' => $user->email,
                'serviceaddress' => $serviceAddress,
                'bankname' => $request->bankname,
                'bankaccountnumber' => $request->bankaccountnumber,
                'amount' => config('zedville.rent', 300), // fixed rent
                'startDate' => now(),
                'signature' => $user->name,
                'date' => now(),
                'tandagree' => 1,
            ]);
        }
        $schoolExists = AutoDebitRequest::where('user_id', $user->id)
            ->where('type', 'City School')
            ->exists();

        if (!$schoolExists) {
            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => 'City School',
                'accountno' => $billers[3]->account_number ?? 'SCHOOL-AUTO',
                'fullname' => $user->name,
                'email' => $user->email,
                'serviceaddress' => $serviceAddress,
                'bankname' => $request->bankname,
                'bankaccountnumber' => $request->bankaccountnumber,
                'amount' => config('zedville.school_fees', 1000),
                'startDate' => now(),
                'signature' => $user->name,
                'date' => now(),
                'tandagree' => 1,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ FIXED TRANSACTIONS MASTER LIST
        |--------------------------------------------------------------------------
        */
        $fixedTransactions = [
            'Utility - Electricity' => rand(60, 90),
            'Utility - Water' => rand(30, 60),
            'Internet' => 20,
            'Rent' => config('zedville.rent', 300),
            'School Fees' => config('zedville.school_fees', 1000),
        ];

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ BUILD FINAL TRANSACTION LIST
        |--------------------------------------------------------------------------
        | - Rent → ALWAYS
        | - School Fees → ALWAYS
        | - Others → ONLY if selected
        */
        $selectedDescriptions = [];

        // Mandatory always
        $selectedDescriptions[] = 'Rent';
        $selectedDescriptions[] = 'School Fees';

        // Map selected services
        foreach ($request->services_meta as $meta) {
            if (!empty($meta['name'])) {
                $selectedDescriptions[] = $meta['name'];
            }
        }

        // Remove duplicates
        $selectedDescriptions = array_unique($selectedDescriptions);

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ CREATE TRANSACTIONS
        |--------------------------------------------------------------------------
        */
        $salaryDate = now();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        foreach ($selectedDescriptions as $description) {

            if (!isset($fixedTransactions[$description])) {
                continue;
            }

            // Once a month, like BankController::ensureMonthlyBills (it checked
            // today only: done after the 7th, the month's bills - already charged
            // by the nightly run - were charged a second time). Locked, through
            // the ledger, so the running balance is right.
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $bankAccount, $description, $fixedTransactions, $salaryDate) {
                if ($bankAccount) {
                    BankAccount::whereKey($bankAccount->id)->lockForUpdate()->first();
                }
                $exists = Transaction1::where('user_id', $user->id)
                    ->where('description', $description)
                    ->whereYear('transaction_date', $salaryDate->year)
                    ->whereMonth('transaction_date', $salaryDate->month)
                    ->exists();

                if ($exists) {
                    return;
                }

                \App\Services\LedgerService::post($user->id, 'debit', 'Needs', (float) $fixedTransactions[$description], $description, [
                    'sid' => $user->sid,
                    'bank_account_id' => $bankAccount->id ?? null,
                    'transaction_date' => $salaryDate,
                    // not enough money: nothing now, the month's bills try again from the 7th
                    'only_if_enough' => true,
                ]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ REDIRECT
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('CitizenActivation.consumerProfile')
            ->with('success', __('citizen.flash.auto_debit_done'));
    }
    /**
     * Step 5 - Consumer Profile
     */
    public function consumerProfile()
    {
        if ($redirect = $this->redirectIfCompleted()) {
            return $redirect;
        }

        $user = Auth::user();

        return view('citizen-activation.consumer-profile', compact('user'));
    }

    /**
     * "Skip for now" on Step 5. The survey itself saves through
     * ConsumerSurveyController::storesurvey (surveys.js) and then moves on to
     * Step 6 by itself; this is for a student who doesn't answer it. Creates
     * a zero-cost record (no groceries/lunch/transport/etc, so no balance is
     * touched) to satisfy CitizenActivationService::getNextRoute()'s gate.
     */
    public function skipConsumerProfile()
    {
        $user = Auth::user();

        ConsumerSurvey::firstOrCreate(
            ['user_id' => $user->id, 'survey_month' => Carbon::now()->format('M-Y')],
            [
                'sid' => $user->sid,
                'diet' => null,
                'groceries' => [],
                'basket_cost' => 0,
                'lunch' => 0,
                'transport' => 0,
                'activities' => 0,
                'restaurants' => 0,
                'total_cost' => 0,
            ]
        );

        return redirect()
            ->route('CitizenActivation.activationComplete')
            ->with('success', __('citizen.flash.profile_skipped'));
    }
    //Post comsure profile
    /**
     * Step 6 - Activation Complete
     */
    public function activationComplete()
    {
        if ($redirect = $this->redirectIfCompleted()) {
            return $redirect;
        }
        $user = Auth::user();
        // The page used to show the same sample numbers to every student.
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        $survey = ConsumerSurvey::where('user_id', $user->id)->latest('id')->first();

        return view('citizen-activation.activation-complete', compact('user', 'bankAccount', 'survey'));
    }
    protected function redirectIfCompleted()
    {
        $service = app(\App\Services\CitizenActivationService::class);

        $nextRoute = $service->getNextRoute(auth()->user());

        if (!$nextRoute) {
            return null;
        }

        if (url()->current() !== $nextRoute) {
            return redirect($nextRoute);
        }

        return null;
    }
}