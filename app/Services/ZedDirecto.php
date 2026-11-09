<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\Hybrid;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Zed Directo, the Intermediate's second bank (proposal «Un segundo banco»;
 * Daniel, 2026-11-08: "te confirmo", then "me gustaría que fuera 5 % para que
 * vean mejor el efecto que tiene el interés compuesto"). The Intermediate's
 * Money Market pays 5 % a year (the Rookie's stays at 4 %). In the level's third
 * month the Zedville central bank raises interest rates above inflation to
 * stop the price rise (PriceRise): the Money Market goes to 6 %. That month Zed
 * Directo opens and pays more: a savings account at 7 % a year, paid every
 * month, and a 6-month deposit linked to inflation, «IPC + 3 %» (8 % with
 * prices 5 % up), paid at its end - taken out early, it loses the interest.
 * With prices 5 % up the real interest is +1 %, +2 % and +3 %: comparing banks
 * pays, and only interest above inflation protects savings. The salary doesn't
 * rise that year (Daniel: "so that they understand the meaning and have less
 * buying power").
 *
 * Money comes in from the student's current account and goes back to it, and
 * from the savings account into a deposit. Like the Money Market, it only comes
 * in from the 26th with the month's tasks done (BankTransferService::
 * moneyMarketLock), so nobody hides money from the City Hall's fine. The
 * accounts are kept with the bank account (SavingsLedger 'zd_savings',
 * 'zd_deposit') but shown on Zed Directo's own page, not in the Banco de
 * Zedville's statements. The levels after the Intermediate keep it.
 */
class ZedDirecto
{
    /** The level's month it opens in, and the Money Market rises. */
    public const MONTH = 3;

    public const SAVINGS = 'zd_savings';
    public const DEPOSIT = 'zd_deposit';

    /** The Money Market of the Intermediate and the levels after it, and from the third month. */
    public const MONEY_MARKET = 0.05;
    public const MONEY_MARKET_RAISED = 0.06;

    /** The deposit pays inflation (PriceRise::INDEX) plus this. */
    public const DEPOSIT_OVER_INFLATION = 0.03;

    public const DEPOSIT_MONTHS = 6;
    public const MIN_DEPOSIT = 100;

    /** The current account's lines (lang/<locale>/transactions.php 'lines'). */
    public const TO_ZD = 'Transfer to Zed Directo';
    public const FROM_ZD = 'Transfer from Zed Directo';
    /** Zed Directo's own lines. */
    public const DEPOSIT_IN = 'Fixed-term deposit';
    public const DEPOSIT_BACK = 'Fixed-term deposit ended';
    public const DEPOSIT_CANCELLED = 'Fixed-term deposit cancelled';

    private static array $open = [];

    /** Whether it is open for the student: in the Intermediate from its third month, after it always. */
    public static function open(?User $user, ?Carbon $at = null): bool
    {
        if (!$user || !Level::saver($user)) {
            return false;
        }
        $at ??= now();

        return self::$open[$user->id . '|' . $at->format('Y-m')] ??= Level::order($user) > Level::INTERMEDIATE || self::since($user, $at) !== null;
    }

    /** The first day of the third month of the student's Intermediate class, once it has come. */
    public static function since(?User $user, ?Carbon $now = null): ?Carbon
    {
        $now ??= now();
        $from = self::opensOn($user, $now);

        return $from && $now->gte($from) ? $from : null;
    }

    /** The day it opens for the student's Intermediate class, come or not. */
    public static function opensOn(?User $user, ?Carbon $now = null): ?Carbon
    {
        $class = $user && $user->grade ? SchoolClass::find($user->grade) : null;
        if (!$class || Level::order($user) !== Level::INTERMEDIATE) {
            return null;
        }

        return IntermediateClassService::start($class, $now)->addMonthsNoOverflow(self::MONTH - 1);
    }

    /** For tests and long-running commands: forget what was looked up. */
    public static function flush(): void
    {
        self::$open = [];
    }

    /** The student's Money Market rate in a month: 4 % before the Intermediate, 5 % in it, 6 % from its third month. */
    public static function moneyMarketRate(?User $user, ?Carbon $at = null): float
    {
        if (!$user || !Level::saver($user)) {
            return (float) config('zedville.interest_rates.money_market', SavingsLedger::RATES['money_market']);
        }

        return self::open($user, $at) ? self::MONEY_MARKET_RAISED : self::MONEY_MARKET;
    }

    /** The deposit's yearly rate: «IPC + 3 %», what prices rose this year plus 3 %. */
    public static function depositRate(): float
    {
        return round(PriceRise::INDEX / 100 + self::DEPOSIT_OVER_INFLATION, 4);
    }

    /** What a deposit pays at its end: its yearly rate for its 6 months. */
    public static function depositInterest(float $amount): float
    {
        return round($amount * self::depositRate() * self::DEPOSIT_MONTHS / 12, 2);
    }

    private static function number(): string
    {
        return 'ZD' . str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
    }

    /** Opens the savings account: 'opened', 'open' (it already was) or 'closed' (Zed Directo isn't there yet). */
    public function openSavings(User $user): string
    {
        if (!self::open($user)) {
            return 'closed';
        }

        return DB::transaction(function () use ($user) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            if (!$account) {
                return 'closed';
            }
            if (SavingsLedger::isOpen($account, self::SAVINGS)) {
                return 'open';
            }
            $account->is_open_zd_savings = 1;
            $account->zd_savings_amount = 0;
            $account->zd_savings_number = $account->zd_savings_number ?: self::number();
            $account->save();

            return 'opened';
        });
    }

    /**
     * Money between the current account and the savings account: 'in' brings it
     * from the current account (from the 26th, the month's tasks done), 'out'
     * takes it back. Null when it moved, else why not (a flash.* key).
     */
    public function move(User $user, string $way, float $amount): ?string
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return 'zd_amount';
        }
        if ($way === 'in' && ($lock = app(BankTransferService::class)->moneyMarketLock($user))) {
            return $lock === 'mm_locked_day' ? 'zd_locked_day' : 'zd_locked_tasks';
        }

        return DB::transaction(function () use ($user, $way, $amount) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            if (!$account || !SavingsLedger::isOpen($account, self::SAVINGS)) {
                return 'zd_not_open';
            }
            $options = ['sid' => $account->sid, 'bank_account_id' => $account->id];
            if ($way === 'in') {
                if (LedgerService::lockedBalance($user->id) < $amount) {
                    return 'insufficient_balance';
                }
                LedgerService::post($user->id, 'debit', 'Own Transfer', $amount, self::TO_ZD, $options);
                SavingsLedger::post($account, self::SAVINGS, 'credit', 'Own Transfer', $amount, BankTransferService::OWN_FROM[BankTransferService::CURRENT]);
            } else {
                if (SavingsLedger::balance($account, self::SAVINGS) < $amount) {
                    return 'insufficient_balance';
                }
                SavingsLedger::post($account, self::SAVINGS, 'debit', 'Own Transfer', $amount, BankTransferService::OWN_TO[BankTransferService::CURRENT]);
                LedgerService::post($user->id, 'credit', 'Own Transfer', $amount, self::FROM_ZD, $options);
            }

            return null;
        });
    }

    /** A deposit from the savings account, 6 months at 7%. Null when made, else why not (a flash.* key). */
    public function openDeposit(User $user, float $amount): ?string
    {
        $amount = round($amount, 2);
        if ($amount < self::MIN_DEPOSIT) {
            return 'zd_deposit_min';
        }

        return DB::transaction(function () use ($user, $amount) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            if (!$account || !SavingsLedger::isOpen($account, self::SAVINGS)) {
                return 'zd_not_open';
            }
            if (SavingsLedger::isOpen($account, self::DEPOSIT)) {
                return 'zd_deposit_exists';
            }
            if (SavingsLedger::balance($account, self::SAVINGS) < $amount) {
                return 'insufficient_balance';
            }
            $account->is_open_zd_deposit = 1;
            $account->zd_deposit_number = $account->zd_deposit_number ?: self::number();
            $account->zd_deposit_started_on = today()->toDateString();
            $account->zd_deposit_matures_on = today()->addMonthsNoOverflow(self::DEPOSIT_MONTHS)->toDateString();
            SavingsLedger::post($account, self::SAVINGS, 'debit', 'Own Transfer', $amount, self::DEPOSIT_IN);
            SavingsLedger::post($account, self::DEPOSIT, 'credit', 'Own Transfer', $amount, self::DEPOSIT_IN);

            return null;
        });
    }

    /** The deposit taken out before its end: its money back to the savings account, without interest. */
    public function cancelDeposit(User $user): ?string
    {
        return DB::transaction(function () use ($user) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            if (!$account || !SavingsLedger::isOpen($account, self::DEPOSIT)) {
                return 'zd_no_deposit';
            }
            self::close($account, false);

            return null;
        });
    }

    /** Ends the deposit: its money - at its end with the interest - back to the savings account. */
    private static function close(BankAccount $account, bool $ended): void
    {
        $amount = SavingsLedger::balance($account, self::DEPOSIT);
        if ($ended) {
            $interest = self::depositInterest($amount);
            SavingsLedger::post($account, self::DEPOSIT, 'credit', 'Interest', $interest, 'Interest', ['deposit' => $account->zd_deposit_started_on]);
            $amount = round($amount + $interest, 2);
        }
        $line = $ended ? self::DEPOSIT_BACK : self::DEPOSIT_CANCELLED;
        SavingsLedger::post($account, self::DEPOSIT, 'debit', 'Own Transfer', $amount, $line);
        $account->is_open_zd_deposit = 0;
        $account->zd_deposit_started_on = null;
        $account->zd_deposit_matures_on = null;
        SavingsLedger::post($account, self::SAVINGS, 'credit', 'Own Transfer', $amount, $line);
    }

    /** The deposits whose 6 months are over, paid with their interest (every night, and when the page opens). */
    public static function endDue(?int $userId = null, ?Carbon $today = null): int
    {
        $today = ($today ?? today())->toDateString();
        $ids = BankAccount::where('is_open_zd_deposit', 1)->whereDate('zd_deposit_matures_on', '<=', $today)
            ->when($userId, fn ($q) => $q->where('student_id', $userId))->pluck('id');
        $ended = 0;
        foreach ($ids as $id) {
            DB::transaction(function () use ($id, $today, &$ended) {
                $account = BankAccount::whereKey($id)->lockForUpdate()->first();
                if ($account && SavingsLedger::isOpen($account, self::DEPOSIT) && (string) $account->zd_deposit_matures_on <= $today) {
                    self::close($account, true);
                    $ended++;
                }
            });
        }

        return $ended;
    }

    /**
     * The two letters, once, the first time the student comes in its month: the
     * Banco de Zedville's (the Money Market now pays 5%, why) and Zed Directo's
     * opening (its rates, compare before choosing).
     */
    public static function announce(User $user): void
    {
        $since = self::since($user);
        if (!$since) {
            return;
        }
        $from = IntermediateClassService::start(SchoolClass::find($user->grade));
        $data = ['month' => $since->format('Y-m')];
        foreach (['rates-up' => 'Your Money Market Account pays more', 'zed-directo-open' => 'Zed Directo opens in Zedville'] as $template => $subject) {
            if (Mailbox::where('student_id', $user->id)->where('template', $template)->where('created_at', '>=', $from)->exists()) {
                continue;
            }
            Mailbox::create([
                'student_id' => $user->id,
                'sid' => $user->sid,
                'subject' => $subject,
                'content' => Hybrid::without(fn () => view('mailbox_templates.' . $template, $data + ['user' => $user])->render()),
                'type' => 'primary',
                'template' => $template,
                'template_data' => $data,
                'read' => 0,
            ]);
        }
    }
}
