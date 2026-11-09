<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Mailbox;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Support\Hybrid;
use App\Support\Level;
use Carbon\Carbon;

/**
 * The Intermediate's saver bonus (proposal «Premio al ahorro»; Daniel,
 * 2026-11-08: "de acuerdo"): the City Hall rewards whoever saves, as Germany
 * adds to what workers save from their payslip (Arbeitnehmer-Sparzulage). With
 * the salary, two bonuses, once each:
 *  - welcome: with the first salary of the level, to a student who brings an
 *    Emergency Fund from the Rookie (scenario A), 5 % of it, at most 400 Ƶ;
 *  - the quarter's: with the salary of the student's months 4, 7 and 10, 5 %
 *    of what was saved in the three months before (into the fund, the Money
 *    Market and Zed Directo, less what came out: SaverService), at most 150 Ƶ.
 * It comes as a 'Bonus' line - not 'Income', which is the salary - with a letter.
 */
class SaverBonus
{
    public const SHARE = 0.05;
    public const WELCOME_MAX = 400;
    public const QUARTER_MAX = 150;
    public const QUARTER_MONTHS = [4, 7, 10];

    /** The current account's lines (lang/<locale>/transactions.php 'lines'). */
    public const WELCOME = 'Saver bonus: welcome to the Intermediate';
    public const QUARTER = 'Saver bonus: the quarter';

    /** 5 % of the Emergency Fund the student had before the class started the level, at most 400. */
    public static function welcomeAmount(User $user, SchoolClass $class): float
    {
        $balance = (float) SavingsTransaction::where('user_id', $user->id)->where('account', 'emergency')
            ->where('transaction_date', '<', IntermediateClassService::start($class))
            ->orderByDesc('transaction_date')->orderByDesc('id')->value('balance');

        return round(min(self::WELCOME_MAX, max(0, $balance) * self::SHARE), 2);
    }

    /** 5 % of what was saved in the three months before $month, at most 150. */
    public static function quarterAmount(User $user, Carbon $month): float
    {
        $saved = 0.0;
        for ($back = 1; $back <= 3; $back++) {
            $saved += SaverService::savedThisMonth($user->id, $month->copy()->startOfMonth()->subMonthsNoOverflow($back))['amount'];
        }

        return round(min(self::QUARTER_MAX, max(0, $saved) * self::SHARE), 2);
    }

    /** The bonuses due with this month's salary, paid once each, with a letter: [line => amount]. */
    public static function payDue(?User $user, ?Carbon $now = null): array
    {
        $class = $user && $user->grade ? SchoolClass::find($user->grade) : null;
        $accountId = $user ? BankAccount::where('student_id', $user->id)->value('id') : null;
        if (!$class || !$accountId || Level::order($user) !== Level::INTERMEDIATE) {
            return [];
        }
        $now ??= now();
        $month = IntermediateClassService::studentMonth($user, $now);

        $due = [];
        if ($month === 1 && IntermediateClassService::scenarioFor($user, $class, $now) === 'A') {
            $due[self::WELCOME] = [self::welcomeAmount($user, $class), IntermediateClassService::start($class, $now)];
        }
        if (in_array($month, self::QUARTER_MONTHS, true)) {
            $due[self::QUARTER] = [self::quarterAmount($user, $now), $now->copy()->startOfMonth()];
        }

        $paid = [];
        foreach ($due as $line => [$amount, $since]) {
            // the welcome once in the level, the quarter's once in its month
            if ($amount <= 0 || Transaction1::where('user_id', $user->id)->where('description', $line)->where('transaction_date', '>=', $since)->exists()) {
                continue;
            }
            LedgerService::post($user->id, 'credit', 'Bonus', $amount, $line, ['sid' => $user->sid, 'bank_account_id' => $accountId]);
            $paid[$line] = $amount;

            $data = ['kind' => $line === self::WELCOME ? 'welcome' : 'quarter', 'amount' => $amount, 'month' => $now->format('Y-m')];
            Mailbox::create([
                'student_id' => $user->id,
                'sid' => $user->sid,
                'subject' => 'Your saver bonus',
                'content' => Hybrid::without(fn () => view('mailbox_templates.saver-bonus', $data + ['user' => $user])->render()),
                'type' => 'primary',
                'template' => 'saver-bonus',
                'template_data' => $data,
                'read' => 0,
            ]);
        }

        return $paid;
    }
}
