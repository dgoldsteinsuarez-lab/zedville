<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\SavingsTransaction;
use App\Services\LedgerService;
use App\Services\ParticipationService;
use App\Services\SavingsLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Emergency Fund and Money Market sub-accounts: opening them and the interest
 * shown on the account pages. Extracted out of BankController (planning.md
 * Phase 3 #4) - emergencyFundInterest()/moneymarketintrest() are calculation
 * helpers, not HTTP endpoints.
 *
 * Bank review, 2026-10-29: opening an account a second time (a double click, a
 * stale tab) set its balance back - the Emergency Fund to its first deposit
 * while taking that deposit again from the current account, the Money Market
 * to 0. An open account now stays as it is. Interest is worked out on the
 * account's own balance (it used the current account's) and really paid each
 * month (savings:credit-interest).
 */
class SavingsAccountController extends Controller
{
    public function emengercyfundaccount()
    {
        $user = Auth::user();

        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $savingsPct = config('zedville.emargency_fund_persentage', 20);
        $savingsAmount = round($salaryamount * ($savingsPct / 100), 2);

        $result = DB::transaction(function () use ($user, $savingsAmount) {
            $bankAccount = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            if (!$bankAccount) {
                return 'missing';
            }
            if (SavingsLedger::isOpen($bankAccount, 'emergency')) {
                return 'open';
            }
            if (LedgerService::lockedBalance($user->id) < $savingsAmount) {
                return 'poor';
            }

            LedgerService::post($user->id, 'debit', 'Savings', (float) $savingsAmount, 'Auto Transfer to Savings Account', [
                'sid' => $user->sid,
                'bank_account_id' => $bankAccount->id,
                'transaction_date' => Carbon::now(),
            ]);
            $bankAccount->is_open_emergency_account = 1;
            SavingsLedger::post($bankAccount, 'emergency', 'credit', 'Opening deposit', (float) $savingsAmount, 'Opening deposit');

            return $bankAccount;
        });

        if ($result === 'open') {
            return redirect()->back()->with('success', e(__('flash.ef_already_open')));
        }
        if ($result === 'poor') {
            return redirect()->back()->with('error', '<strong>' . e(__('flash.ef_insufficient_title')) . '</strong><br>' . e(__('flash.ef_insufficient_text')));
        }
        if (!$result instanceof BankAccount) {
            return redirect()->back()->with('error', __('flash.ef_failed'));
        }

        app(ParticipationService::class)->award($user->id, 'emergency_fund_open');
        app(\App\Services\ActivityCompletionService::class)->complete($user->id, 'emergency_fund', 'Emergency Fund');

        return redirect()->back()->with('success', '
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-900">
        <p class="font-semibold">' . e(__('flash.ef_updated')) . '</p>

        <p class="mt-2"><strong>' . e(__('flash.ef_thanks')) . '</strong></p>
        <p>' . e(__('flash.ef_active_with')) . '</p>

        <ul class="list-disc ml-6 mt-2">
            <li><strong>' . e(__('flash.ef_account_number')) . '</strong> ' . e($result->emergency_fund_account_number) . '</li>
            <li><strong>' . e(__('flash.ef_initial_deposit')) . '</strong> ' . e(zeds($savingsAmount)) . '</li>
            <li><strong>' . e(__('flash.ef_auto_debit')) . '</strong> ' . e(__('flash.ef_auto_debit_value', ['pct' => config('zedville.emargency_fund_persentage', 20)])) . '</li>
        </ul>

        <p class="mt-2">' . e(__('flash.ef_transfers_set')) . '</p>

        <p class="font-semibold mt-2">Universal Bank</p>
    </div>');
    }

    public function moneymarketamount()
    {
        $user = Auth::user();

        $result = DB::transaction(function () use ($user) {
            $bankAccount = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            if (!$bankAccount) {
                return 'missing';
            }
            if (SavingsLedger::isOpen($bankAccount, 'money_market')) {
                return 'open';
            }
            $bankAccount->is_open_money_market_account = 1;
            $bankAccount->money_market_account_amount = 0;
            $bankAccount->save();

            return 'opened';
        });

        if ($result === 'open') {
            return redirect()->back()->with('success', e(__('flash.mm_already_open')));
        }
        if ($result !== 'opened') {
            return redirect()->back()->with('error', __('flash.mm_failed'));
        }

        app(ParticipationService::class)->award($user->id, 'money_market_open');

        return redirect()->back()->with('success', __('flash.mm_opened'));
    }

    public function emergencyFundInterest($userId)
    {
        return $this->interest((int) $userId, 'emergency');
    }

    public function moneymarketintrest($userId)
    {
        return $this->interest((int) $userId, 'money_market');
    }

    /**
     * What the account earns: the interest it will get on the 1st (a month of
     * its yearly rate on what's in it now), a year's worth at that balance,
     * and the interest it has been paid so far.
     */
    public function interest(int $userId, string $key): array
    {
        $account = BankAccount::where('student_id', $userId)->first();
        $balance = $account && SavingsLedger::isOpen($account, $key) ? SavingsLedger::balance($account, $key) : 0.0;
        // the student's own rate: the Money Market pays 5 % from the Intermediate's third month (ZedDirecto)
        $student = \App\Models\User::find($userId);
        $rate = SavingsLedger::rate($key, $student);
        $previousMonth = Carbon::now()->startOfMonth()->subMonth();

        // the Money Market's contract: a month with money taken out earns nothing
        $withdrawn = $key === 'money_market' && SavingsLedger::withdrewIn($userId, $key, Carbon::now());

        return [
            'withdrawn' => $withdrawn,
            'month' => $previousMonth->format('F'),
            'year' => $previousMonth->year,
            'ending_balance' => round($balance, 2),
            'estimated_interest' => $withdrawn ? 0.0 : SavingsLedger::monthlyInterest($balance, $key, $student),
            'annual_projection' => round($balance * $rate, 2),
            'annual_rate' => $rate * 100,
            'earned' => round((float) SavingsTransaction::where('user_id', $userId)->where('account', $key)
                ->where('category', 'Interest')->sum('amount'), 2),
            'next_payment_date' => Carbon::now()->startOfMonth()->addMonth()->locale(app()->getLocale())->isoFormat('LL'),
        ];
    }
}
