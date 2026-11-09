<?php

namespace App\Http\Controllers;
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
use App\Services\BankTransferService;
use App\Services\DonationService;
use App\Services\EasyActivityService;
use App\Services\SaverService;
use App\Support\Level;
use App\Services\LedgerService;
use App\Services\SavingsLedger;
use App\Services\BankStatementService;
use App\Services\WantsPlanService;
use App\Support\BankLevel;
use App\Models\BankStatement;
use App\Models\ManagePayeeBiller;


class BankController extends Controller
{
    /** The day the monthly salary comes in, Berlin time (creditMonthlySalary). */
    public const SALARY_DAY = 6;

    /**
     * The bank's Overview: the three accounts with what each is for, this
     * month's money, the bills, the latest movements (bank review, 2026-10-29).
     * Before the bank is set up: the steps of setting it up.
     */
    public function index(BankStatementService $statements)
    {
        $user = auth()->user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return view('bank.bank', compact('user'));
        }
        if ($bankAccount->primary_savings_account_amount <= 0) {
            return view('bank.form.bank-direct-deposite-message', compact('user'));
        }
        if (!AutoDebitRequest::where('user_id', $user->id)->exists()) {
            return view('bank.form.auto-debit-authorization-message', compact('user'));
        }

        // this month's salary and bills first, so the page already shows them
        // (it read the balance before them: the first visit showed last month's)
        $this->creditMonthlySalary($user->id);
        $this->ensureMonthlyBills($user);
        WantsPlanService::chargeDue($user->id); // a plan payment waiting for money, once there is
        // the month's unexpected expense after the salary and the bills, if it is due (App\Services\EmergencyService)
        \App\Services\EmergencyService::chargeDue($user);
        \App\Services\EmergencyService::arrive($user);
        $bankAccount->refresh();
        $savings = app(SavingsAccountController::class);

        return view('bank.overview', [
            'user' => $user,
            'bankAccount' => $bankAccount,
            'current' => BankTransferService::balance($bankAccount, BankTransferService::CURRENT),
            'interest' => [
                BankTransferService::EMERGENCY => $savings->emergencyFundInterest($user->id),
                BankTransferService::MONEY_MARKET => $savings->moneymarketintrest($user->id),
            ],
            'summary' => $statements->summary($bankAccount),
            'bills' => $statements->bills($bankAccount),
            'recent' => $statements->recent($bankAccount),
            // the account-opening pop-ups
            'emmsavingsAmount' => round(config('zedville.monthly_salary', 3952.40) * SaverService::fundPercent($user, $bankAccount) / 100, 2),
            // the Intermediate and after: what they saved this month and their share for the fund
            'saver' => Level::saver($user),
            'saved' => SaverService::savedThisMonth($user->id),
            'fundPercent' => SaverService::fundPercent($user, $bankAccount),
            'bankUserName' => $user->name,
            'easyActivities' => app(EasyActivityService::class)->forStudent($user, 'bank_transfer'),
        ]);
    }
    public function store(Request $request, StatementGenerator $generator)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->withErrors('You must be logged in.');
        }
        if (BankAccount::where('student_id', $user->id)->exists()) {
            return redirect()->route('bank.index');
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
        // A student's address is their street and number in Zedville (same
        // as the activation form, Daniel 2026-10-25); typed only without a Class.
        if ($user->isStudent() && ($cityAddress = \App\Services\AddressService::assignForStudent($user))) {
            $validated['homeAddress'] = $cityAddress->load('street')->label();
        }
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

        return redirect()->route('bank.direct_deposite_message')->with('success', __('flash.bank_created'));
    }
    public function direct_deposite_message()
    {
        $user = Auth::user();
        $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-direct-deposite-message', compact('user', 'bankAccount'));
    }
    /** Card & details: the card, the PIN, and the details a friend needs to send the student money. */
    public function my_account()
    {
        $user = auth()->user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->route('bank.index');
        }

        return view('bank.details', compact('user', 'bankAccount'));
    }

    /**
     * Pay & transfer: to a friend (their account number or IBAN and name), between
     * the student's own accounts, a bill, or the monthly donation to a charity
     * (every level: DonationService) - and the scheduled ones still to run.
     * Later dates and standing orders to friends for the older students (BankLevel).
     */
    public function transfer(Request $request, BankTransferService $bank)
    {
        $user = auth()->user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->route('bank.index');
        }
        $to = in_array($request->query('to'), ['friend', 'own', 'bill', 'donation'], true) ? $request->query('to') : 'friend';
        $saved = Beneficiary::where('user_id', $user->id)->orderBy('name')->get();
        $charities = DonationService::saved($user->id);
        $accounts = collect(BankTransferService::FROM_LABELS)
            ->filter(fn ($label, $key) => $key === BankTransferService::CURRENT || SavingsLedger::isOpen($bankAccount, $key))
            ->map(fn ($label, $key) => [
                'from' => $label,
                'name' => __('bank_ui.' . $key),
                'balance' => BankTransferService::balance($bankAccount, $key),
            ])->all();

        return view('bank.pay', [
            'bankAccount' => $bankAccount,
            'to' => $to,
            'accounts' => $accounts,
            // friends here; the saved charities go with the donation
            'beneficiaries' => $saved->reject(fn ($b) => $charities->contains('id', $b->id))->values(),
            'charities' => $charities,
            'donation' => $donation = DonationService::active($user->id),
            // last school year's, to keep or change when it's set up again
            'lastDonation' => $donation ? null : DonationService::last($user->id),
            'donationRange' => DonationService::range(),
            'billers' => ManagePayeeBiller::where('status', 1)->orderBy('name')->get(),
            'orders' => Transfer::where('user_id', $user->id)->where('status', 'scheduled')->orderBy('next_run_at')->get(),
            'advanced' => BankLevel::advanced($user),
            'mmLock' => $bank->moneyMarketLock($user),
            'easyActivities' => $to === 'friend' ? app(EasyActivityService::class)->forStudent($user, 'bank_transfer') : collect(),
        ]);
    }

    public function transfer_store(Request $request, BankTransferService $bank)
    {
        $data = $request->validate([
            'from_account' => 'required|string',
            'to' => 'required|in:friend,own',
            'own_to' => 'nullable|required_if:to,own|in:current,emergency,money_market',
            'account_number' => 'nullable|required_if:to,friend|string|max:40',
            'beneficiary_name' => 'nullable|required_if:to,friend|string|max:120',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'memo' => 'nullable|string|max:140',
            'type' => 'nullable|in:now,later,recurring',
            'paydate' => 'nullable|required_if:type,later|date|after_or_equal:today',
            'start_date' => 'nullable|required_if:type,recurring|date|after_or_equal:today',
            'end_date' => 'nullable|required_if:type,recurring|date|after:start_date',
            'frequency' => 'nullable|required_if:type,recurring|in:weekly,monthly,yearly',
            'saveAsBeneficiary' => 'nullable|boolean',
        ]);

        // No card PIN (Daniel, 2026-10-29: "better no pin for transfer"): online
        // banking confirms a transfer on a check step; the PIN is for card payments.
        $user = $request->user();
        $back = fn (string $error) => redirect()->route('bank.transfer', ['to' => $data['to']])
            ->withInput()->with('error', __('flash.' . $error));

        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->route('bank.index');
        }
        $from = BankTransferService::key($data['from_account']);
        if (!$from) {
            return $back('transfer_failed');
        }
        if ($data['to'] === 'own') {
            $data['account_number'] = BankTransferService::numberOf($bankAccount, $data['own_to']);
            $data['beneficiary_name'] = $bankAccount->student_name;
        } else {
            $found = $bank->findAccount($data['account_number']);
            if (!$found || !BankTransferService::sameName($data['beneficiary_name'], $found[0]->student_name)) {
                return $back('no_beneficiary');
            }
            if ((int) $found[0]->student_id !== (int) $user->id && !BankTransferService::sameClass($user, (int) $found[0]->student_id)) {
                return $back('other_class');
            }
        }

        $type = $data['type'] ?? 'now';
        $first = match ($type) {
            'later' => Carbon::parse($data['paydate'])->startOfDay(),
            'recurring' => Carbon::parse($data['start_date'])->startOfDay(),
            default => null,
        };
        $transfer = Transfer::create([
            'user_id' => $user->id,
            'sid' => $bankAccount->sid,
            'from_account' => BankTransferService::FROM_LABELS[$from],
            'account_number' => trim($data['account_number']),
            'beneficiary_name' => trim($data['beneficiary_name']),
            'amount' => round((float) $data['amount'], 2),
            'memo' => $data['memo'] ?? null,
            'type' => $type,
            'kind' => 'transfer',
            'status' => 'done',
            'scheduled_at' => $type === 'later' ? $first : null,
            'start_date' => $type === 'recurring' ? $first : null,
            'end_date' => $type === 'recurring' ? Carbon::parse($data['end_date'])->endOfDay() : null,
            'frequency' => $type === 'recurring' ? $data['frequency'] : null,
        ]);

        $result = $first ? $bank->schedule($transfer, $first) : $bank->execute($transfer);
        if (!$result['ok']) {
            $transfer->update(['status' => 'failed', 'failure' => $result['error']]);

            return $back($result['error']);
        }

        if ($data['to'] === 'friend' && $request->boolean('saveAsBeneficiary')) {
            Beneficiary::firstOrCreate(
                ['user_id' => $user->id, 'name' => trim($data['beneficiary_name']), 'account_number' => trim($data['account_number'])],
                ['sid' => $bankAccount->sid, 'bank' => 'Universal Bank']
            );
        }

        $message = $first && $first->gt(now())
            ? __('flash.transfer_scheduled', ['date' => $first->locale(app()->getLocale())->isoFormat('LL')])
            : __('flash.transfer_done');
        // out of the Money Market: what its early withdrawal fee was
        if (!empty($result['fee'])) {
            $message .= ' ' . __('flash.mm_fee_charged', ['fee' => zeds($result['fee'])]);
        }

        return redirect()->route('bank.transfer', ['to' => $data['to']])->with('success', e($message));
    }

    /** Runs one transfer now (kept for callers of the old method; BankTransferService does it). */
    public function processTransfer($transferId, $transactionDate = null)
    {
        $transfer = Transfer::find($transferId);

        return $transfer ? app(BankTransferService::class)->execute($transfer) : ['ok' => false, 'error' => 'transfer_failed'];
    }

    /**
     * Cancels a scheduled or recurring transfer or bill of the student's that
     * hasn't run (out). Not the monthly donation: it can only move to another
     * charity (Daniel, 2026-11-08).
     */
    public function transfer_cancel(Transfer $transfer)
    {
        abort_unless((int) $transfer->user_id === (int) auth()->id(), 404);
        if ($transfer->kind === DonationService::KIND) {
            return redirect()->route('bank.transfer', ['to' => 'donation'])->with('error', __('flash.donation_no_cancel'));
        }
        if ($transfer->status === 'scheduled') {
            $transfer->update(['status' => 'cancelled', 'next_run_at' => null]);
        }

        return back()->with('success', e(__('flash.transfer_cancelled')));
    }

    /**
     * Saves a friend to send money to: their account number (or IBAN) and their
     * name must belong to the same account, as a bank checks it. Before, it
     * looked for a field the form doesn't send and always said "not found".
     */
    public function beneficiary_store(Request $request, BankTransferService $bank)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'account_number' => 'required|string|max:40',
            'for' => 'nullable|in:friend,donation',
        ]);
        $tab = $data['for'] ?? 'friend';
        $back = fn (string $error) => redirect()->route('bank.transfer', ['to' => $tab])->withInput()->with('error', __('flash.' . $error));

        // a charity, for the monthly donation: its name and account number from the Donation Center
        if ($tab === 'donation') {
            $npo = DonationService::npo($data['account_number']);
            if (!$npo || !DonationService::sameName($data['name'], $npo)) {
                return $back('not_a_charity');
            }
            Beneficiary::firstOrCreate(
                ['user_id' => auth()->id(), 'account_number' => trim($data['account_number'])],
                ['name' => trim($data['name']), 'sid' => auth()->user()->sid, 'bank' => 'Charity']
            );

            return redirect()->route('bank.transfer', ['to' => 'donation'])->with('success', e(__('flash.charity_saved')));
        }

        $found = $bank->findAccount($data['account_number']);
        if (!$found || !BankTransferService::sameName($data['name'], $found[0]->student_name)) {
            return $back('no_beneficiary');
        }
        if ((int) $found[0]->student_id === (int) auth()->id()) {
            return $back('beneficiary_is_you');
        }
        if (!BankTransferService::sameClass(auth()->user(), (int) $found[0]->student_id)) {
            return $back('other_class');
        }
        Beneficiary::firstOrCreate(
            ['user_id' => auth()->id(), 'name' => trim($data['name']), 'account_number' => trim($data['account_number'])],
            ['sid' => $found[0]->sid, 'bank' => 'Universal Bank']
        );

        return redirect()->route('bank.transfer', ['to' => 'friend'])->with('success', e(__('flash.beneficiary_saved')));
    }

    public function beneficiary_delete(Beneficiary $beneficiary)
    {
        abort_unless((int) $beneficiary->user_id === (int) auth()->id(), 404);
        $tab = DonationService::npo($beneficiary->account_number) ? 'donation' : 'friend';
        $beneficiary->delete();

        return redirect()->route('bank.transfer', ['to' => $tab])->with('success', e(__('flash.beneficiary_removed')));
    }

    /**
     * Sets up the monthly donation: a standing order to a saved charity, 4-6%
     * of the salary, every month from the first date, no end (DonationService).
     */
    public function donation_store(Request $request, DonationService $donations)
    {
        $data = $request->validate([
            'beneficiary' => 'required|integer',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'start_date' => 'required|date|after_or_equal:today',
        ]);
        $user = $request->user();
        [$min, $max] = DonationService::range();
        $back = fn (string $error) => redirect()->route('bank.transfer', ['to' => 'donation'])->withInput()
            ->with('error', __('flash.' . $error, ['min' => zeds($min), 'max' => zeds($max)]));

        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->route('bank.index');
        }
        $saved = Beneficiary::where('user_id', $user->id)->find($data['beneficiary']);
        $npo = $saved ? DonationService::npo($saved->account_number) : null;
        if (!$npo) {
            return $back('not_a_charity');
        }

        $first = Carbon::parse($data['start_date'])->startOfDay();
        $result = $donations->start($user, $bankAccount, $npo, round((float) $data['amount'], 2), $first);
        if (!isset($result['transfer'])) {
            return $back($result['error']);
        }

        $message = match (true) {
            $first->gt(now()) => __('flash.donation_scheduled', ['date' => $first->locale(app()->getLocale())->isoFormat('LL')]),
            $result['ok'] => __('flash.donation_paid'),
            default => __('flash.donation_first_failed'),
        };

        return redirect()->route('bank.transfer', ['to' => 'donation'])->with($result['ok'] || $first->gt(now()) ? 'success' : 'error', e($message));
    }

    /**
     * The share of the salary that goes to the Emergency Fund, from the next
     * payday: the Intermediate and after choose it every month (SaverService).
     */
    public function fund_percent(Request $request)
    {
        $data = $request->validate(['percent' => ['required', 'integer', 'in:' . implode(',', SaverService::OPTIONS)]]);
        $user = $request->user();
        abort_unless(Level::saver($user), 403);

        $account = BankAccount::where('student_id', $user->id)->firstOrFail();
        $account->emergency_fund_percent = (int) $data['percent'];
        $account->save();

        return redirect()->route('bank.index')->with('success', e(__('flash.fund_percent_saved', ['percent' => $data['percent']])));
    }

    /** Moves the monthly donation to another saved charity: it can't be cancelled. */
    public function donation_change(Request $request, DonationService $donations)
    {
        $data = $request->validate(['beneficiary' => 'required|integer']);
        $user = $request->user();
        $donation = DonationService::active($user->id);
        $saved = Beneficiary::where('user_id', $user->id)->find($data['beneficiary']);
        $npo = $saved ? DonationService::npo($saved->account_number) : null;
        if (!$donation || !$npo) {
            return redirect()->route('bank.transfer', ['to' => 'donation'])->with('error', __('flash.not_a_charity'));
        }
        $donations->moveTo($donation, $npo);

        return redirect()->route('bank.transfer', ['to' => 'donation'])
            ->with('success', e(__('flash.donation_moved', ['name' => ct('activities', $npo->name)])));
    }

    /** Paying a bill is on Pay & transfer now. */
    public function pay_bills()
    {
        return redirect()->route('bank.transfer', ['to' => 'bill']);
    }

    public function store_pay_bill(Request $request, BankTransferService $bank)
    {
        $data = $request->validate([
            'beneficiary_name' => 'required|string|max:191',
            'account_number' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'type' => 'nullable|in:now,later,recurring',
            'paydate' => 'nullable|required_if:type,later|date|after_or_equal:today',
            'start_date' => 'nullable|required_if:type,recurring|date|after_or_equal:today',
            'end_date' => 'nullable|required_if:type,recurring|date|after:start_date',
            'frequency' => 'nullable|required_if:type,recurring|in:weekly,monthly,yearly',
        ]);
        $back = fn (string $error) => redirect()->route('bank.transfer', ['to' => 'bill'])
            ->withInput()->with('error', __('flash.' . $error));

        $userId = auth()->id();
        $bankAccount = BankAccount::where('student_id', $userId)->first();
        if (!$bankAccount) {
            return $back('account_not_found');
        }

        $type = $data['type'] ?? 'now';
        $first = match ($type) {
            'later' => Carbon::parse($data['paydate'])->startOfDay(),
            'recurring' => Carbon::parse($data['start_date'])->startOfDay(),
            default => null,
        };
        $transfer = Transfer::create([
            'user_id' => $userId,
            'sid' => $bankAccount->sid,
            'from_account' => BankTransferService::FROM_LABELS[BankTransferService::CURRENT],
            'account_number' => trim($data['account_number']),
            'beneficiary_name' => trim($data['beneficiary_name']),
            'amount' => round((float) $data['amount'], 2),
            'type' => $type,
            'kind' => 'bill',
            'status' => 'done',
            'scheduled_at' => $type === 'later' ? $first : null,
            'start_date' => $type === 'recurring' ? $first : null,
            'end_date' => $type === 'recurring' ? Carbon::parse($data['end_date'])->endOfDay() : null,
            'frequency' => $type === 'recurring' ? $data['frequency'] : null,
        ]);

        $result = $first ? $bank->schedule($transfer, $first) : $bank->execute($transfer);
        if (!$result['ok']) {
            $transfer->update(['status' => 'failed', 'failure' => $result['error']]);

            return $back($result['error']);
        }

        $message = $first && $first->gt(now())
            ? __('flash.bill_scheduled_on', ['date' => $first->locale(app()->getLocale())->isoFormat('LL')])
            : __('flash.bill_paid');

        return redirect()->route('bank.transfer', ['to' => 'bill'])->with('success', e($message));
    }


    /** The old statement pages read a table nothing writes to (always "no statements"): the statement page. */
    public function bank_statements(Request $request)
    {
        return redirect()->route('bank.bank_statement_show');
    }

    public function viewStatement($year, $month)
    {
        $valid = ctype_digit((string) $year) && ctype_digit((string) $month) && (int) $month >= 1 && (int) $month <= 12;

        return redirect()->route('bank.bank_statement_show', $valid ? ['month' => sprintf('%04d-%02d', $year, $month)] : []);
    }

    public function help()
    {
        return view('bank.bank-help');
    }
    /** These pages showed sample data (a "John Smith", buttons that did nothing): their real versions. */
    public function manage_payee()
    {
        return redirect()->route('bank.transfer', ['to' => 'bill']);
    }

    public function schedule_transfers()
    {
        return redirect()->route('bank.transfer');
    }

    public function recurring_payment()
    {
        return redirect()->route('bank.transfer');
    }

    public function payment_history()
    {
        return redirect()->route('bank.bank_statement_show');
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
        // Sent again (the old mail's link, a stale tab): the bank is running
        // already - it paid a second salary and started the balance again from 0.
        if (Transaction1::where('user_id', $user->id)->exists()) {
            return redirect()->route('bank.index')->with('success', e(__('flash.direct_deposit_already')));
        }
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
            ->with('success', __('flash.salary_created'));
        // ✅ Redirect to mailbox page with success message
        /*return redirect()
            ->route('profile.mailbox') // change to your actual mailbox route name
            ->with('success', 'Auto-debit authorized successfully and message added to your mailbox.');*/
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
            return redirect()->back()->with('error', __('flash.already_submitted'));
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

        return redirect()->back()->with('success', __('flash.form_submitted'));

    }
    public function creditMonthlySalary($userId)
    {
        // =========================================================
        // Monthly Salary Credit
        // Salary is credited on/after the 6th of every month
        // Business timezone: Europe/Berlin
        // =========================================================

        $businessNow = Carbon::now('Europe/Berlin');

        // Salary can only be credited from the 6th onward
        if ($businessNow->day < self::SALARY_DAY) {
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
            self::SALARY_DAY,
            8,
            0,
            0,
            'Europe/Berlin'
        );

        // =========================================================
        // Prevent duplicate salary for the same month
        // =========================================================

        if (self::salaryCredited($userId, $businessNow)) {
            return;
        }

        // =========================================================
        // Monthly salary, then the Emergency Fund's share (bank review,
        // 2026-10-29): under a lock on the bank account so two page loads at
        // once can't pay it twice, and on top of the latest line - the
        // balance every screen shows - so no line drops out of it.
        // =========================================================

        $amount = (float) config('zedville.monthly_salary', 3952.40);

        $transaction = DB::transaction(function () use ($userId, $account, $amount, $salaryDate, $businessNow) {
            $locked = BankAccount::whereKey($account->id)->lockForUpdate()->first();
            if (self::salaryCredited($userId, $businessNow)) {
                return null;
            }

            $salary = LedgerService::post($userId, 'credit', 'Income', $amount, 'Monthly Salary Credit - ' . $businessNow->format('F Y'), [
                'sid' => $locked->sid,
                'bank_account_id' => $locked->id,
                'transaction_date' => $salaryDate,
            ]);

            // Auto Transfer to the Emergency Fund: 20%, or from the Intermediate
            // on the share the student chose (SaverService; 0% sends nothing)
            $fundPercent = SaverService::fundPercent(User::find($userId), $locked);
            if (SavingsLedger::isOpen($locked, 'emergency') && $fundPercent > 0) {
                $alreadyTransferred = Transaction1::where('user_id', $userId)
                    ->where(fn ($q) => $q->where('description', 'LIKE', '%Emergency Fund%')->orWhere('description', 'LIKE', '%Savings Account%'))
                    ->where('category', 'Savings')
                    ->whereMonth('transaction_date', $businessNow->month)
                    ->whereYear('transaction_date', $businessNow->year)
                    ->exists();
                $savingsAmount = round($amount * $fundPercent / 100, 2);

                if (!$alreadyTransferred && (float) $salary->balance >= $savingsAmount) {
                    $when = $salaryDate->copy()->addMinutes(5);
                    LedgerService::post($userId, 'debit', 'Savings', $savingsAmount, 'Auto Transfer to Emergency Fund', [
                        'sid' => $locked->sid,
                        'bank_account_id' => $locked->id,
                        'transaction_date' => $when,
                    ]);
                    SavingsLedger::post($locked, 'emergency', 'credit', 'Monthly transfer', $savingsAmount, 'Monthly transfer from your current account', null, $when);
                }
            }

            // A plan of the month waiting for money is paid right after it, after
            // the Emergency Fund's share (Daniel, 2026-11-02: "si el salario no ha
            // llegado aún, se descuenta justo cuando llegue el salario").
            WantsPlanService::chargeDue($userId, $salaryDate->copy()->addMinutes(10)->max($businessNow));

            return $salary;
        });

        if (!$transaction) {
            return;
        }

        // the Intermediate's saver bonus comes with the salary (App\Services\SaverBonus)
        \App\Services\SaverBonus::payDue(User::find($userId));

        // =========================================================
        // Trigger mailbox notification
        // =========================================================

        MailboxScheduler::scheduleForEvent(
            'salary-deposite',
            $userId,
            [
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'balance' => (float) $transaction->balance,
                'month' => $businessNow->format('F Y'),
            ]
        );
    }

    /** Whether the salary of $month (Berlin's month, like the credit) is in. */
    public static function salaryCredited(int $userId, Carbon $month): bool
    {
        return Transaction1::where('user_id', $userId)
            ->where(fn ($q) => $q->where('category', 'Income')->orWhere('description', 'LIKE', '%Salary%'))
            ->whereMonth('transaction_date', $month->month)
            ->whereYear('transaction_date', $month->year)
            ->exists();
    }

    /**
     * The bills of the 7th and what each costs, [lowest, highest]: the
     * utilities change every month. In the Intermediate's second month they
     * rise (App\Services\PriceRise::BILLS).
     */
    public static function billAmounts(): array
    {
        $rent = (float) config('zedville.rent', 300);
        $school = (float) config('zedville.school_fees', 1000);

        return [
            'Utility - Electricity' => [60, 90],
            'Utility - Water' => [30, 60],
            'Internet' => [20, 20],
            'Rent' => [$rent, $rent],
            'School Fees' => [$school, $school],
        ];
    }

    public function ensureMonthlyBills(User $user)
    {
        // the City Hall's letter about the new prices comes before the bills that have them;
        // in the third month, the bank's and Zed Directo's
        \App\Services\PriceRise::announce($user);
        \App\Services\ZedDirecto::announce($user);
        // loan instalments due (the 7th), and late ones as soon as there is money (App\Services\LoanService)
        \App\Services\LoanService::chargeDue($user);
        // the credit card's statement, paid from the 7th as the student chose (App\Services\CreditCardService)
        \App\Services\CreditCardService::settle($user);

        // 🔒 Only generate bills on or after the 7th of the month (Berlin's day,
        // like the scheduler that also calls this)
        if (Carbon::now('Europe/Berlin')->day < 7) {
            return;
        }

        $fixedTransactions = [];
        foreach (self::billAmounts() as $description => [$low, $high]) {
            $amount = $low == $high ? $low : rand((int) $low, (int) $high);
            $fixedTransactions[] = ['day' => 7, 'description' => $description,
                'amount' => \App\Services\PriceRise::price($user, (float) $amount, \App\Services\PriceRise::BILLS[$description])];
        }

        $today = now();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        // no bank yet: its bills start with it (a line before it blocked that month's real bills)
        if (!$bankAccount) {
            return;
        }

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

            // tried again every day until the money is there (no balance below
            // zero): a bill paid after its day is dated the day it is paid
            $txnDate = $today->day > $billDay ? now() : Carbon::create($today->year, $today->month, $billDay, 9, 0, 0);

            // ✅ CATEGORY RULE
            $needsItems = [
                'Utility - Electricity',
                'Utility - Water',
                'Internet',
                'Rent',
                'School Fees'
            ];

            $category = in_array($item['description'], $needsItems) ? 'Needs' : 'Wants';

            DB::transaction(function () use ($user, $bankAccount, $txnDate, $item, $category, $today) {
                BankAccount::whereKey($bankAccount->id)->lockForUpdate()->first();
                $again = Transaction1::where('user_id', $user->id)
                    ->where('description', $item['description'])
                    ->whereYear('transaction_date', $today->year)
                    ->whereMonth('transaction_date', $today->month)
                    ->exists();
                if ($again) {
                    return;
                }
                \App\Services\LedgerService::post(
                    $user->id,
                    'debit',
                    $category,
                    (float) $item['amount'],
                    $item['description'],
                    [
                        'sid' => $user->sid,
                        'bank_account_id' => $bankAccount->id ?? null,
                        'transaction_date' => $txnDate,
                        'only_if_enough' => true,
                    ]
                );
            });
        }

        // the health insurance's premium, from the Intermediate (App\Services\InsuranceService);
        // whoever ended their first month without a plan gets Basic first
        \App\Services\InsuranceService::assignDefault($user);
        \App\Services\InsuranceService::chargePremium($user);

        // The month's shopping from the student's consumer survey, with the bills
        // (Daniel, 2026-11-08): it was only charged the month the survey was first
        // filled, so nobody reached the 91% the Spending activities are made for.
        $this->chargeMonthlyShopping($user, $bankAccount, $today);
    }

    /**
     * The habits of the survey in effect (App\Models\ConsumerSurvey: one a
     * month, applied from the next), charged as the survey's first month was
     * (ConsumerSurveyController): each line once a month - the month of a first
     * survey already has them - waiting for the money like a bill.
     */
    private function chargeMonthlyShopping(User $user, BankAccount $bankAccount, Carbon $today): void
    {
        $survey = \App\Models\ConsumerSurvey::inEffect($user->id);
        if (!$survey || $survey->isSkipped()) {
            return;
        }

        $groceries = (float) ($survey->basket_cost ?: collect($survey->groceries ?? [])->sum('total_price'));
        $amounts = ['groceries' => $groceries, 'lunch' => (float) $survey->lunch, 'transport' => (float) $survey->transport,
            'activities' => (float) $survey->activities, 'restaurants' => (float) $survey->restaurants];
        // the Intermediate's new prices (App\Services\PriceRise): the survey keeps the old ones
        if (\App\Services\PriceRise::on($user)) {
            foreach (\App\Services\PriceRise::basket($survey)['lines'] as $field => $line) {
                $amounts[$field] = $line['after'];
            }
        }
        $lines = [
            ['Grocery – ' . ucfirst((string) $survey->diet), 'Grocery – %', $amounts['groceries'], 'Needs'],
            ['School Lunch', 'School Lunch', $amounts['lunch'], 'Needs'],
            ['Transportation', 'Transportation', $amounts['transport'], 'Needs'],
            ['Activities & Hobbies', 'Activities & Hobbies', $amounts['activities'], 'Wants'],
            ['Eating Out', 'Eating Out', $amounts['restaurants'], 'Wants'],
        ];
        $txnDate = $today->day > 7 ? now() : Carbon::create($today->year, $today->month, 7, 9, 0, 0);

        foreach ($lines as [$description, $match, $amount, $category]) {
            if ($amount <= 0) {
                continue;
            }
            DB::transaction(function () use ($user, $bankAccount, $today, $txnDate, $description, $match, $amount, $category) {
                BankAccount::whereKey($bankAccount->id)->lockForUpdate()->first();
                $charged = Transaction1::where('user_id', $user->id)->where('description', 'like', $match)
                    ->whereYear('transaction_date', $today->year)->whereMonth('transaction_date', $today->month)->exists();
                if ($charged) {
                    return;
                }
                LedgerService::post($user->id, 'debit', $category, $amount, $description, [
                    'sid' => $user->sid,
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => $txnDate,
                    'only_if_enough' => true,
                ]);
            });
        }
    }

    /**
     * The statement of one account for one month: opening balance, every line,
     * closing balance, and where the money went (bank review, 2026-10-29: three
     * statement pages, two always empty, the third with "Total Savings" showing
     * the current account and the heading always this month). ?month=YYYY-MM and
     * ?account=current|emergency|money_market; ?from_date= (the dashboard and the
     * monthly mail) picks that date's month.
     */
    public function bank_statement_show(Request $request, BankStatementService $statements)
    {
        $user = auth()->user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->route('bank.index');
        }

        $months = $statements->months($bankAccount);
        $month = $months[0];
        foreach ([[$request->query('month'), '!Y-m'], [$request->query('from_date'), '!Y-m-d']] as [$value, $format]) {
            if (is_string($value) && $value !== '') {
                try {
                    $picked = Carbon::createFromFormat($format, $value)->startOfMonth();
                    $month = $picked->gt(now()) ? $month : $picked;
                } catch (\Throwable $e) {
                    // not a date: the latest month
                }
                break;
            }
        }
        $accounts = $statements->accounts($bankAccount);
        $key = in_array($request->query('account'), $accounts, true) ? $request->query('account') : BankTransferService::CURRENT;

        return view('bank.statement-page', [
            'user' => $user,
            'bankAccount' => $bankAccount,
            'months' => $months,
            'month' => $month,
            'accounts' => $accounts,
            'key' => $key,
            'statement' => $statements->month($bankAccount, $key, $month),
        ]);
    }

    public function banks_penalty(StatementGenerator $generator, $user = null)
    {
        // 🔒 STEP 0: DATE & SECURITY GUARDS
        $today = now();

        // If called directly via route without being end of month
        if (!$today->isLastOfMonth() && !$today->isSameDay($today->copy()->endOfMonth())) {
            if (!app()->runningInConsole() && request()->routeIs('bank.banks_penalty')) {
                return redirect()
                    ->route('bank.bank_statement_show')
                    ->with('error', __('flash.penalty_last_day'));
            }
        }

        $user = $user ?? Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Only a student who never chose the plan of the month pays it: the City
        // Hall's fine, up to 99% of the salary spent, with its letter - at most
        // once a month, never on Autopilot (App\Services\MonthEndPenaltyService,
        // Daniel 2026-11-07). It used to charge everyone who had spent less than
        // 91% of the salary.
        $charged = app(\App\Services\MonthEndPenaltyService::class)->apply($user);

        if (request()->routeIs('bank.banks_penalty')) {
            return $charged
                ? redirect()->route('bank.bank_statement_show')->with('success', __('flash.penalty_applied'))
                : redirect()->route('bank.bank_statement_show');
        }
    }

    public function updatePin(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'security_answer' => 'required|in:-1',
            'new_pin' => 'required|digits:4',
            'confirm_pin' => 'same:new_pin',
        ]);

        //$bankAccount = BankAccount::findOrFail($request->bank_account_id);
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        $bankAccount->update([
            'card_pin' => $request->new_pin,
        ]);

        return back()->with('success', __('flash.pin_updated'));
    }

}