<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\SavingsTransaction;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use App\Models\WantsPlanCharge;
use Carbon\Carbon;

/**
 * What the bank's pages show (bank review, Daniel 2026-10-29): one statement
 * per account and month - opening balance, every line, closing balance and
 * totals that add up - the "This month" box and the month's bills.
 *
 * The current account's lines are transactions1, its running balance in
 * booking order (id); the Emergency Fund's and Money Market's are
 * savings_transactions. A statement lists its lines in that booking order, so
 * each line's balance follows from the one before.
 */
class BankStatementService
{
    /** The bills every student's current account pays on the 7th (BankController::ensureMonthlyBills). */
    public const BILLS = ['Rent', 'School Fees', 'Internet', 'Utility - Electricity', 'Utility - Water'];

    /** Where a current-account debit goes in the totals. */
    public const GROUPS = [
        'Needs' => 'needs',
        'Wants' => 'wants',
        'Penalty' => 'wants',
        'Savings' => 'savings',
        'Own Transfer' => 'savings',
        'Fund Transfer' => 'transfers',
        'Bill Payment' => 'bills',
    ];

    /** The months with a statement: from the bank account's first month to now, newest first. */
    public function months(BankAccount $account): array
    {
        $first = Transaction1::where('user_id', $account->student_id)->min('transaction_date');
        $start = Carbon::parse($first ?? $account->created_at ?? now())->startOfMonth();
        $months = [];
        for ($month = now()->startOfMonth(); $month->gte($start) && count($months) < 36; $month = $month->copy()->subMonthNoOverflow()) {
            $months[] = $month->copy();
        }

        return $months ?: [now()->startOfMonth()];
    }

    /** The accounts with a statement: the current account, and each savings account once it is open. */
    public function accounts(BankAccount $account): array
    {
        $keys = [BankTransferService::CURRENT];
        foreach ([BankTransferService::EMERGENCY, BankTransferService::MONEY_MARKET] as $key) {
            if (SavingsLedger::isOpen($account, $key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * A month of one account: ['lines' => [...], 'opening', 'closing', 'in', 'out', 'groups' => [group => amount]].
     * Each line: date, text (in the student's language), in, out, balance, memo.
     */
    public function month(BankAccount $account, string $key, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $query = $key === BankTransferService::CURRENT
            ? Transaction1::where('user_id', $account->student_id)
            : SavingsTransaction::where('user_id', $account->student_id)->where('account', $key);
        $rows = (clone $query)->whereBetween('transaction_date', [$start, $end])->orderBy('id')->get();

        if ($rows->isEmpty()) {
            $balance = (float) ((clone $query)->where('transaction_date', '<', $start)->orderByDesc('id')->value('balance') ?? 0);

            return ['lines' => [], 'opening' => $balance, 'closing' => $balance, 'in' => 0.0, 'out' => 0.0, 'groups' => []];
        }

        $groups = [];
        // an older order's item lines as one purchase (App\Support\OrderLine)
        $lines = \App\Support\OrderLine::fold($rows)->map(function ($row) use ($key, &$groups) {
            $credit = $row->type === 'credit';
            if (!$credit && $key === BankTransferService::CURRENT) {
                $group = self::GROUPS[$row->category] ?? 'other';
                $groups[$group] = round(($groups[$group] ?? 0) + (float) $row->amount, 2);
            }

            return [
                'date' => Carbon::parse($row->transaction_date),
                'text' => txn_text($row->description),
                'in' => $credit ? (float) $row->amount : null,
                'out' => $credit ? null : (float) $row->amount,
                'balance' => (float) $row->balance,
                'memo' => is_array($row->meta) ? ($row->meta['memo'] ?? null) : null,
            ];
        })->all();

        $first = $rows->first();
        $opening = round((float) $first->balance + ($first->type === 'credit' ? -1 : 1) * (float) $first->amount, 2);

        return [
            'lines' => $lines,
            'opening' => $opening,
            'closing' => (float) $rows->last()->balance,
            'in' => round((float) $rows->where('type', 'credit')->sum('amount'), 2),
            'out' => round((float) $rows->where('type', 'debit')->sum('amount'), 2),
            'groups' => array_filter($groups),
        ];
    }

    /** The "This month" box: salary in, where the money went, what's left in the current account. */
    public function summary(BankAccount $account): array
    {
        $month = $this->month($account, BankTransferService::CURRENT, now());
        $salary = (float) Transaction1::where('user_id', $account->student_id)->where('type', 'credit')->where('category', 'Income')
            ->whereYear('transaction_date', now()->year)->whereMonth('transaction_date', now()->month)->sum('amount');
        $groups = $month['groups'];

        return [
            'salary' => round($salary, 2),
            'needs' => $groups['needs'] ?? 0.0,
            'wants' => $groups['wants'] ?? 0.0,
            'savings' => $groups['savings'] ?? 0.0,
            'other' => round(($groups['transfers'] ?? 0) + ($groups['bills'] ?? 0) + ($groups['other'] ?? 0), 2),
            'left' => BankTransferService::balance($account, BankTransferService::CURRENT),
        ];
    }

    /**
     * This month's bills: each fixed one paid (date, amount), still to come on
     * the 7th, or late - from the 7th, waiting for money: the bank never takes
     * the balance below zero and tries again every day. Then the student's own
     * scheduled transfers and bills still to run.
     */
    public function bills(BankAccount $account): array
    {
        // the health insurance's premium too, once the student has a plan (App\Services\InsuranceService)
        $names = InsuranceService::current($account->student_id) ? [...self::BILLS, InsuranceService::LINE] : self::BILLS;
        $paid = Transaction1::where('user_id', $account->student_id)->where('type', 'debit')
            ->whereIn('description', $names)
            ->whereYear('transaction_date', now()->year)->whereMonth('transaction_date', now()->month)
            ->get()->keyBy('description');

        $fixed = collect($names)->map(fn ($name) => [
            'text' => txn_text($name),
            'paid' => $paid->has($name),
            'amount' => $paid->has($name) ? (float) $paid[$name]->amount : null,
            'date' => $paid->has($name) ? Carbon::parse($paid[$name]->transaction_date) : now()->startOfMonth()->addDays(6),
            'late' => !$paid->has($name) && Carbon::now('Europe/Berlin')->day >= 7,
        ])->all();

        $scheduled = Transfer::where('user_id', $account->student_id)->where('status', 'scheduled')
            ->orderBy('next_run_at')->get();

        // The plan of the month (Spending 4): this month's payments, and any still
        // waiting for money (Daniel, 2026-11-02: "pago en espera me parece perfecto").
        $plans = WantsPlanCharge::with('plan')->where('user_id', $account->student_id)
            ->where(fn ($q) => $q->where('status', 'waiting')
                ->orWhere(fn ($q) => $q->whereYear('due_date', now()->year)->whereMonth('due_date', now()->month)))
            ->orderBy('due_date')->orderBy('id')->get()
            ->map(fn ($charge) => [
                'text' => txn_text(WantsPlanService::LINE . ($charge->plan->name ?? 'Plan')),
                'status' => $charge->status,
                'amount' => (float) $charge->amount,
                'date' => $charge->status === 'charged' ? Carbon::parse($charge->updated_at) : Carbon::parse($charge->due_date),
            ])->all();

        return ['fixed' => $fixed, 'plans' => $plans, 'scheduled' => $scheduled];
    }

    /** The latest lines of the current account, newest first (an older order's items as one purchase). */
    public function recent(BankAccount $account, int $count = 5): array
    {
        $rows = Transaction1::where('user_id', $account->student_id)->orderByDesc('id')->limit($count + 60)->get();

        return \App\Support\OrderLine::fold($rows)->take($count)
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->transaction_date),
                'text' => txn_text($row->description),
                'amount' => (float) $row->amount * ($row->type === 'credit' ? 1 : -1),
                'group' => $row->type === 'credit' ? 'in' : (self::GROUPS[$row->category] ?? 'other'),
            ])->all();
    }
}
