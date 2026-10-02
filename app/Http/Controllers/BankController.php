<?php

namespace App\Http\Controllers;
use App\Jobs\ProcessScheduledTransfer;
use App\Jobs\ProcessScheduledBillPayment;
use App\Services\ParticipationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Mailbox;
use App\Models\BankAccount;
use App\Models\DirectDeposite;
use App\Models\AutoDebitRequest;
use App\Models\Transaction;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\Beneficiary;
use App\Models\PayBill;
use Illuminate\Http\Request;
use App\Helpers\MailboxScheduler;
use Illuminate\Support\Facades\Auth;
use App\Services\StatementGenerator;
use App\Models\BankStatement;
use App\Models\ManagePayeeBiller;


class BankController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // Check if this student already has a bank account
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return view('bank.bank', compact('user'));
        }


        // ❌ 2. Bank account created BUT primary savings account balance is ZERO
        if ($bankAccount->primary_savings_account_amount <= 0) {
            return view('bank.form.bank-direct-deposite-message', compact('user'));
        }
        $autoDebitExists = AutoDebitRequest::where('user_id', $user->id)
            //->where('status', 1) // adjust if status is 'approved'
            ->exists();

        // 👉 adjust column/table name if needed
        if (!$autoDebitExists) {
            return view('bank.form.auto-debit-authorization-message', compact('user'));
        }
        /*$transactions = \App\Models\Transaction1::where('user_id', $user->id)
                    ->orderBy('id', 'desc') // latest first
                    ->get();*/
        $transactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->orderBy('id', 'desc') // latest first
            ->get();

        // ✅ Get latest balance from transactions
        /*$availableBalance = Transaction::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;*/
        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id') // or latest('created_at') if you track timestamps
            ->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $emmsavingsPct = config('zedville.emargency_fund_persentage', 20);
        $emmsavingsAmount = round($salaryamount * ($emmsavingsPct / 100), 2);
        $emmengercyfundintrest = $this->emmengercyfundintrest($user->id);
        $emmengercyfundtransactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('description', 'Auto Transfer to Savings Account')
                    ->orWhere('description', 'Auto Transfer to Emergency Fund');
            })
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        $moneymarketintrest = $this->moneymarketintrest($user->id);
        $moneymarkettransactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->where('description', 'Auto Transfer to Money Market Account')
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        $bankUserName = $user->nickname;
        if ($bankAccount) {
            $this->creditMonthlySalary($user->id);
            $this->ensureMonthlyBills($user);
            // If bank account exists → load welcome view
            return view('bank.bank-account', compact('user', 'bankAccount', 'transactions', 'lastBalance', 'emmsavingsAmount', 'emmengercyfundintrest', 'emmengercyfundtransactions', 'moneymarketintrest', 'moneymarkettransactions', 'bankUserName'));
        }
        return view('bank.bank', compact('user'));
    }
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
            'dob' => 'nullable|date',
            'email' => 'required|email|unique:bank_accounts,student_email',
            'phone' => 'nullable|string|max:20',
            'homeAddress' => 'nullable|string|max:255',
            'AccPreName' => 'nullable|string|max:255',
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
            'sid' => session('sid'),
            'student_name' => $validated['fullName'],
            'student_dob' => Carbon::parse($validated['dob'])->format('Y-m-d'),
            'student_email' => $validated['email'],
            'student_phone' => $validated['phone'],
            'student_cityzen_id' => $user->citizenId,
            'student_address' => $validated['homeAddress'],
            'student_prefered_name' => $validated['AccPreName'],

            'bank_name' => 'Universal Bank',
            'primary_savings_account' => 'Primary Savings Account',
            // Same overflow bug as CitizenActivationController - was 13
            // chars against a varchar(10) column. Matches the 10-digit
            // pattern the sibling account-number fields already use.
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

        return redirect()->route('bank.direct_deposite_message')->with('success', 'Bank account created successfully!');
    }
    public function direct_deposite_message()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-direct-deposite-message', compact('user', 'bankAccount'));
    }
    public function my_account()
    {
        $user = auth()->user();
        // Check if this student already has a bank account
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        if ($bankAccount) {
            $this->creditMonthlySalary($user->id);
            $this->ensureMonthlyBills($user);
        }
        $transactions = \App\Models\Transaction1::where('user_id', $user->id)
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id') // or latest('created_at') if you track timestamps
            ->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        $emmengercyfundintrest = $this->emmengercyfundintrest($user->id);
        $emmengercyfundtransactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('description', 'Auto Transfer to Savings Account')
                    ->orWhere('description', 'Auto Transfer to Emergency Fund');
            })
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        $moneymarketintrest = $this->moneymarketintrest($user->id);
        $moneymarkettransactions = \App\Models\Transaction1::where('user_id', $user->id)
            ->where('description', 'Auto Transfer to Money Market Account')
            //->orderBy('transaction_date', 'desc') // latest first
            ->get();
        //Emmargency Fund
        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $emmsavingsPct = config('zedville.emargency_fund_persentage', 20);
        ;
        $emmsavingsAmount = round($salaryamount * ($emmsavingsPct / 100), 2);
        $bankUserName = $user->nickname;
        return view('bank.bank-my-account', compact('user', 'bankAccount', 'transactions', 'lastBalance', 'moneymarketintrest', 'emmengercyfundintrest', 'emmengercyfundtransactions', 'moneymarkettransactions', 'emmsavingsAmount', 'bankUserName'));
    }
    public function transfer()
    {
        $transfer = Transfer::where('user_id', auth()->id())->get();
        $scheduledTransfers = Transfer::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where('type', 'later')
                    ->orWhere('type', 'recurring')
                    ->orWhereNotNull('scheduled_at');
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();
        $bankaccountdetails = BankAccount::where('student_id', auth()->id())->first();

        $beneficiaries = Beneficiary::where('user_id', auth()->id())->get();

        return view('bank.bank-transfer', compact('transfer', 'scheduledTransfers', 'bankaccountdetails', 'beneficiaries'));
    }
    /*public function transfer_store(Request $request)
    {
        $data = $request->validate([
            'from_account'     => 'required|string',
            'account_number'   => 'required|string',
            'sort_code'        => 'nullable|string',
            'beneficiary_name' => 'required|string',
            'amount'           => 'required|numeric|min:0.01',
            'memo'             => 'nullable|string',
            'type'             => 'required|in:now,later,recurring',
            'scheduled_at'     => 'nullable|date',
            'saveAsBeneficiary'=> 'sometimes|accepted'
        ]);

        $sender = auth()->user();

        // Find recipient bank account
        $recipientBankAccount = BankAccount::where('primary_savings_account_number', $request->account_number)
            ->where('student_name', $request->beneficiary_name)
            ->first();

        if (!$recipientBankAccount) {
            return redirect()
                ->route('bank.transfer')
                ->withInput()
                ->with('error', 'Bank account number and beneficiary name not available.');
        }

        // ✅ Find the recipient user by student_id
        $recipientUser = \App\Models\User::find($recipientBankAccount->student_id);
        if (!$recipientUser) {
            return redirect()
                ->route('bank.transfer')
                ->withInput()
                ->with('error', 'Recipient user not found.');
        }

        // Check sender's balance
        $latestTxnSender = Transaction::where('user_id', $sender->id)->latest()->first();
        $senderBalance = $latestTxnSender ? $latestTxnSender->balance : 0;

        if ($senderBalance < $request->amount) {
            return redirect()
                ->route('bank.transfer')
                ->withInput()
                ->with('error', 'Insufficient balance.');
        }

        // Create transfer
        $data['user_id'] = $sender->id;
        $transfer = Transfer::create($data);

        // 1️⃣ Debit from sender
        $newSenderBalance = $senderBalance - $request->amount;
        Transaction::create([
            'user_id'     => $sender->id,
            'type'        => 'Fund Transfer Debit',
            'txn_date'    => now()->toDateString(),
            'value_date'  => now()->toDateString(),
            'description' => "Transfer to {$request->beneficiary_name} ({$request->account_number})",
            'ref_no'      => 'TRANSFER-' . strtoupper(uniqid()),
            'debit'       => $request->amount,
            'credit'      => 0,
            'balance'     => $newSenderBalance,
        ]);

        // 2️⃣ Credit to recipient
        $latestTxnRecipient = Transaction::where('user_id', $recipientUser->id)->latest()->first();
        $recipientBalance   = $latestTxnRecipient ? $latestTxnRecipient->balance : 0;
        $newRecipientBalance = $recipientBalance + $request->amount;

        // ✅ fetch sender’s primary savings account number from bank_accounts
        $senderBankAccount   = BankAccount::where('student_id', $sender->id)->first();
        $senderAccountNumber = $senderBankAccount?->primary_savings_account_number ?? '';

        Transaction::create([
            'user_id'     => $recipientUser->id,
            'type'        => 'Fund Transfer Credit',
            'txn_date'    => now()->toDateString(),
            'value_date'  => now()->toDateString(),
            'description' => "Received from {$sender->nickname} ({$senderAccountNumber})",
            'ref_no'      => 'TRANSFER-' . strtoupper(uniqid()),
            'debit'       => 0,
            'credit'      => $request->amount,
            'balance'     => $newRecipientBalance,
        ]);


        // Optional: save as beneficiary
        if ($request->filled('saveAsBeneficiary')) {
            Beneficiary::create([
                'user_id'        => $sender->id,
                'name'           => $request->beneficiary_name,
                'bank'           => $request->from_account,
                'account_number' => $request->account_number,
            ]);
        }

        return redirect()
            ->route('bank.transfer')
            ->with('success', 'Transfer completed successfully!');
    }*/
    /*public function transfer_store(Request $request)
    {
        //Log::info('transfer_store called', ['payload' => $request->all()]);

        $data = $request->validate([
            'from_account'      => 'required|string',
            'account_number'    => 'required|string',
            'sort_code'         => 'nullable|string',
            'beneficiary_name'  => 'required|string',
            'amount'            => 'required|numeric|min:0.01',
            'memo'              => 'nullable|string',
            'type'              => 'required|in:now,later,recurring',
            'scheduled_at'      => 'nullable|date',
            'saveAsBeneficiary' => 'sometimes|accepted'
        ]);

        $sender = auth()->user();
        //Log::info('Authenticated sender', ['id' => $sender->id]);

        // ✅ Find recipient bank account
        $recipientBankAccount = BankAccount::where('primary_savings_account_number', $request->account_number)
            ->where('student_name', $request->beneficiary_name)
            ->first();

        if (!$recipientBankAccount) {
            //Log::error('Recipient bank account not found', ['account_number' => $request->account_number]);
            return redirect()->route('bank.transfer')
                ->withInput()
                ->with('error', 'Bank account number and beneficiary name not available.');
        }
        //Log::info('Recipient bank account found', ['id' => $recipientBankAccount->id]);

        // ✅ Recipient user
        $recipientUser = User::find($recipientBankAccount->student_id);
        if (!$recipientUser) {
            //Log::error('Recipient user not found', ['student_id' => $recipientBankAccount->student_id]);
            return redirect()->route('bank.transfer')
                ->withInput()
                ->with('error', 'Recipient user not found.');
        }
        Log::info('Recipient user found', ['id' => $recipientUser->id]);

        // ✅ Sender bank account
        $senderBankAccount = BankAccount::where('student_id', $sender->id)->first();
        $senderBankAccountId = $senderBankAccount?->id;
        //Log::info('Sender bank account', ['id' => $senderBankAccountId]);

        // ✅ Sender balance
        $latestTxnSender = Transaction1::where('user_id', $sender->id)
            ->latest('transaction_date')
            ->first();
        $senderBalance = $latestTxnSender?->balance ?? 0;
        //Log::info('Sender balance', ['balance' => $senderBalance]);

        if ($senderBalance < $request->amount) {

            return redirect()->route('bank.transfer')
                ->withInput()
                ->with('error', 'Insufficient balance.');
        }

        // Create Transfer record
        $data['user_id'] = $sender->id;
        $transfer = Transfer::create($data);
        //Log::info('Transfer record created', ['transfer_id' => $transfer->id]);

        // ----- 1️⃣ Debit sender -----
        $newSenderBalance = $senderBalance - $request->amount;
        $debit = Transaction1::create([
            'user_id'          => $sender->id,
            'bank_account_id'  => $senderBankAccountId,
            'transaction_date' => now(),
            'description'      => "Transfer to {$request->beneficiary_name} ({$request->account_number})",
            'type'             => 'debit',
            'category'         => 'Fund Transfer',
            'amount'           => $request->amount,
            'balance'          => $newSenderBalance,
            'is_penalty'       => 0,
        ]);
        //Log::info('Sender debit transaction created', ['id' => $debit->id]);

        // ----- 2️⃣ Credit recipient -----
        $latestTxnRecipient = Transaction1::where('user_id', $recipientUser->id)
            ->latest('transaction_date')
            ->first();
        $recipientBalance = $latestTxnRecipient?->balance ?? 0;
        $newRecipientBalance = $recipientBalance + $request->amount;

        $credit = Transaction1::create([
            'user_id'          => $recipientUser->id,
            'bank_account_id'  => $recipientBankAccount->id,
            'transaction_date' => now(),
            'description'      => "Received from {$sender->nickname} ({$senderBankAccount?->primary_savings_account_number})",
            'type'             => 'credit',
            'category'         => 'Fund Transfer',
            'amount'           => $request->amount,
            'balance'          => $newRecipientBalance,
            'is_penalty'       => 0,
        ]);
        //Log::info('Recipient credit transaction created', ['id' => $credit->id]);

        // Optional: save as beneficiary
        if ($request->filled('saveAsBeneficiary')) {
            $beneficiary = Beneficiary::create([
                'user_id'        => $sender->id,
                'name'           => $request->beneficiary_name,
                'bank'           => $request->from_account,
                'account_number' => $request->account_number,
            ]);
            //Log::info('Beneficiary saved', ['id' => $beneficiary->id]);
        }

        //Log::info('Transfer completed successfully', ['transfer_id' => $transfer->id]);

        return redirect()
            ->route('bank.transfer')
            ->with('success', 'Transfer completed successfully!');
    }*/
    public function transfer_store(Request $request)
    {
        // 1️⃣ Validate request
        $data = $request->validate([
            'from_account' => 'required|string',
            'account_number' => 'required|string',
            'sort_code' => 'nullable|string',
            'beneficiary_name' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'memo' => 'nullable|string',
            'type' => 'nullable|in:now,later,recurring',
            'paydate' => 'required_if:type,later|nullable|date',
            'start_date' => 'required_if:type,recurring|nullable|date',
            'end_date' => 'required_if:type,recurring|nullable|date|after_or_equal:start_date',
            'frequency' => 'required_if:type,recurring|nullable|in:daily,weekly,monthly',
            'saveAsBeneficiary' => 'nullable|boolean', // new field
        ]);
        // ✅ ADD THIS LINE HERE
        $selectedOption = $request->input('own_account_option', null);
        $sender = auth()->user();

        //Check is account is open or not
        if ($request->has('own_account_option')) {
            $senderBankAccount = BankAccount::where('student_id', $sender->id)->first();

            if (
                $senderBankAccount->is_open_emergency_account == 0 &&
                $senderBankAccount->is_open_money_market_account == 0
            ) {
                return redirect()->back()->with('error', 'You don’t have any active account. Please open at least one account before transferring money.');
            }

            //$selectedOption = $request->input('own_account_option');

            if ($selectedOption === 'Emergency Fund Account' && $senderBankAccount->is_open_emergency_account == 0) {
                return redirect()->back()->with('error', 'Your Emergency Fund Account is not active yet. Please open it before transferring money.');
            }

            if ($selectedOption === 'Money Market Account' && $senderBankAccount->is_open_money_market_account == 0) {
                return redirect()->back()->with('error', 'Your Money Market Account is not active yet. Please open it before transferring money.');
            }
        }



        // 🧠 Step 2: Money Market Lock Check
        if ($selectedOption === 'Money Market Account') {
            //Before 26 access money market account
            $today = now();
            $dayOfMonth = (int) $today->format('d');
            if ($dayOfMonth < 26) {
                return back()->with('error', "
                    <strong>Money Market Locked</strong><br>
                    You can access your <strong>Money Market Account</strong> only from the <strong>26th</strong> of each month onward.<br><br>
                    💡 Use this time to manage your expenses and budgeting activities first!
                ");
            }
            // Get user's total monthly spending
            //$generator = app(\App\Services\StatementGenerator::class);
            //$monthlySalary = $generator->getActiveBudgetConfig()->monthly_salary ?? 3952.40;
            $monthlySalary = config('zedville.monthly_salary', 3952.40);

            $totalExpenses = Transaction1::where('user_id', $sender->id)
                ->where('type', 'debit')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount');

            $minThreshold = $monthlySalary * 0.91; // 91%

            // ❌ Not spent 91% — LOCKED
            if ($totalExpenses < $minThreshold) {
                return back()->with('error', "
                <strong>Transfer Not Allowed</strong><br>
                You cannot transfer money to your <strong>Money Market Account</strong> yet.<br><br>
                <strong>Why?</strong> You need to spend at least <strong>91%</strong> of this month's salary first (on expenses, bills, and budgeting activities).<br><br>
                💡 Remember: Money Market Accounts are for extra funds after covering essentials!
            ");
            }

            // ✅ Spent 91% or more — UNLOCK temporarily
            //$bankAccount->update(['is_money_market_locked' => false]);
        }

        //dd('test');
        //Check is account is open or not


        // 2️⃣ Find recipient bank account
        $recipientBankAccount = BankAccount::where('student_name', $request->beneficiary_name)
            ->where(function ($query) use ($request) {
                $query->where('primary_savings_account_number', $request->account_number)
                    ->orWhere('emergency_fund_account_number', $request->account_number)
                    ->orWhere('money_market_account_number', $request->account_number);
            })
            ->first();


        if (!$recipientBankAccount) {
            return redirect()->route('bank.transfer')
                ->withInput()
                ->with('error', 'Bank account number and beneficiary name not available.');
        }

        // 3️⃣ Check sender balance
        /*$latestTxnSender = Transaction1::where('user_id', $sender->id)
            //->latest('transaction_date')
             ->orderBy('transaction_date', 'desc')
             ->orderBy('id', 'desc')
            ->first();*/

        /*$latestTxnSender = Transaction1::where('user_id', $sender->id)
            ->latest('id') // or latest('created_at') if you track timestamps
            ->first();
        $senderBalance = $latestTxnSender?->balance ?? 0;*/
        $senderBank = BankAccount::where('student_id', $sender->id)->first();

        switch ($request->from_account) {

            case 'Primary Savings Account':

                $latestTxnSender = Transaction1::where('user_id', $sender->id)
                    ->latest('id')
                    ->first();

                $senderBalance = $latestTxnSender?->balance ?? 0;
                break;

            case 'Emergency Fund Account':

                $senderBalance = $senderBank->emergency_fund_account_amount ?? 0;
                break;

            case 'Money Market Account':

                $senderBalance = $senderBank->money_market_account_amount ?? 0;
                break;

            default:

                $senderBalance = 0;
        }
        //dd($senderBalance);

        if ($senderBalance < $request->amount) {
            /*if ($selectedOption === 'Emergency Fund Account') {
                return redirect()->route('bank.transfer')
                ->withInput()
                ->with('error', "<strong>Insufficient Balance</strong><br>
                    Insufficient funds. The payment will automatically be deducted next month on the same day, after your monthly statement is sent (1st–5th).");
            }*/
            return redirect()->route('bank.transfer')
                ->withInput()
                ->with('error', 'Insufficient Balance.');
        }

        // 4️⃣ Map form fields to DB
        $data['user_id'] = $sender->id;
        $data['sid'] = session()->get('sid');
        $data['scheduled_at'] = isset($data['paydate']) ? Carbon::parse($data['paydate']) : null;
        unset($data['paydate']);

        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::parse($data['start_date']);
        }
        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date']);
        }

        // 5️⃣ Create transfer record
        $transfer = Transfer::create($data);
        // 2️⃣ Save as beneficiary if checkbox is checked
        if ($request->boolean('saveAsBeneficiary')) {
            // Check if beneficiary already exists to avoid duplicates
            $existing = Beneficiary::where('user_id', auth()->id())
                ->where('name', $data['beneficiary_name'])
                ->where('account_number', $data['account_number'])
                ->first();

            if (!$existing) {
                Beneficiary::create([
                    'user_id' => auth()->id(),
                    'sid' => session()->get('sid'),
                    'name' => $data['beneficiary_name'],
                    'bank' => $data['from_account'],
                    'account_number' => $data['account_number'],
                    'sort_code' => $data['sort_code'] ?? null,
                ]);
            }
        }

        // 6️⃣ Dispatch transfer based on type
        switch ($request->type) {
            case 'now':
                $this->processTransfer($transfer->id, now());
                break;

            case 'later':
                if ($transfer->scheduled_at) {
                    ProcessScheduledTransfer::dispatch($transfer->id)
                        ->delay($transfer->scheduled_at->diffInSeconds(now()));
                }
                break;

            case 'recurring':
                if ($transfer->start_date) {
                    // Dispatch first occurrence
                    ProcessScheduledTransfer::dispatch($transfer->id)
                        ->delay($transfer->start_date->diffInSeconds(now()));
                }
                break;
        }
        /*if (isset($selectedOption) && $selectedOption === 'Money Market Account') {
            $bankAccount->update(['is_money_market_locked' => true]);
        }*/
        return redirect()->route('bank.transfer')->with('success', 'Transfer successfully!');
    }


    // Process single transfer transaction
    public function processTransfer($transferId, $transactionDate)
    {
        DB::transaction(function () use ($transferId, $transactionDate) {
            $transfer = Transfer::find($transferId);
            if (!$transfer)
                return;

            $sender = User::find($transfer->user_id);
            $recipientBank = BankAccount::where('student_name', $transfer->beneficiary_name)
                ->where(function ($query) use ($transfer) {
                    $query->where('primary_savings_account_number', $transfer->account_number)
                        ->orWhere('emergency_fund_account_number', $transfer->account_number)
                        ->orWhere('money_market_account_number', $transfer->account_number);
                })
                ->first();

            //Check Recived account
            $recipientAccountType = null;
            if ($recipientBank->primary_savings_account_number === $transfer->account_number) {
                $recipientAccountType = 'primary';
            } elseif ($recipientBank->emergency_fund_account_number === $transfer->account_number) {
                $recipientAccountType = 'emergency';
            } elseif ($recipientBank->money_market_account_number === $transfer->account_number) {
                $recipientAccountType = 'money_market';
            }

            $recipient = User::find($recipientBank->student_id);

            /* $senderBank = BankAccount::where('student_id', $sender->id)->first();



             $latestSenderTxn = Transaction1::where('user_id', $sender->id)
                         ->latest('id') // or latest('created_at') if you track timestamps
                         ->first();


             $senderBalance = $latestSenderTxn?->balance ?? 0;

             $newSenderBalance = $senderBalance - $transfer->amount;*/
            $senderBank = BankAccount::where('student_id', $sender->id)->first();

            switch ($transfer->from_account) {

                case 'Primary Savings Account':

                    $latestSenderTxn = Transaction1::where('user_id', $sender->id)
                        ->latest('id')
                        ->first();

                    $senderBalance = $latestSenderTxn?->balance ?? 0;

                    $newSenderBalance = $senderBalance - $transfer->amount;

                    $senderAccountNumber = $senderBank->primary_savings_account_number;

                    break;


                case 'Emergency Fund Account':

                    $senderBalance = $senderBank->emergency_fund_account_amount;

                    $newSenderBalance = $senderBalance - $transfer->amount;

                    $senderBank->emergency_fund_account_amount = $newSenderBalance;

                    $senderBank->save();

                    $senderAccountNumber = $senderBank->emergency_fund_account_number;

                    break;


                case 'Money Market Account':

                    $senderBalance = $senderBank->money_market_account_amount;

                    $newSenderBalance = $senderBalance - $transfer->amount;

                    $senderBank->money_market_account_amount = $newSenderBalance;

                    $senderBank->save();

                    $senderAccountNumber = $senderBank->money_market_account_number;

                    break;
            }
            if ($transfer->from_account === 'Primary Savings Account') {
                Transaction1::create([
                    'user_id' => $sender->id,
                    'sid' => session()->get('sid'),
                    'bank_account_id' => $senderBank?->id,
                    'transaction_date' => $transactionDate,
                    'description' => "Transfer to {$transfer->beneficiary_name} ({$transfer->account_number})",
                    'type' => 'debit',
                    'category' => 'Fund Transfer',
                    'amount' => $transfer->amount,
                    'balance' => $newSenderBalance,
                    'is_penalty' => 0,
                ]);
            }
            $latestRecipientTxn = Transaction1::where('user_id', $recipient->id)
                ->latest('transaction_date')->first();
            $recipientBalance = $latestRecipientTxn?->balance ?? 0;
            /*if ($recipientAccountType === 'primary') {
            $newRecipientBalance = $recipientBalance + $transfer->amount;
            $recivedbankkAccount=$senderBank?->primary_savings_account_number;
            }elseif($recipientAccountType === 'emergency'){
            $newRecipientBalance = $recipientBalance ;
            $recipientBank->emergency_fund_account_amount = $recipientBank->emergency_fund_account_amount + $transfer->amount;
            $recipientBank->save();
            $recivedbankkAccount=$senderBank?->emergency_fund_account_amount;
            //dd($recipientBank->emergency_fund_account_amount);
            }elseif($recipientAccountType === 'money_market'){
            $newRecipientBalance = $recipientBalance ;
            $recipientBank->money_market_account_amount = $recipientBank->money_market_account_amount + $transfer->amount;
            $recipientBank->save();
            $recivedbankkAccount=$senderBank?->money_market_account_number;
            }*/
            switch ($recipientAccountType) {

                case 'primary':

                    $latestRecipientTxn = Transaction1::where('user_id', $recipient->id)
                        ->latest('id')
                        ->first();

                    $recipientBalance = $latestRecipientTxn?->balance ?? 0;

                    $newRecipientBalance = $recipientBalance + $transfer->amount;

                    break;


                case 'emergency':

                    $recipientBalance = $recipientBank->emergency_fund_account_amount;

                    $newRecipientBalance = $recipientBalance + $transfer->amount;

                    $recipientBank->emergency_fund_account_amount = $newRecipientBalance;

                    $recipientBank->save();

                    break;


                case 'money_market':

                    $recipientBalance = $recipientBank->money_market_account_amount;

                    $newRecipientBalance = $recipientBalance + $transfer->amount;

                    $recipientBank->money_market_account_amount = $newRecipientBalance;

                    $recipientBank->save();

                    break;
            }
            if ($recipientAccountType === 'primary') {
                Transaction1::create([
                    'user_id' => $recipient->id,
                    'sid' => session()->get('sid'),
                    'bank_account_id' => $recipientBank->id,
                    'transaction_date' => $transactionDate,
                    //'description' => "Received from {$sender->name} ({$senderBank?->primary_savings_account_number})",
                    //'description' => "Received from {$sender->name} ({$recivedbankkAccount})",
                    'description' => "Received from {$sender->nickname} ({$senderAccountNumber})",
                    'type' => 'credit',
                    'category' => 'Fund Transfer',
                    'amount' => $transfer->amount,
                    'balance' => $newRecipientBalance,
                    'is_penalty' => 0,
                ]);
            }
            app(ParticipationService::class)->award($sender->id, 'transfer');
        });

    }
    public function beneficiary_store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'bank' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'sort_code' => 'nullable|string|max:255',
        ]);
        // Check if bank account exists
        $accountExists = BankAccount::where('primary_savings_account_number', $request->account_number)->exists();
        $nameExists = BankAccount::where('student_name', $request->beneficiary_name)->exists();

        // Prepare error messages
        // Prepare error message
        if (!$nameExists && !$accountExists) {
            return redirect()
                ->route('bank.transfer')
                ->withInput()
                ->with('error', 'Bank account number and beneficiary name not available in our database.');
        } elseif (!$nameExists) {
            return redirect()
                ->route('bank.transfer')
                ->withInput()
                ->with('error', 'Beneficiary name not available in our database.');
        } elseif (!$accountExists) {
            return redirect()
                ->route('bank.transfer')
                ->withInput()
                ->with('error', 'Bank account number not available in our database.');
        }
        $data['user_id'] = auth()->id();
        $data['sid'] = session()->get('sid');
        Beneficiary::create($data);
        return redirect()
            ->route('bank.transfer')
            ->with('success', 'Beneficiary saved!');
        //return back()->with('success','Beneficiary saved!');
    }
    public function pay_bills()
    {
        $getAllbiller = ManagePayeeBiller::where('status', '1')->get();
        $transactions = Transaction1::where('user_id', Auth::id())
            ->where('type', 'debit')                 // ✅ exclude 0.00 or NULL
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $scheduledTransfers = Transfer::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where('type', 'later')
                    ->orWhere('type', 'recurring')
                    ->orWhereNotNull('scheduled_at');
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();
        return view('bank.bank-pay-bills', compact('getAllbiller', 'transactions', 'scheduledTransfers'));
    }
    /*public function store_pay_bill(Request $request)
        {
            $validated = $request->validate([
                'account_id'     => 'required|string|max:191',
                'biller_id'      => 'required|string|max:191',
                'account_number' => 'required|string|max:100',
                'amount'         => 'required|numeric|min:0.01',
                'payment_type'   => 'required|in:now,schedule,recurring',
                'schedule_date'  => 'nullable|date',
                'frequency'      => 'nullable|in:weekly,monthly,yearly',
                'start_date'     => 'nullable|date',
                'end_date'       => 'nullable|date|after_or_equal:start_date',
            ]);

            $validated['user_id'] = auth()->id();

            PayBill::create($validated);

            return redirect()
                ->route('bank.pay_bills')
                ->with('success', 'Bill payment scheduled successfully!');
        }*/
    public function store_pay_bill(Request $request)
    {

        $validated = $request->validate([
            'from_account' => 'required|string|max:191',
            'beneficiary_name' => 'required|string|max:191',
            'account_number' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:now,later,recurring',
            'paydate' => 'nullable|date',
            'frequency' => 'nullable|in:weekly,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $userId = auth()->id();
        $validated['user_id'] = $userId;


        $bankAccount = BankAccount::where('student_id', $userId)->first();
        if (!$bankAccount) {
            return back()->with('error', 'Bank account not found.');
        }

        //$latestTxn = Transaction1::where('user_id', $userId)->latest('transaction_date')->first();
        $latestTxn = Transaction1::where('user_id', $userId)
            //->latest('transaction_date')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        $balance = $latestTxn?->balance ?? 0;
        if ($balance < $request->amount) {
            return redirect()->route('bank.pay_bills')->with('error', 'Insufficient balance.');
        }

        // ✅ Prepare transfer data
        $data = $validated;
        $data['scheduled_at'] = isset($validated['paydate']) && $validated['paydate'] !== 'now'
            ? Carbon::parse($validated['paydate'])
            : null;
        unset($data['paydate']);

        if (isset($validated['start_date'])) {
            $data['start_date'] = Carbon::parse($validated['start_date']);
        }
        if (isset($validated['end_date'])) {
            $data['end_date'] = Carbon::parse($validated['end_date']);
        }
        $data['sid'] = session()->get('sid');
        \Log::info('Creating transfer', $data);
        $transfer = Transfer::create($data);

        switch ($request->type) {
            case 'now':
                $this->processBillPayment($userId, $request->amount, $request->beneficiary_name, $request->account_number);
                break;

            case 'schedule':
                if ($transfer->scheduled_at) {
                    ProcessScheduledBillPayment::dispatch($transfer->id)
                        ->delay($transfer->scheduled_at->diffInSeconds(now()));
                }
                break;

            case 'recurring':
                if ($transfer->start_date) {
                    ProcessScheduledBillPayment::dispatch($transfer->id)
                        ->delay($transfer->start_date->diffInSeconds(now()));
                }
                break;
        }

        return redirect()->route('bank.pay_bills')->with('success', 'Bill payment scheduled successfully!');
    }

    public function processBillPayment($userId, $amount, $beneficiaryName, $accountNumber)
    {
        DB::transaction(function () use ($userId, $amount, $beneficiaryName, $accountNumber) {
            $senderBank = BankAccount::where('student_id', $userId)->first();
            if (!$senderBank)
                return;

            /*$latestTxn = Transaction1::where('user_id', $userId)
                ->latest('transaction_date')->first();*/
            $latestTxn = Transaction1::where('user_id', $userId)
                ->latest('id') // or latest('created_at') if you track timestamps
                ->first();
            $balance = $latestTxn?->balance ?? 0;
            //$newBalance = $balance - $amount;
            $newBalance = max(0, $balance - $amount);

            // Record debit for sender (no recipient)
            Transaction1::create([
                'user_id' => $userId,
                'sid' => session()->get('sid'),
                'bank_account_id' => $senderBank->id,
                'transaction_date' => now(),
                'description' => "Bill payment to {$beneficiaryName} ({$accountNumber})",
                'type' => 'debit',
                'category' => 'Bill Payment',
                'amount' => $amount,
                'balance' => $newBalance,
                'is_penalty' => 0,
            ]);
        });
    }


    public function bank_statements(Request $request)
    {
        $query = Transaction::where('user_id', auth()->id());

        // Optional date filters from form
        if ($request->filled('start_date')) {
            $query->whereDate('txn_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('txn_date', '<=', $request->end_date);
        }

        // Optional account filter if you have multiple accounts
        if ($request->filled('account')) {
            $query->where('type', $request->account);
        }

        $transactions = $query->orderBy('txn_date', 'desc')->get();
        return view('bank.bank-statements', compact('transactions'));
    }

    public function viewStatement($year, $month)
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        $transactions = Transaction::where('user_id', auth()->id())
            ->whereBetween('txn_date', [$start, $end])
            ->orderBy('txn_date')
            ->get();

        // Calculate summary
        $openingBalance = Transaction::where('user_id', auth()->id())
            ->whereDate('txn_date', '<', $start)
            ->orderBy('txn_date', 'desc')
            ->value('balance') ?? 0;

        $totalCredits = $transactions->sum('credit');
        $totalDebits = $transactions->sum('debit');

        // If your transactions table keeps running balance:
        $closingBalance = $transactions->last()->balance ?? $openingBalance + $totalCredits - $totalDebits;

        return view('bank.view-statement', [
            'year' => $year,
            'month' => $month,
            'start' => $start,
            'end' => $end,
            'openingBalance' => $openingBalance,
            'totalCredits' => $totalCredits,
            'totalDebits' => $totalDebits,
            'closingBalance' => $closingBalance,
            'transactions' => $transactions,
        ]);
    }
    public function help()
    {
        return view('bank.bank-help');
    }
    public function manage_payee()
    {
        $userId = auth()->id();

        // Fetch pay bills for logged-in user
        $payBills = PayBill::where('user_id', $userId)->get();
        $getAllbiller = ManagePayeeBiller::where('status', '1')->get();

        // Example: transfer recipients could be another table, for now static or leave empty
        $transferRecipients = [
            ['name' => 'John Smith', 'bank' => 'Global Trust Bank', 'account_number' => '1234567890', 'amount' => '125.30 ZEDS'],
            ['name' => 'Sarah Johnson', 'bank' => 'Global Trust Bank', 'account_number' => '1234567890', 'amount' => '125.30 ZEDS'],
        ];

        return view('bank.bank-manage-payee', compact('payBills', 'transferRecipients', 'getAllbiller'));
    }
    public function schedule_transfers()
    {
        return view('bank.bank-schedule-transfers');
    }
    public function recurring_payment()
    {
        return view('bank.bank-recurring-payment');
    }
    public function payment_history()
    {
        $transactions = Transaction1::where('user_id', Auth::id())
            ->where('type', 'debit')                 // ✅ exclude 0.00 or NULL
            ->orderBy('id', 'ASC')
            ->get();
        return view('bank.bank-payment-history', compact('transactions'));
    }
    public function direct_deposite()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-direct-deposite-form', compact('user', 'bankAccount'));
    }

    public function authorize_direct_deposit(Request $request, StatementGenerator $generator)
    {
        $user = Auth::user();
        // 💰 Update bank account amount
        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        if ($bankAccount) {
            $salary = config('zedville.monthly_salary', 3952.40); // or dynamic amount

            $bankAccount->update([
                'primary_savings_account_amount' => $salary
            ]);
        }

        // 📄 Generate bank statement AFTER balance update
        $generator->generateForUser($user->id);
        $timezone = $request->input('timezone', 'UTC');

        /*$content = view('mailbox_templates.auto-debit', ['user' => $user])->render();

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
            ->route('bank.auto_debit_authorization_message')
            ->with('success', 'Salary Created Sucessfully');
        // ✅ Redirect to mailbox page with success message
        /*return redirect()
            ->route('profile.mailbox') // change to your actual mailbox route name
            ->with('success', 'Auto-debit authorized successfully and message added to your mailbox.');*/
    }
    public function auto_debit_authorization_message()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.auto-debit-authorization-message', compact('user', 'bankAccount'));
    }
    public function auto_debit_authorization_form()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        // Fetch required billers
        $billers = ManagePayeeBiller::whereIn('name', [
            'City School',
            'Internet Service Provider',
            'Utility - Electricity',
            'Utility - Water'
        ])->where('status', 1)->get()->keyBy('id');
        return view('bank.form.bank-auto-debit-authorization-form', compact('user', 'bankAccount', 'billers'));
    }
    //Store auto debit authorization form
/*public function store_auto_debit_authorization_form(Request $request){
    $user = Auth::user();
    // Validate base data
    $request->validate([
        'services' => 'required|array|min:1',
        'bankaccountnumber' => 'required',
    ]);
    foreach ($request->services as $key => $accountNumber) {

        $meta = $request->services_meta[$key] ?? null;
        if (!$meta) continue;

        AutoDebitRequest::create([
            'user_id'        => $user->id,
            'type'           => $meta['name'],           // School / Internet / Water
            'accountno'      => $meta['account'],
            'fullname'       => $user->name,
            'email'          => $user->email,
            'serviceaddress' => $user->student_address,
            'bankname'       => $request->bankname,
            'backaccountnumber' => $request->bankaccountnumber,
            'amount'         => $request->amount, // can be updated later
            'startDate'      => now(),
            'signature'      => $user->name,
            'date'           => now(),
            'tandagree'      => 1,
        ]);
    }
    return redirect()
        ->route('bank.index') // change to your actual mailbox route name
        ->with('success', 'Congratulations, you are all set!');
}*/
    /*public function store_auto_debit_authorization_form(Request $request)
    {
        $user = Auth::user();

        // Validate base data
        $request->validate([
            'services' => 'required|array|min:1',
            'bankaccountnumber' => 'required',
        ]);

        foreach ($request->services as $key => $accountNumber) {

            $meta = $request->services_meta[$key] ?? null;
            if (!$meta) continue;

            // ✅ CHECK DUPLICATE (user + service)
            $alreadySubmitted = AutoDebitRequest::where('user_id', $user->id)
                ->where('type', $meta['name']) // City School / Internet / Water
                ->exists();

            if ($alreadySubmitted) {
                return redirect()
                    ->back()
                    //->with('error', "You have already submitted auto-debit for {$meta['name']}.");
                    ->with('error', "You have already submitted auto-debit for School Fees, Internet Service Provider, Electricity Service, Water Service.");
            }

            AutoDebitRequest::create([
                'user_id'            => $user->id,
                'type'               => $meta['name'],
                'accountno'          => $meta['account'],
                'fullname'           => $user->name,
                'email'              => $user->email,
                'serviceaddress'     => $user->student_address,
                'bankname'           => $request->bankname,
                'backaccountnumber'  => $request->bankaccountnumber,
                'amount'             => $meta['amount'] ?? 0,
                'startDate'          => now(),
                'signature'          => $user->name,
                'date'               => now(),
                'tandagree'          => 1,
            ]);
        }

        $salaryDate = now();
        $fixedTransactions = [
        ['description' => 'Utility - Electricity', 'amount' => rand(60, 90)],
        ['description' => 'Utility - Water', 'amount' => rand(30, 60)],
        ['description' => 'Internet', 'amount' => 20],
        ['description' => 'Rent', 'amount' => 300],
        ['description' => 'School Fees', 'amount' => 1000],
    ];

    $bankAccount = BankAccount::where('student_id', $user->id)->first();

    foreach ($fixedTransactions as $item) {

        // 🔒 Prevent duplicate bills on SAME salary date
        $exists = Transaction1::where('user_id', $user->id)
            ->where('description', $item['description'])
            ->whereDate('transaction_date', $salaryDate->toDateString())
            ->exists();

        if ($exists) {
            continue;
        }

        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        $newBalance  = max(0, $lastBalance - $item['amount']);

        Transaction1::create([
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id ?? null,
            'transaction_date' => $salaryDate, // ✅ SAME DATE
            'description' => $item['description'],
            'type' => 'debit',
            'category' => 'Wants',
            'amount' => $item['amount'],
            'balance' => $newBalance,
            'is_penalty' => false,
        ]);
    }


        /*return redirect()
            ->route('bank.index')
            ->with('success', 'Congratulations, you are all set!');
            return redirect()
        ->route('consumer-profile-survey')
        ->with('success', 'Congratulations, your auto debit is set up. Now complete the survey');
    }*/
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
                'sid' => session()->get('sid'),
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
                'sid' => session()->get('sid'),
                'type' => 'Rent',
                'accountno' => 'RENT-AUTO', // static / dummy
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $user->student_address,
                'bankname' => $request->bankname,
                'backaccountnumber' => $request->bankaccountnumber,
                'amount' => 300, // fixed rent
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
                'sid' => session()->get('sid'),
                'type' => 'City School',
                'accountno' => $billers[3]->account_number ?? 'SCHOOL-AUTO',
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $user->student_address,
                'bankname' => $request->bankname,
                'backaccountnumber' => $request->bankaccountnumber,
                'amount' => 1000,
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
            'Rent' => 300,
            'School Fees' => 1000,
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

        // ✅ Ensure monthly bills are processed only on or after the 7th
        $this->ensureMonthlyBills($user);

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ REDIRECT
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('bank.consumer_survey_message')
            ->with('success', 'Congratulations, your auto debit is set up. Now complete the survey.');
    }
    public function consumer_survey_message()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.consumer-survey-message', compact('user', 'bankAccount'));
    }
    //Store auto debit authorization form
    public function store_direct_deposite(Request $request)
    {
        $user = Auth::user();
        // Check if already submitted
        $exists = DirectDeposite::where('user_id', $user->id)->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You have already submitted.');
        }

        // Validate request
        $validated = $request->validate([
            'empname' => 'required|string|max:191',
            'empphone' => 'nullable|string|max:20',
            'empemail' => 'nullable|email|max:191',
            'empid' => 'nullable|string|max:100',
            'bankname' => 'nullable|string|max:191',
            'bankaccountiban' => 'nullable|string|max:100',
            'banknumber' => 'nullable|string|max:191',
            'bankdeposite' => 'nullable|boolean',
            'empsignature' => 'nullable|string|max:191',
            'empdate' => 'nullable|date',
            'empreceived' => 'nullable|string|max:191',
            'empreceiveddate' => 'nullable|date',
            'empeffectivedate' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        // Insert new record
        $validated['user_id'] = $user->id;
        $validated['sid'] = session()->get('sid');
        $validated['bankdeposite'] = $request->has('bankdeposite') ? 1 : 0;

        DirectDeposite::create($validated);

        return redirect()->back()->with('success', 'Form submitted successfully.');

    }
    public function eletricity_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-eletricity-auto-debit-form', compact('user', 'bankAccount'));
    }
    public function internet_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-internet-auto-debit-form', compact('user', 'bankAccount'));
    }
    public function school_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-school-auto-debit-form', compact('user', 'bankAccount'));
    }
    public function water_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-water-auto-debit-form', compact('user', 'bankAccount'));
    }
    public function store_auto_debit(Request $request)
    {
        $user = Auth::user();

        // ✅ Check if already submitted for this type
        $exists = AutoDebitRequest::where('user_id', $user->id)
            ->where('type', $request->input('type'))
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You have already submitted.');
        }

        // ✅ Validate inputs
        $validated = $request->validate([
            'type' => 'nullable|string|max:191',
            'fullname' => 'required|string|max:191',
            'email' => 'nullable|email|max:191',
            'accountno' => 'required|string|max:100',
            'serviceaddress' => 'required|string|max:255',
            'bankname' => 'required|string|max:191',
            'backaccountnumber' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1', // must have positive amount
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'signature' => 'required|string|max:191',
            'date' => 'required|date',
        ]);

        // ✅ Handle checkboxes
        $validated['billschedule'] = $request->has('billschedule') ? 1 : 0;
        $validated['fixedpayment'] = $request->has('fixedpayment') ? 1 : 0;
        $validated['tandagree'] = $request->has('tandagree') ? 1 : 0;

        // ✅ Assign logged-in user
        $validated['user_id'] = $user->id;
        $validated['sid'] = session()->get('sid');

        // ✅ Get available balance from transactions
        $availableBalance = Transaction::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        // ✅ Check sufficient balance
        if ($availableBalance < $validated['amount']) {
            return redirect()->back()->with('error', 'Insufficient balance for this auto-debit request.');
        }

        // ✅ Store auto debit request
        AutoDebitRequest::create($validated);

        // ✅ Calculate new balance
        /*$newBalance = $availableBalance - $validated['amount'];

        // ✅ Store debit transaction
        $transaction = Transaction::create([
            'user_id'    => $user->id,
            'type'       => $validated['type'] ?? 'Auto Debit',
            'txn_date'   => now()->toDateString(),
            'value_date' => now()->toDateString(),
            'description'=> 'TO TRANSFER ' . ($validated['type'] ?? '') . ' ' . $validated['accountno'],
            'ref_no'     => 'TRANSFER TO ' . $validated['accountno'],
            'debit'      => $validated['amount'],
            'credit'     => 0,
            'balance'    => $newBalance,
        ]);*/
        // Get the user's bank account
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->back()->with('error', 'Bank account not found for this user.');
        }
        $latestTxn = Transaction1::where('user_id', $user->id)
            ->where('bank_account_id', $bankAccount->id)
            ->latest('transaction_date')
            ->first();
        $previousBalance = $latestTxn ? $latestTxn->balance : 0;
        $newBalance = max(0, $previousBalance - $validated['amount']);
        Transaction1::create([
            'user_id' => $user->id,
            'sid' => session()->get('sid'),
            'bank_account_id' => $bankAccount->id,
            'transaction_date' => now(),
            'description' => 'Transfer to ' . ($validated['type'] ?? '') . ' ' . $validated['accountno'],
            'type' => 'debit',
            'category' => $validated['type'] ?? 'Auto Debit',
            'amount' => $validated['amount'],
            'balance' => $newBalance,
            'is_penalty' => 0,
        ]);
        return redirect()->back()->with('success', 'Form submitted and auto-debit transaction created successfully.');
    }

    /*protected function creditMonthlySalary($userId)
    {
        // ✅ Find user's bank account (only for linking, not for balance)
        $account = BankAccount::where('student_id', $userId)->first();
        if (!$account) {
            return;
        }

        // ✅ Prevent duplicate credit for the same month
        $alreadyCredited = Transaction::where('user_id', $userId)
            ->where('description', 'LIKE', 'Monthly Salary Credit%')
            ->whereMonth('txn_date', now()->month)
            ->whereYear('txn_date', now()->year)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $amount = 4250;

        // ✅ Get last balance from transactions
        $lastBalance = Transaction::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        $newBalance = $lastBalance + $amount;

        // ✅ Create transaction entry
        $transaction = Transaction::create([
            'user_id'    => $userId,
            'sid'              => session()->get('sid'),
            'type'       => 'Monthly Salary Credit',
            'txn_date'   => now()->toDateString(),
            'value_date' => now()->toDateString(),
            'description'=> 'Monthly Salary Credit - ' . now()->format('F Y'),
            'ref_no'     => 'SALARY-' . uniqid(),
            'debit'      => 0,
            'credit'     => $amount,
            'balance'    => $newBalance,
        ]);

        // ✅ Trigger mailbox notification
        MailboxScheduler::scheduleForEvent(
            'salary-deposite',
            $userId,
            [
                'transaction_id' => $transaction->id,
                'amount'         => $amount,
                'balance'        => $newBalance,
                'month'          => now()->format('F Y'),
            ]
        );
    }*/
    /*public function creditMonthlySalary($userId)
    {
        // ✅ Salary is only credited on/after the 6th of each month
        if (now()->day < 6) {
            return;
        }

        // ✅ Find user's bank account (only for linking, not for balance)
        $account = BankAccount::where('student_id', $userId)->first();
        if (!$account) {
            return;
        }

        // ✅ Prevent duplicate credit for the same month (matches any salary or Income entry)
        $alreadyCredited = Transaction1::where('user_id', $userId)
            ->where(function ($query) {
                $query->where('category', 'Income')
                    ->orWhere('description', 'LIKE', '%Salary%');
            })
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $amount = config('zedville.monthly_salary', 3952.40);

        // ✅ Get last balance from transactions
        $lastBalance = Transaction1::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        $newBalance = $lastBalance + $amount;
        $salaryDate = Carbon::create(now()->year, now()->month, 6, 8, 0, 0);

        // ✅ Create transaction entry
        $transaction = Transaction1::create([
            'user_id' => $userId,
            'sid' => session()->get('sid'),
            'bank_account_id' => $account->id,
            'transaction_date' => $salaryDate,
            'description' => 'Monthly Salary Credit - ' . now()->format('F Y'),
            'type' => 'credit',
            'category' => 'Income',
            'amount' => $amount,
            'balance' => $newBalance,
            'is_penalty' => false,
        ]);

        // ✅ Trigger mailbox notification
        MailboxScheduler::scheduleForEvent(
            'salary-deposite',
            $userId,
            [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'balance' => $newBalance,
                'month' => now()->format('F Y'),
            ]
        );

        // 💰 Auto Transfer 20% to Emergency Fund if account is active
        if ($account->is_open_emergency_account == 1) {
            $alreadyTransferred = Transaction1::where('user_id', $userId)
                ->where(function ($q) {
                    $q->where('description', 'LIKE', '%Emergency Fund%')
                        ->orWhere('description', 'LIKE', '%Savings Account%');
                })
                ->where('category', 'Savings')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->exists();

            if (!$alreadyTransferred) {
                $emmsavingsPct = config('zedville.emargency_fund_persentage', 20);
                $savingsAmount = round($amount * ($emmsavingsPct / 100), 2);

                if ($newBalance >= $savingsAmount) {
                    $newBalance = max(0, $newBalance - $savingsAmount);

                    // Update account balance
                    $account->emergency_fund_account_amount = ($account->emergency_fund_account_amount ?? 0) + $savingsAmount;
                    $account->save();

                    // Create auto-transfer debit transaction
                    Transaction1::create([
                        'user_id' => $userId,
                        'sid' => session()->get('sid'),
                        'bank_account_id' => $account->id,
                        'transaction_date' => Carbon::create(now()->year, now()->month, 6, 8, 5, 0),
                        'description' => 'Auto Transfer to Emergency Fund',
                        'type' => 'debit',
                        'category' => 'Savings',
                        'amount' => $savingsAmount,
                        'balance' => $newBalance,
                        'is_penalty' => false,
                    ]);
                }
            }
        }
    }*/
    public function creditMonthlySalary($userId)
    {
        // =========================================================
        // Monthly Salary Credit
        // Salary is credited on/after the 6th of every month
        // Business timezone: Europe/Berlin
        // =========================================================

        $businessNow = Carbon::now('Europe/Berlin');

        // Salary can only be credited from the 6th onward
        if ($businessNow->day < 6) {
            return;
        }

        // =========================================================
        // Find user's bank account
        // =========================================================

        $account = BankAccount::where('student_id', $userId)->first();

        if (!$account) {
            return;
        }

        // =========================================================
        // Salary transaction date
        // Salary is officially dated on the 6th at 08:00 Berlin time
        // =========================================================

        /*$salaryDate = $businessNow->copy()
            ->startOfDay()
            ->setTime(8, 0, 0);*/
        $salaryDate = Carbon::create(
            $businessNow->year,
            $businessNow->month,
            6,
            8,
            0,
            0,
            'Europe/Berlin'
        );

        // =========================================================
        // Prevent duplicate salary for the same month
        // =========================================================

        $alreadyCredited = Transaction1::where('user_id', $userId)
            ->where(function ($query) {
                $query->where('category', 'Income')
                    ->orWhere('description', 'LIKE', '%Salary%');
            })
            ->whereMonth('transaction_date', $businessNow->month)
            ->whereYear('transaction_date', $businessNow->year)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        // =========================================================
        // Monthly salary amount
        // =========================================================

        $amount = config('zedville.monthly_salary', 3952.40);

        // =========================================================
        // IMPORTANT:
        // Get the balance from the latest transaction BEFORE
        // the salary transaction date.
        //
        // We do NOT use orderBy('id', 'desc') alone because the
        // highest transaction ID is not always the transaction
        // that represents the balance immediately before salary.
        // =========================================================

        $lastBalance = Transaction1::where('user_id', $userId)
            ->where('transaction_date', '<', $salaryDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        // Make sure the balance is numeric
        $lastBalance = (float) $lastBalance;

        // =========================================================
        // Add monthly salary to previous balance
        // Example:
        // Previous balance = €70.00
        // Salary           = €3952.40
        // New balance      = €4022.40
        // =========================================================

        $newBalance = round($lastBalance + $amount, 2);

        // =========================================================
        // Create Salary Transaction
        // =========================================================

        $transaction = Transaction1::create([
            'user_id' => $userId,
            'sid' => session()->get('sid'),
            'bank_account_id' => $account->id,
            'transaction_date' => $salaryDate,
            'description' => 'Monthly Salary Credit - ' . $businessNow->format('F Y'),
            'type' => 'credit',
            'category' => 'Income',
            'amount' => $amount,
            'balance' => $newBalance,
            'is_penalty' => false,
        ]);

        // =========================================================
        // Trigger mailbox notification
        // =========================================================

        MailboxScheduler::scheduleForEvent(
            'salary-deposite',
            $userId,
            [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'balance' => $newBalance,
                'month' => $businessNow->format('F Y'),
            ]
        );

        // =========================================================
        // Auto Transfer 20% to Emergency Fund
        // =========================================================

        if ($account->is_open_emergency_account == 1) {

            // Prevent duplicate Emergency Fund transfer
            $alreadyTransferred = Transaction1::where('user_id', $userId)
                ->where(function ($q) {
                    $q->where('description', 'LIKE', '%Emergency Fund%')
                        ->orWhere('description', 'LIKE', '%Savings Account%');
                })
                ->where('category', 'Savings')
                ->whereMonth('transaction_date', $businessNow->month)
                ->whereYear('transaction_date', $businessNow->year)
                ->exists();

            if (!$alreadyTransferred) {

                $emmsavingsPct = config(
                    'zedville.emargency_fund_persentage',
                    20
                );

                $savingsAmount = round(
                    $amount * ($emmsavingsPct / 100),
                    2
                );

                // Make sure there is enough balance
                if ($newBalance >= $savingsAmount) {

                    // Deduct Emergency Fund amount from running balance
                    $newBalance = round(
                        $newBalance - $savingsAmount,
                        2
                    );

                    // Update Emergency Fund account balance
                    $account->emergency_fund_account_amount =
                        ($account->emergency_fund_account_amount ?? 0)
                        + $savingsAmount;

                    $account->save();

                    // =================================================
                    // Create Emergency Fund Debit Transaction
                    // =================================================

                    Transaction1::create([
                        'user_id' => $userId,
                        'sid' => session()->get('sid'),
                        'bank_account_id' => $account->id,
                        //'transaction_date' => $businessNow->copy()
                        //->startOfDay()
                        //->setTime(8, 5, 0),
                        'transaction_date' => $salaryDate->copy()->addMinutes(5),
                        'description' => 'Auto Transfer to Emergency Fund',
                        'type' => 'debit',
                        'category' => 'Savings',
                        'amount' => $savingsAmount,
                        'balance' => $newBalance,
                        'is_penalty' => false,
                    ]);
                }
            }
        }
    }
    protected function processAutoDebits($userId)
    {
        $today = now()->toDateString();

        $requests = AutoDebitRequest::where('user_id', $userId)
            ->whereDate('startDate', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('endDate')->orWhere('endDate', '>=', $today);
            })
            ->get();

        foreach ($requests as $request) {
            $startDay = \Carbon\Carbon::parse($request->startDate)->day;

            if (now()->day != $startDay)
                continue;

            $alreadyDebited = Transaction::where('user_id', $userId)
                ->where('description', 'LIKE', 'Auto Debit - ' . $request->type . '%')
                ->whereMonth('txn_date', now()->month)
                ->whereYear('txn_date', now()->year)
                ->exists();

            if ($alreadyDebited)
                continue;

            $availableBalance = Transaction::where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->value('balance') ?? 0;

            if ($availableBalance < $request->amount)
                continue;

            $newBalanceUser = max(0, $availableBalance - $request->amount);

            Transaction::create([
                'user_id' => $userId,
                'sid' => session()->get('sid'),
                'type' => $request->type,
                'txn_date' => now()->toDateString(),
                'value_date' => now()->toDateString(),
                'description' => 'Auto Debit - ' . $request->type . ' to ' . $request->accountno,
                'ref_no' => 'AUTO-DEBIT-' . uniqid(),
                'debit' => $request->amount,
                'credit' => 0,
                'balance' => $newBalanceUser,
            ]);

            $provider = BankAccount::where('primary_savings_account_number', $request->backaccountnumber)->first();
            if ($provider) {
                $lastBalanceProvider = Transaction::where('user_id', $provider->student_id)
                    ->orderBy('id', 'desc')
                    ->value('balance') ?? 0;

                Transaction::create([
                    'user_id' => $provider->student_id,
                    'sid' => session()->get('sid'),
                    'type' => 'Credit',
                    'txn_date' => now()->toDateString(),
                    'value_date' => now()->toDateString(),
                    'description' => 'Auto Debit Payment from User ' . $userId,
                    'ref_no' => 'AUTO-DEBIT-CREDIT-' . uniqid(),
                    'debit' => 0,
                    'credit' => $request->amount,
                    'balance' => $lastBalanceProvider + $request->amount,
                ]);
            }
        }
    }


    //public function bank_statement_show(StatementGenerator $generator)
/*public function bank_statement_show(StatementGenerator $generator, Request $request)
{
    $user = Auth::user();

    // ✅ Date range filter
    $startDate = $request->input('from_date') 
        ? Carbon::parse($request->input('from_date'))->startOfDay()
        : now()->startOfMonth();

    $endDate = $request->input('to_date') 
        ? Carbon::parse($request->input('to_date'))->endOfDay()
        : now()->endOfMonth();

    $statement = BankStatement::where('user_id', $user->id)
        ->where('month', now()->month)
        ->where('year', now()->year)
        ->first();

    $data = json_decode($statement->statement_data, true);

    // ✅ Filter statement transactions by selected date range
    $data['transactions'] = collect($data['transactions'])
        ->filter(function ($txn) use ($startDate, $endDate) {
            $txnDate = Carbon::parse($txn['date']);
            return $txnDate->between($startDate, $endDate);
        })
        ->values()
        ->toArray();

    // ✅ Sort by type (credit before debit)
    usort($data['transactions'], function ($a, $b) {
        $typeCompare = strcmp($a['type'], $b['type']);
        if ($typeCompare !== 0) return $typeCompare;
        return strtotime($a['date']) <=> strtotime($b['date']);
    });

    // ✅ Running balance
    $runningBalance = 0.0;
    foreach ($data['transactions'] as &$txn) {
        $amount = (float) $txn['amount'];
        $runningBalance += strtolower($txn['type']) === 'credit' ? $amount : -$amount;
        $txn['balance'] = $runningBalance;
    }
    unset($txn);

    // ✅ Filter Transactions Based on Date Range
    $transactions = Transaction1::where('user_id', $user->id)
        ->whereBetween('transaction_date', [$startDate, $endDate])
        ->whereNotIn('category', ['Income', 'Savings', 'Needs'])
        ->orderBy('transaction_date', 'asc')
        ->get();

    return view('bank.statement', [
        'user' => $user,
        'statement' => $data,
        'transactions' => $transactions,
    ]);
}*/
    public function ensureMonthlyBills(User $user)
    {
        // 🔒 Only generate bills on or after the 7th of the month
        if (now()->day < 7) {
            return;
        }

        $fixedTransactions = [
            ['day' => 7, 'description' => 'Utility - Electricity', 'amount' => rand(60, 90)],
            ['day' => 7, 'description' => 'Utility - Water', 'amount' => rand(30, 60)],
            ['day' => 7, 'description' => 'Internet', 'amount' => 20],
            ['day' => 7, 'description' => 'Rent', 'amount' => 300],
            ['day' => 7, 'description' => 'School Fees', 'amount' => 1000],
        ];

        $today = now();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        foreach ($fixedTransactions as $item) {
            $billDay = $item['day'] ?? 7;

            // Only process if today is on or past this bill's day
            if ($today->day < $billDay) {
                continue;
            }

            $exists = Transaction1::where('user_id', $user->id)
                ->where('description', $item['description'])
                ->whereYear('transaction_date', $today->year)
                ->whereMonth('transaction_date', $today->month)
                ->exists();

            if ($exists) {
                continue;
            }

            $txnDate = Carbon::create($today->year, $today->month, $billDay, 9, 0, 0);

            $latestTxn = Transaction1::where('user_id', $user->id)
                ->latest('id')
                ->first();

            $lastBalance = $latestTxn ? $latestTxn->balance : 0;
            $newBalance = max(0, $lastBalance - $item['amount']);

            // ✅ CATEGORY RULE
            $needsItems = [
                'Utility - Electricity',
                'Utility - Water',
                'Internet',
                'Rent',
                'School Fees'
            ];

            $category = in_array($item['description'], $needsItems) ? 'Needs' : 'Wants';
            Transaction1::create([
                'user_id' => $user->id,
                'sid' => session()->get('sid'),
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => $txnDate,
                'description' => $item['description'],
                'type' => 'debit',
                'category' => $category,
                'amount' => $item['amount'],
                'balance' => $newBalance,
                'is_penalty' => false,
            ]);
        }
    }

    public function bank_statement_show(StatementGenerator $generator, Request $request)
    {
        $user = Auth::user();
        $this->creditMonthlySalary($user->id);
        $this->ensureMonthlyBills($user);

        $today = now();

        // 🔹 Check last day of current month
        $isLastDayOfMonth = $today->isLastOfMonth();

        // 🔹 Penalty already applied this month?
        $penaltyExists = Transaction1::where('user_id', $user->id)
            ->where('category', 'Penalty')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->exists();

        // ✅ APPLY PENALTY ONLY ON LAST DAY OF MONTH
        if ($isLastDayOfMonth && !$penaltyExists) {
            $this->banks_penalty($generator, $user);
        }


        // ✅ Date range filter
        $startDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : now()->endOfMonth();

        $statement = BankStatement::where('user_id', $user->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        if (!$statement) {
            $data = [
                'month' => now()->month,
                'year' => now()->year,
                'transactions' => [],
                'summary' => [
                    'totalIncome' => 0,
                    'totalExpense' => 0,
                    'closingBalance' => 0,
                    'totalNeeds' => 0,
                    'totalWants' => 0,
                ]
            ];
        } else {
            $data = json_decode($statement->statement_data, true);

            // Ensure required keys exist
            $data['month'] = $data['month'] ?? now()->month;
            $data['year'] = $data['year'] ?? now()->year;
            $data['transactions'] = $data['transactions'] ?? [];
            $data['summary'] = $data['summary'] ?? [
                'totalIncome' => 0,
                'totalExpense' => 0,
                'closingBalance' => 0,
                'totalNeeds' => 0,
                'totalWants' => 0,
            ];
        }



        // ✅ Filter statement transactions by selected date range
        $data['transactions'] = collect($data['transactions'])
            ->filter(function ($txn) use ($startDate, $endDate) {
                $txnDate = Carbon::parse($txn['date']);
                return $txnDate->between($startDate, $endDate);
            })
            ->values()
            ->toArray();

        // ✅ Sort by type (credit before debit)
        usort($data['transactions'], function ($a, $b) {
            $typeCompare = strcmp($a['type'], $b['type']);
            if ($typeCompare !== 0)
                return $typeCompare;
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        // ✅ Running balance
        $runningBalance = 0.0;
        foreach ($data['transactions'] as &$txn) {
            $amount = (float) $txn['amount'];
            $runningBalance += strtolower($txn['type']) === 'credit' ? $amount : -$amount;
            $txn['balance'] = $runningBalance;
        }
        unset($txn);

        // ✅ Filter Transactions Based on Date Range
        $transactions = Transaction1::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            //->whereNotIn('category', ['Income', 'Savings', 'Needs'])
            //->whereNotIn('category', ['Income', 'Needs'])
            //->whereNotIn('category', ['Income'])
            //->whereNotIn('description', ['GroceriesOmnivore', 'GroceriesPescatarian', 'GroceriesVegan', 'GroceriesVegetarian'])
            //->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'DESC')
            ->get();
        /* ============================
            📊 RE-CALCULATE SUMMARY
            ============================ */

        $totalIncome = 0;
        $totalNeeds = 0;
        $totalWants = 0;
        $totalSavings = 0;

        foreach ($transactions as $txn) {

            if (strtolower($txn->type) === 'credit') {

                $totalIncome += $txn->amount;

            } else {

                switch (strtolower($txn->category)) {

                    case 'needs':
                        $totalNeeds += $txn->amount;
                        break;

                    case 'wants':
                    case 'penalty': // ✅ penalty counted under wants
                        $totalWants += $txn->amount;
                        break;

                    case 'savings':
                        $totalSavings += $txn->amount;
                        break;
                }
            }
        }


        /*$latestTxn = Transaction1::where('user_id', $user->id)
        ->latest('id') // or latest('created_at') if you track timestamps
        ->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;*/
        /*$latestTxn = Transaction1::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $lastBalance = $latestTxn ? $latestTxn->balance : 0;*/
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $latestTxn = Transaction1::where('user_id', $user->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->latest('id') // or latest('created_at') if you track timestamps
                ->first();
            $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        } else {
            $latestTxn = Transaction1::where('user_id', $user->id)
                ->latest('id') // or latest('created_at') if you track timestamps
                ->first();
            $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        }


        $existingPenalty = Transaction1::where('user_id', $user->id)
            ->where('category', 'Penalty')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->first();
        // ✅ Override statement summary with filtered values
        $data['summary']['totalIncome'] = $totalIncome;
        $data['summary']['totalNeeds'] = $totalNeeds;
        $data['summary']['totalWants'] = $totalWants;
        $data['summary']['totalSavings'] = $totalSavings;

        return view('bank.statement', [
            'user' => $user,
            'statement' => $data,
            'transactions' => $transactions,
            'totalsaving' => $lastBalance,
            'existingPenalty' => $existingPenalty,
        ]);
    }

    public function emengercyfundaccount()
    {
        $user = Auth::user();
        // Example: Get the new amount from request
        //$newAmount = $request->input('amount', 0); // default 0 if not provided


        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $savingsPct = config('zedville.emargency_fund_persentage', 20);
        $savingsAmount = round($salaryamount * ($savingsPct / 100), 2);
        $lastBalance = Transaction1::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;
        //Check balance after savings deduction
        if ($lastBalance < $savingsAmount) {
            return redirect()->back()->with('error', "<strong>Insufficient funds</strong><br>
                The payment will automatically be deducted next month on the same day, after your monthly statement is sent (1st–5th).");
        }
        //Check balance after savings deduction

        $newAmount = $lastBalance - $savingsAmount;
        // Update the bank account for this user
        $updated = BankAccount::where('student_id', $user->id)
            ->update([
                'emergency_fund_account_amount' => $savingsAmount,
                'is_open_emergency_account' => '1',
            ]);
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        $now = Carbon::now();
        Transaction1::create([
            'user_id' => $user->id,
            'sid' => session()->get('sid'),
            'bank_account_id' => $bankAccount->id ?? null,
            'transaction_date' => $now,
            'description' => 'Auto Transfer to Savings Account',
            'type' => 'debit',
            'category' => 'Savings',
            'amount' => $savingsAmount,
            'balance' => $newAmount,
            'is_penalty' => false
        ]);

        app(ParticipationService::class)->award($user->id, 'emergency_fund_open');

        if ($updated) {
            return redirect()->back()->with('success', '
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-900">
        <p class="font-semibold">Emergency fund updated successfully.</p>

        <p class="mt-2"><strong>Thank you for opening your Emergency Fund Account with Universal Bank.</strong></p>
        <p>Your account is now active with:</p>

        <ul class="list-disc ml-6 mt-2">
            <li><strong>Account number:</strong> ' . $bankAccount->emergency_fund_account_number . '</li>
            <li><strong>Initial deposit:</strong> $' . $savingsAmount . '</li>
            <li><strong>Auto-debit:</strong> 20% of monthly salary</li>
        </ul>

        <p class="mt-2">Your automatic transfers have been set up as requested.</p>

        <p class="font-semibold mt-2">Universal Bank</p>
    </div>');
        } else {
            return redirect()->back()->with('error', 'Failed to update emergency fund.');
        }

    }

    public function moneymarketamount()
    {
        /*$user = Auth::user();
        $salaryamount=3952.40;
        $savingsPct=30;
       $savingsAmount = round($salaryamount * ($savingsPct/100), 2);
       $lastBalance = Transaction1::where('user_id', $user->id)
           ->orderBy('id', 'desc')
           ->value('balance') ?? 0;
       if ($lastBalance < $savingsAmount) {
           return redirect()->back()->with('error', 'Insufficient balance.');
       }
       $newAmount = $lastBalance-$savingsAmount;
       // Update the bank account for this user
       $updated = BankAccount::where('student_id', $user->id)
           ->update(['money_market_account_amount' => $savingsAmount,
                   'is_open_money_market_account' => '1',
                   ]);
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
       $now = Carbon::now();   
        Transaction1::create([
           'user_id' => $user->id,
           'bank_account_id' => $bankAccount->id ?? null,
           'transaction_date' =>  $now ,
           'description' => 'Auto Transfer to Money Market Account',
           'type' => 'debit',
           'category' => 'Savings',
           'amount' => $savingsAmount,
           'balance' => $newAmount,
           'is_penalty' => false
       ]);
       if ($updated) {
           return redirect()->back()->with('success', 'Money Market amount updated successfully.');
       } else {
           return redirect()->back()->with('error', 'Failed to update Money Market amount.');
       }*/
        $user = Auth::user();
        $savingsAmount = 0;
        $updated = BankAccount::where('student_id', $user->id)
            ->update([
                'money_market_account_amount' => $savingsAmount,
                'is_open_money_market_account' => '1',
            ]);

        app(ParticipationService::class)->award($user->id, 'money_market_open');


        if ($updated) {
            return redirect()->back()->with('success', 'Money Market Account Open successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to Open Money Market amount.');
        }
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
            ->where(function ($q) {
                $q->where('description', 'Auto Transfer to Savings Account')
                    ->orWhere('description', 'Auto Transfer to Emergency Fund');
            })
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
    public function moneymarketintrest($userId)
    {
        // 1️⃣ Get previous month info
        $previousMonth = Carbon::now()->subMonth();
        $monthName = $previousMonth->format('F');
        $year = $previousMonth->year;

        // 2️⃣ Interest config
        $annualInterestRate = 0.04; // 4% per annum
        $daysInMonth = $previousMonth->daysInMonth;
        $daysInYear = $previousMonth->isLeapYear() ? 366 : 365;
        $dailyRate = $annualInterestRate / $daysInYear;

        // 3️⃣ Get ending balance (latest txn before end of month)
        $endingBalance = Transaction1::where('user_id', $userId)
            ->where('description', 'Auto Transfer to Money Market Account')
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
    /*public function banks_penalty(StatementGenerator $generator){
        $user = Auth::user();
         $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id') // or latest('created_at') if you track timestamps
            ->first();
            $lastBalance = $latestTxn ? $latestTxn->balance : 0;

         $bankAccount = BankAccount::where('student_id', $user->id)->first();
        $monthlySalary = $generator->getActiveBudgetConfig()->monthly_salary ?? 3952.40;
        $wantsThreshold = $monthlySalary * 0.21;

        // Total Wants spent this month
        $totalWants = Transaction1::where('user_id', $user->id)
            ->where('category', 'Wants')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

          // Check if a penalty transaction exists
        $existingPenalty = Transaction1::where('user_id', $user->id)
            ->where('category', 'Penalty')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->first();

            if ($totalWants < $wantsThreshold) {
            // Needs penalty
            if (!$existingPenalty) {
                $penalty = $wantsThreshold - $totalWants;
                $lastBalance -= $penalty;

                $penaltyTxn = Transaction1::create([
                    'user_id' => $user->id,
                    'bank_account_id' => $bankAccount->id ?? null,
                    'transaction_date' => Carbon::now()->toDateTimeString(),
                    'description' => 'Penalty Applied (Wants < 21% of Salary)',
                    'type' => 'debit',
                    'category' => 'Penalty',
                    'amount' => $penalty,
                    'balance' => $lastBalance,
                    'is_penalty' => true
                ]);

                $data['transactions'][] = [
                    'date' => $penaltyTxn->transaction_date,
                    'description' => $penaltyTxn->description,
                    'type' => $penaltyTxn->type,
                    'category' => $penaltyTxn->category,
                    'amount' => $penaltyTxn->amount,
                    'balance' => $penaltyTxn->balance
                ];
            }
        } else {
            // Reverse penalty if exists
            if ($existingPenalty) {
                // Remove penalty from running balance
                 // ✅ Get the latest recorded balance
            $latestTxn = Transaction1::where('user_id', $user->id)
                ->latest('transaction_date')
                ->first();

            $currentBalance = $latestTxn ? $latestTxn->balance : 0;

            // Add penalty back
            $newBalance = $currentBalance + $existingPenalty->amount;

                // Create a reversal transaction
                $reversalTxn = Transaction1::create([
                    'user_id' => $user->id,
                    'bank_account_id' => $bankAccount->id ?? null,
                    'transaction_date' => Carbon::now()->toDateTimeString(),
                    'description' => 'Penalty Reversed (Wants >= 21% of Salary)',
                    'type' => 'credit',
                    'category' => 'Penalty',
                    'amount' => $existingPenalty->amount,
                    'balance' => $newBalance,
                    'is_penalty' => true
                ]);

                // Append reversal to statement
                $data['transactions'][] = [
                    'date' => $reversalTxn->transaction_date,
                    'description' => $reversalTxn->description,
                    'type' => $reversalTxn->type,
                    'category' => $reversalTxn->category,
                    'amount' => $reversalTxn->amount,
                    'balance' => $reversalTxn->balance
                ];
            }
        }

        return redirect()->route('bank.bank_statement_show');
    }*/
    public function banks_penalty(StatementGenerator $generator, $user = null)
    {
        // 🔒 STEP 0: DATE & SECURITY GUARDS
        $today = now();

        // If called directly via route without being end of month
        if (!$today->isLastOfMonth() && !$today->isSameDay($today->copy()->endOfMonth())) {
            if (!app()->runningInConsole() && request()->routeIs('bank.banks_penalty')) {
                return redirect()
                    ->route('bank.bank_statement_show')
                    ->with('error', 'Penalty can only be applied on the last day of the month.');
            }
        }

        $user = $user ?? Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        // STEP 1: GATHER INPUTS
        $monthlySalary = $generator->getActiveBudgetConfig()->monthly_salary ?? config('zedville.monthly_salary', 3952.40);
        $minThreshold = $monthlySalary * 0.91; // 91%
        $targetAmount = $monthlySalary * 0.99; // 99%

        // STEP 2: CALCULATE TOTAL EXPENSES (Debits, excluding existing penalty transactions)
        $totalExpenses = Transaction1::where('user_id', $user->id)
            ->where('type', 'debit')
            ->where('category', '!=', 'Penalty')
            ->where('is_penalty', 0)
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        // STEP 3: CHECK IF PENALTY APPLIES
        if ($totalExpenses >= $minThreshold) {
            if (request()->routeIs('bank.banks_penalty')) {
                return redirect()->route('bank.bank_statement_show');
            }
            return;
        }

        // STEP 4: PENALTY APPLIES - CALCULATE PENALTY AMOUNT
        $penaltyAmount = max(0, $targetAmount - $totalExpenses);

        // safety cap with available balance
        $latestTxn = Transaction1::where('user_id', $user->id)->latest('id')->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        $penaltyAmount = min($penaltyAmount, max(0, $lastBalance));

        if ($penaltyAmount <= 0) {
            if (request()->routeIs('bank.banks_penalty')) {
                return redirect()->route('bank.bank_statement_show');
            }
            return;
        }

        // STEP 4B: SELECT RANDOM PENALTY ITEMS FROM penalty_wants_items TABLE
        $selectedItems = [];
        try {
            $wantsItems = DB::table('penalty_wants_items')->inRandomOrder()->get(['item_name', 'price']);
            $accumulated = 0;

            foreach ($wantsItems as $item) {
                if ($accumulated + $item->price > $penaltyAmount) {
                    break;
                }

                $selectedItems[] = [
                    'name' => $item->item_name,
                    'price' => $item->price
                ];
                $accumulated += $item->price;
            }

            // Add a filler if remaining small amount
            if ($accumulated < $penaltyAmount && $wantsItems->count() > 0) {
                $remaining = $penaltyAmount - $accumulated;
                $selectedItems[] = [
                    'name' => 'More items',
                    'price' => round($remaining, 2)
                ];
                $accumulated += $remaining;
            }
        } catch (\Exception $e) {
            Log::warning('penalty_wants_items query failed or table missing: ' . $e->getMessage());
        }

        // Fallback: If table has no items or nothing was selected, add standard penalty item
        if (empty($selectedItems) && $penaltyAmount > 0) {
            $selectedItems[] = [
                'name' => 'Monthly Budget Penalty (Expenses < 91%)',
                'price' => round($penaltyAmount, 2),
            ];
        }

        // STEP 4C: APPLY PENALTY TRANSACTIONS
        $latestTxn = Transaction1::where('user_id', $user->id)->latest('id')->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;

        foreach ($selectedItems as $item) {
            $lastBalance = max(0, $lastBalance - $item['price']);

            Transaction1::create([
                'user_id' => $user->id,
                'sid' => session()->get('sid'),
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => now(),
                'description' => 'Penalty Item: ' . $item['name'],
                'type' => 'debit',
                'category' => 'Penalty',
                'amount' => $item['price'],
                'balance' => $lastBalance,
                'is_penalty' => true,
            ]);
        }

        // STEP 5: REDIRECT TO STATEMENT IF DIRECT WEB REQUEST
        if (request()->routeIs('bank.banks_penalty')) {
            return redirect()->route('bank.bank_statement_show')->with('success', 'Monthly penalty applied successfully.');
        }
    }

    public function updatePin(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'security_answer' => 'required|in:-1',
            'new_pin' => 'required|digits:4|regex:/^[1-9]{4}$/',
            'confirm_pin' => 'same:new_pin',
        ]);

        //$bankAccount = BankAccount::findOrFail($request->bank_account_id);
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        $bankAccount->update([
            'card_pin' => $request->new_pin,
        ]);

        return back()->with('success', 'PIN updated successfully');
    }

}