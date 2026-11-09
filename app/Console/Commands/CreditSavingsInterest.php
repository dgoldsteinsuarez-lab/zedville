<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pays a month of interest into every open Emergency Fund (2% a year) and
 * Money Market account (4% a year; 5% in the Intermediate, 6% from its third
 * month: ZedDirecto) and Zed Directo savings account (7%), once per account per
 * month - the bank promised it "on the 1st of each month" but nothing ever paid
 * it (bank review, 2026-10-29). A month with money taken out of the Money
 * Market pays it nothing, as its contract says. Scheduled on the 1st for the month that just
 * ended. Zed Directo's deposit is paid at its end (ZedDirecto::endDue).
 */
class CreditSavingsInterest extends Command
{
    protected $signature = 'savings:credit-interest {--write : Pay it (without, only show what would be paid)} {--month= : The month it is for, YYYY-MM (default: last month)}';

    protected $description = 'Pay the monthly interest on the Emergency Fund, Money Market and Zed Directo savings accounts';

    public function handle()
    {
        $month = $this->option('month')
            ? Carbon::createFromFormat('!Y-m', $this->option('month'))
            : now()->startOfMonth()->subMonth();
        $tag = $month->format('Y-m');
        $write = (bool) $this->option('write');
        $paid = 0;

        BankAccount::where(fn ($q) => $q->where('is_open_emergency_account', 1)->orWhere('is_open_money_market_account', 1)->orWhere('is_open_zd_savings', 1))
            ->orderBy('id')->pluck('id')->each(function ($id) use ($tag, $month, $write, &$paid) {
                foreach (array_keys(SavingsLedger::ACCOUNTS) as $key) {
                    if ($key === ZedDirecto::DEPOSIT) {
                        continue;
                    }
                    DB::transaction(function () use ($id, $key, $tag, $month, $write, &$paid) {
                        $account = BankAccount::whereKey($id)->lockForUpdate()->first();
                        if (!$account || !SavingsLedger::isOpen($account, $key)) {
                            return;
                        }
                        $already = SavingsTransaction::where('bank_account_id', $id)->where('account', $key)
                            ->where('category', 'Interest')->where('meta->month', $tag)->exists();
                        if ($key === 'money_market' && SavingsLedger::withdrewIn((int) $account->student_id, $key, $month)) {
                            $this->line("  account {$id} {$key}: no interest, money taken out in {$tag}");

                            return;
                        }
                        // the student's rate in that month (the Money Market rises in the Intermediate's third)
                        $interest = SavingsLedger::monthlyInterest(SavingsLedger::balance($account, $key), $key, User::find($account->student_id), $month);
                        if ($already || $interest <= 0) {
                            return;
                        }
                        $this->line("  account {$id} {$key}: Ƶ{$interest}" . ($write ? '' : ' (dry run)'));
                        if ($write) {
                            SavingsLedger::post($account, $key, 'credit', 'Interest', $interest, 'Interest', ['month' => $tag],
                                $month->copy()->endOfMonth()->setTime(23, 59));
                            $paid++;
                        }
                    });
                }
            });

        $this->comment($write ? "Paid interest {$paid} time(s) for {$tag}." : 'Dry run - nothing written. Add --write to pay it.');

        return self::SUCCESS;
    }
}
