<?php

namespace App\Http\Controllers;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\ManagePayeeBiller;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Auto-debit authorization: the "authorize once, cover Rent/School Fees/
 * one chosen utility" flow, plus the older per-utility forms that still
 * post individually. Extracted out of BankController (planning.md
 * Phase 3 #4 - that controller was 2377 lines; this slice is fully
 * self-contained, sharing only ensureMonthlyBills() with BankController
 * itself).
 */
class AutoDebitController extends Controller
{
    public function auto_debit_authorization_message()
    {
        $user = Auth::user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.auto-debit-authorization-message', compact('user', 'bankAccount'));
    }

    public function auto_debit_authorization_form()
    {
        $user = Auth::user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
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
                'bankaccountnumber' => $request->bankaccountnumber,
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
                'bankaccountnumber' => $request->bankaccountnumber,
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
            // $billers is intentionally not defined in this method (moved
            // verbatim from BankController) - $billers[3] always falls
            // through to the 'SCHOOL-AUTO' default below. Leave this as is:
            // the real "City School" biller's account_number happens to BE
            // the literal string 'SCHOOL-AUTO', and id 3 is actually
            // "Utility - Electricity", not City School - defining $billers
            // and keying by id here would silently substitute Electricity's
            // account number instead.
            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => 'City School',
                'accountno' => $billers[3]->account_number ?? 'SCHOOL-AUTO',
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $user->student_address,
                'bankname' => $request->bankname,
                'bankaccountnumber' => $request->bankaccountnumber,
                'amount' => config('zedville.school_fees', 1000),
                'startDate' => now(),
                'signature' => $user->nickname,
                'date' => now(),
                'tandagree' => 1,
            ]);
        }

        // ✅ Ensure monthly bills are processed only on or after the 7th
        app(BankController::class)->ensureMonthlyBills($user);

        return redirect()
            ->route('bank.consumer_survey_message')
            ->with('success', 'Congratulations, your auto debit is set up. Now complete the survey.');
    }

    public function eletricity_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-eletricity-auto-debit-form', compact('user', 'bankAccount'));
    }

    public function internet_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-internet-auto-debit-form', compact('user', 'bankAccount'));
    }

    public function school_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        return view('bank.form.bank-school-auto-debit-form', compact('user', 'bankAccount'));
    }

    public function water_auto_debit()
    {
        $user = Auth::user();
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
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
            'bankaccountnumber' => 'required|string|max:100',
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
        $validated['sid'] = $user->sid;

        // Get the user's bank account
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        if (!$bankAccount) {
            return redirect()->back()->with('error', 'Bank account not found for this user.');
        }

        // ✅ Check sufficient balance against the real ledger (transactions1),
        // not the legacy, always-empty `transactions` table.
        $availableBalance = LedgerService::currentBalance($user->id);

        if ($availableBalance < $validated['amount']) {
            return redirect()->back()->with('error', 'Insufficient balance for this auto-debit request.');
        }

        // ✅ Store auto debit request
        AutoDebitRequest::create($validated);

        LedgerService::post(
            $user->id,
            'debit',
            $validated['type'] ?? 'Auto Debit',
            (float) $validated['amount'],
            'Transfer to ' . ($validated['type'] ?? '') . ' ' . $validated['accountno'],
            [
                'sid' => $user->sid,
                'bank_account_id' => $bankAccount->id,
                'transaction_date' => now(),
                'floor_at_zero' => true,
            ]
        );

        return redirect()->back()->with('success', 'Form submitted and auto-debit transaction created successfully.');
    }
}
