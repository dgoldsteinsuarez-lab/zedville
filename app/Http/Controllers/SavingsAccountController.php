<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Services\LedgerService;
use App\Services\ParticipationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Emergency Fund and Money Market sub-accounts: opening them (debits the
 * primary balance) and the interest projection shown on the account pages.
 * Extracted out of BankController (planning.md Phase 3 #4) - emergencyFundInterest()/
 * moneymarketintrest() are pure calculation helpers, not HTTP endpoints,
 * called from BankController::index()/my_account() via
 * app(SavingsAccountController::class)->....
 */
class SavingsAccountController extends Controller
{
    public function emengercyfundaccount()
    {
        $user = Auth::user();

        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $savingsPct = config('zedville.emargency_fund_persentage', 20);
        $savingsAmount = round($salaryamount * ($savingsPct / 100), 2);
        $lastBalance = Transaction1::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        if ($lastBalance < $savingsAmount) {
            return redirect()->back()->with('error', "<strong>Insufficient funds</strong><br>
                The payment will automatically be deducted next month on the same day, after your monthly statement is sent (1st–5th).");
        }

        // Update the bank account for this user
        $updated = BankAccount::where('student_id', $user->id)
            ->update([
                'emergency_fund_account_amount' => $savingsAmount,
                'is_open_emergency_account' => '1',
            ]);
        $bankAccount = BankAccount::where('student_id', $user->id)->first();
        LedgerService::post(
            $user->id,
            'debit',
            'Savings',
            (float) $savingsAmount,
            'Auto Transfer to Savings Account',
            [
                'sid' => $user->sid,
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => Carbon::now(),
            ]
        );

        app(ParticipationService::class)->award($user->id, 'emergency_fund_open');
        app(\App\Services\ActivityCompletionService::class)->complete($user->id, 'emergency_fund', 'Emergency Fund');

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
}
