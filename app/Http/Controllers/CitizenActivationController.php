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
class CitizenActivationController extends Controller
{
    /**
     * Step 1 - Welcome
     */
    public function index()
    {
        if ($redirect = $this->redirectIfCompleted()) {
            return $redirect;
        }
        $user = Auth::user();

        return view('citizen-activation.index', compact('user'));
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

        return view('citizen-activation.bank-account', compact('user'));
    }
    //Post Bank account
    public function store(Request $request, StatementGenerator $generator)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->withErrors('You must be logged in.');
        }

        //$salary=3952.40; // Set your salary amount here
        $salary = 0.00; // Set your salary amount here
        $emergencyFund = $salary * 0.20;
        $moneyMarketFund = $salary * 0.30;
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:bank_accounts,student_email',
            'homeAddress' => 'nullable|string|max:255',
            'rcvaccemail' => 'nullable|string',
            'tandc' => 'nullable|string',
        ]);
        // ✅ Update user address if not already set
        if (empty($user->address) && !empty($validated['homeAddress'])) {
            $user->address = $validated['homeAddress'];
            $user->save();
        }
        $data = [
            'student_id' => $user->id,
            'sid' => $user->sid,
            'student_name' => $validated['fullName'],
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

        return redirect()->route('CitizenActivation.salaryAuthorization')->with('success', 'Bank account created successfully!');
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
            ->with('success', 'Salary Created Sucessfully');
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

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ SAVE AUTO DEBIT AUTHORIZATION (NO CHANGE)
        |--------------------------------------------------------------------------
        */
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
                    ->with('error', 'You have already submitted auto-debit for one or more services.');
            }

            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => $meta['name'],
                'accountno' => $meta['account'],
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $user->student_address,
                'bankname' => $request->bankname,
                'backaccountnumber' => $request->bankaccountnumber,
                'amount' => $meta['amount'] ?? 0,
                'startDate' => now(),
                'signature' => $user->nickname,
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
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $user->student_address,
                'bankname' => $request->bankname,
                'backaccountnumber' => $request->bankaccountnumber,
                'amount' => config('zedville.rent', 300), // fixed rent
                'startDate' => now(),
                'signature' => $user->nickname,
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
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $user->student_address,
                'bankname' => $request->bankname,
                'backaccountnumber' => $request->bankaccountnumber,
                'amount' => config('zedville.school_fees', 1000),
                'startDate' => now(),
                'signature' => $user->nickname,
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

            $exists = Transaction1::where('user_id', $user->id)
                ->where('description', $description)
                ->whereDate('transaction_date', $salaryDate->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            $latestTxn = Transaction1::where('user_id', $user->id)
                ->latest('id')
                ->first();

            $lastBalance = $latestTxn ? $latestTxn->balance : 0;
            $amount = $fixedTransactions[$description];
            $newBalance = max(0, $lastBalance - $amount);

            Transaction1::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => $salaryDate,
                'description' => $description,
                'type' => 'debit',
                'category' => 'Needs',
                'amount' => $amount,
                'balance' => $newBalance,
                'is_penalty' => false,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ REDIRECT
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('CitizenActivation.consumerProfile')
            ->with('success', 'Congratulations, your auto debit is set up. Now complete the survey.');
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

        return view('citizen-activation.activation-complete', compact('user'));
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