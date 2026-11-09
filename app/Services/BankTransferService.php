<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Every movement of money the student starts in the bank - a transfer to a
 * friend, between their own accounts, or a bill payment - now or when it is
 * due (bank review, Daniel 2026-10-29). Before, the same row was read twice and
 * saved twice (Emergency Fund to itself doubled the money), scheduled ones ran
 * again every minute and recurring ones looped forever, and the balance was
 * only checked when the form was sent.
 *
 * Here: both bank accounts are locked (lowest id first), the balance is checked
 * at the moment the money moves, an account can't pay into itself, a savings
 * account must be open to receive or send, a friend must be at the same school,
 * and both sides get a line (current account: transactions1 through
 * LedgerService; Emergency Fund / Money Market: SavingsLedger).
 *
 * Results: ['ok' => true] or ['ok' => false, 'error' => a flash.* key].
 */
class BankTransferService
{
    public const CURRENT = 'current';
    public const EMERGENCY = 'emergency';
    public const MONEY_MARKET = 'money_market';

    /** What the forms send as from_account (and old rows keep), by account. */
    public const FROM_LABELS = [
        self::CURRENT => 'Primary Savings Account',
        self::EMERGENCY => 'Emergency Fund Account',
        self::MONEY_MARKET => 'Money Market Account',
    ];

    /** The bank lines of a move between the student's own accounts (lang/<locale>/transactions.php 'lines'). */
    public const OWN_TO = [
        self::CURRENT => 'Transfer to your current account',
        self::EMERGENCY => 'Transfer to your Emergency Fund',
        self::MONEY_MARKET => 'Transfer to your Money Market Account',
    ];
    public const OWN_FROM = [
        self::CURRENT => 'Transfer from your current account',
        self::EMERGENCY => 'Transfer from your Emergency Fund',
        self::MONEY_MARKET => 'Transfer from your Money Market Account',
    ];

    /**
     * Not spending for the month-end penalty (95% of the salary) and the Money
     * Market's 91%: money moved
     * between accounts or people, and an admin's corrections. The monthly 20% to
     * the Emergency Fund ('Savings') does count: it is the budget's savings part.
     */
    public const NOT_SPENDING = ['Penalty', 'Fund Transfer', 'Own Transfer', 'Reversal', 'Admin Adjustment'];

    /** The Money Market's line for its early withdrawal fee (SavingsLedger::withdrawalFee). */
    public const WITHDRAWAL_FEE_LINE = 'Early withdrawal fee';

    /** The account a from_account value means: one of the labels above, or already a key. */
    public static function key(?string $fromAccount): ?string
    {
        if (isset(self::FROM_LABELS[(string) $fromAccount])) {
            return $fromAccount;
        }
        $key = array_search($fromAccount, self::FROM_LABELS, true);

        return $key === false ? null : $key;
    }

    public static function numberOf(BankAccount $account, string $key): ?string
    {
        return $key === self::CURRENT ? $account->primary_savings_account_number : $account->{SavingsLedger::ACCOUNTS[$key]['number']};
    }

    /** What's in one of the student's accounts (current: the latest line; savings: bank_accounts). */
    public static function balance(BankAccount $account, string $key): float
    {
        return $key === self::CURRENT
            ? round(LedgerService::currentBalance((int) $account->student_id), 2)
            : SavingsLedger::balance($account, $key);
    }

    /** Names match the way a bank checks them: upper/lower case and extra spaces don't matter. */
    public static function sameName(?string $typed, ?string $holder): bool
    {
        $clean = fn ($name) => preg_replace('/\s+/u', ' ', mb_strtolower(trim((string) $name)));

        return $clean($typed) !== '' && $clean($typed) === $clean($holder);
    }

    /**
     * The account an account number or IBAN belongs to: [BankAccount, key], or null.
     * An IBAN is the current account's (spaces and case don't matter).
     */
    public function findAccount(?string $number): ?array
    {
        $number = trim((string) $number);
        if ($number === '') {
            return null;
        }
        $iban = strtoupper(preg_replace('/\s+/', '', $number));

        $account = BankAccount::where('primary_savings_account_number', $number)
            ->orWhere('emergency_fund_account_number', $number)
            ->orWhere('money_market_account_number', $number)
            ->orWhereRaw("REPLACE(UPPER(card_iban), ' ', '') = ?", [$iban])
            ->first();
        if (!$account) {
            return null;
        }

        foreach ([self::EMERGENCY, self::MONEY_MARKET] as $key) {
            if ((string) self::numberOf($account, $key) === $number) {
                return [$account, $key];
            }
        }

        return [$account, self::CURRENT];
    }

    /** This month's spending: debits except the NOT_SPENDING ones. */
    public static function spentThisMonth(int $userId, ?Carbon $month = null): float
    {
        $month ??= now();

        return (float) Transaction1::where('user_id', $userId)
            ->where('type', 'debit')
            ->whereNotIn('category', self::NOT_SPENDING)
            ->where('is_penalty', 0)
            ->whereYear('transaction_date', $month->year)
            ->whereMonth('transaction_date', $month->month)
            ->sum('amount');
    }

    /**
     * Why money can't go into the Money Market account yet (a flash.* key), or null:
     * from the 26th, with the month's tasks done - the plan of the month and every
     * Required activity - and 91% of the salary spent (Daniel, 2026-11-04: "solo
     * puede pasar el dinero a Money Market si ha finalizado las tareas y está en
     * un gasto superior al 91% de su sueldo en ese mes"). Money there can't be
     * taken by the month-end penalty, so nobody hides it there first. From the
     * Intermediate on, saving as much as possible is the point: the tasks are
     * enough, without the 91% (Daniel, 2026-11-08).
     */
    public function moneyMarketLock(User $user): ?string
    {
        if ((int) now()->format('j') < 26) {
            return 'mm_locked_day';
        }
        if (WantsPlanService::needsChoice($user) || app(ActivityFineService::class)->pending($user)->isNotEmpty()) {
            return 'mm_locked_tasks';
        }
        if (\App\Support\Level::saver($user)) {
            return null;
        }

        return self::spentThisMonth($user->id) < MonthEndPenaltyService::salary() * 0.91 ? 'mm_locked_spend' : null;
    }

    /**
     * Money goes to classmates only (Daniel, 2026-10-29: "transfers only same
     * class"): a student without a class can't send to anyone else.
     */
    public static function sameClass(User $sender, int $recipientUserId): bool
    {
        $recipientClass = User::whereKey($recipientUserId)->value('grade');

        return $sender->grade && $recipientClass && (int) $recipientClass === (int) $sender->grade;
    }

    /** Moves the money of a transfer or a bill payment now. */
    public function execute(Transfer $transfer): array
    {
        return DB::transaction(function () use ($transfer) {
            $sender = User::find($transfer->user_id);
            $senderId = BankAccount::where('student_id', $transfer->user_id)->value('id');
            if (!$sender || !$senderId) {
                return $this->fail('account_not_found');
            }
            $amount = round((float) $transfer->amount, 2);
            if ($amount <= 0) {
                return $this->fail('transfer_failed');
            }

            if ($transfer->kind === 'bill') {
                $senderBank = BankAccount::whereKey($senderId)->lockForUpdate()->first();

                return $this->payBill($transfer, $sender, $senderBank, $amount);
            }
            if ($transfer->kind === DonationService::KIND) {
                $senderBank = BankAccount::whereKey($senderId)->lockForUpdate()->first();

                return app(DonationService::class)->pay($transfer, $sender, $senderBank, $amount);
            }

            $found = $this->findAccount($transfer->account_number);
            if (!$found || !self::sameName($transfer->beneficiary_name, $found[0]->student_name)) {
                return $this->fail('no_beneficiary');
            }
            [$recipientRow, $to] = $found;

            // both rows locked, lowest id first, one instance if it's the same row
            $locked = BankAccount::whereIn('id', array_unique([$senderId, $recipientRow->id]))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $senderBank = $locked[$senderId];
            $recipientBank = $locked[$recipientRow->id];
            $own = $senderBank->id === $recipientBank->id;
            $from = self::key($transfer->from_account) ?? self::CURRENT;

            if ($own && $from === $to) {
                return $this->fail('same_account');
            }
            if (!$own && !self::sameClass($sender, (int) $recipientBank->student_id)) {
                return $this->fail('other_class');
            }
            if ($from !== self::CURRENT && !SavingsLedger::isOpen($senderBank, $from)) {
                return $this->fail($from === self::EMERGENCY ? 'ef_not_active' : 'mm_not_active');
            }
            if ($to !== self::CURRENT && !SavingsLedger::isOpen($recipientBank, $to)) {
                return $this->fail($own ? ($to === self::EMERGENCY ? 'ef_not_active' : 'mm_not_active') : 'recipient_account_closed');
            }
            if ($own && $to === self::MONEY_MARKET && ($lock = $this->moneyMarketLock($sender))) {
                return $this->fail($lock);
            }

            $available = $from === self::CURRENT ? LedgerService::lockedBalance($sender->id) : SavingsLedger::balance($senderBank, $from);
            if ($available < $amount) {
                return $this->fail('insufficient_balance');
            }
            // the Money Market's contract: taking money out costs 2 % of it, at most the interest earned so
            // far; it comes off what is sent (and that month earns no interest: savings:credit-interest)
            $fee = $from === self::MONEY_MARKET ? SavingsLedger::withdrawalFee($senderBank, $amount) : 0.0;
            $sent = round($amount - $fee, 2);

            $category = $own ? 'Own Transfer' : 'Fund Transfer';
            $out = $own ? self::OWN_TO[$to] : "Transfer to {$transfer->beneficiary_name} ({$transfer->account_number})";
            $in = $own ? self::OWN_FROM[$from] : "Received from {$sender->display_name} (" . self::numberOf($senderBank, $from) . ')';
            $meta = $transfer->memo ? ['memo' => $transfer->memo] : null;

            if ($from === self::CURRENT) {
                LedgerService::post($sender->id, 'debit', $category, $amount, $out,
                    ['sid' => $senderBank->sid, 'bank_account_id' => $senderBank->id, 'meta' => $meta]);
            } else {
                SavingsLedger::post($senderBank, $from, 'debit', $category, $sent, $out, $meta);
                if ($fee > 0) {
                    SavingsLedger::post($senderBank, $from, 'debit', 'Fee', $fee, self::WITHDRAWAL_FEE_LINE);
                }
            }
            if ($to === self::CURRENT) {
                LedgerService::post((int) $recipientBank->student_id, 'credit', $category, $sent, $in,
                    ['sid' => $recipientBank->sid, 'bank_account_id' => $recipientBank->id, 'meta' => $meta]);
            } else {
                SavingsLedger::post($recipientBank, $to, 'credit', $category, $sent, $in, $meta);
            }

            app(ParticipationService::class)->award($sender->id, 'transfer');
            // only now, once the money has moved (it was sent when the row was created)
            app(NotificationService::class)->log($sender->id, 'Bank Account', 'transfer_completed', 'Transfer Completed',
                'Your transfer of Ƶ' . number_format($amount, 2) . " to {$transfer->beneficiary_name} has been completed.",
                'arrow-left-right', 'green', $transfer->exists ? $transfer : null);
            if (!$own) {
                app(EasyActivityService::class)->transferMade($sender, (int) $recipientBank->student_id, $amount, $transfer->memo);
            }

            return ['ok' => true, 'fee' => $fee];
        });
    }

    private function payBill(Transfer $transfer, User $sender, BankAccount $senderBank, float $amount): array
    {
        if (LedgerService::lockedBalance($sender->id) < $amount) {
            return $this->fail('insufficient_balance');
        }
        LedgerService::post($sender->id, 'debit', 'Bill Payment', $amount,
            "Bill payment to {$transfer->beneficiary_name} ({$transfer->account_number})",
            ['sid' => $senderBank->sid, 'bank_account_id' => $senderBank->id]);

        return ['ok' => true];
    }

    /**
     * A later or recurring transfer / bill: kept as scheduled for its first date,
     * or run straight away when that date is today or past.
     */
    public function schedule(Transfer $transfer, Carbon $first): array
    {
        $transfer->update(['status' => 'scheduled', 'next_run_at' => $first]);

        return $first->lte(now()) ? ($this->runScheduled($transfer->id) ?? ['ok' => true]) : ['ok' => true];
    }

    /**
     * One due run of a scheduled transfer or bill, or null if it isn't due (any
     * more): a "later" one is then done or failed; a recurring one moves to its
     * next date - a failed date (not enough money) is skipped - and is done
     * after its end date.
     */
    public function runScheduled(int $id, ?Carbon $now = null): ?array
    {
        $now ??= now();

        return DB::transaction(function () use ($id, $now) {
            $transfer = Transfer::whereKey($id)->lockForUpdate()->first();
            if (!$transfer || $transfer->status !== 'scheduled' || !$transfer->next_run_at || $transfer->next_run_at->gt($now)) {
                return null;
            }

            $result = $this->execute($transfer);
            $transfer->runs++;
            $transfer->last_run_at = $now;
            $transfer->failure = $result['ok'] ? null : $result['error'];

            if ($transfer->type === 'recurring' && $transfer->frequency) {
                $next = self::advance($transfer->next_run_at, $transfer->frequency);
                $last = $transfer->end_date ? $transfer->end_date->copy()->endOfDay() : null;
                if (!$next || ($last && $next->gt($last))) {
                    $transfer->status = 'done';
                    $transfer->next_run_at = null;
                } else {
                    $transfer->next_run_at = $next;
                }
            } else {
                $transfer->status = $result['ok'] ? 'done' : 'failed';
                $transfer->next_run_at = null;
            }
            $transfer->save();

            return $result;
        });
    }

    public static function advance(Carbon $date, string $frequency): ?Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonthNoOverflow(),
            'yearly' => $date->copy()->addYearNoOverflow(),
            default => null,
        };
    }

    private function fail(string $error): array
    {
        return ['ok' => false, 'error' => $error];
    }
}
