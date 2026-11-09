<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\CardMovement;
use App\Models\CardStatement;
use App\Models\CreditCard;
use App\Models\User;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The credit card, from the Intermediate (proposal «Tarjeta de crédito», step
 * 16; agreed 2026-11-08): "La pide en el banco y paga con ella en el City Mall
 * y en las actividades de gasto; el día 7 paga todo o aplaza". A 1,000 Ƶ
 * limit; each month's purchases make a statement, paid on the 7th with the
 * bills as the student chose: all of it, which costs nothing; the 3 %
 * minimum; or a fixed amount. What is deferred pays 20 % TAE, added to what
 * is owed - with the minimum, the debt hardly goes down (the BaFin example:
 * 1,500 € at 20 %, paid off in 12 months, costs about 170 €; with the 3 %
 * minimum it takes about 80 months). A statement not paid by the end of its
 * month is taken anyway, into overdraft (Overdraft), like a loan instalment.
 */
class CreditCardService
{
    public const LIMIT = 1000.0;
    public const TAE = 0.20;
    public const MIN_SHARE = 0.03;

    /** The minimum payment is at least this - or all of a smaller debt (common on real cards; with it 1,500 at the minimum takes 85 months, near BaFin's 80). */
    public const MIN_FLOOR = 25.0;

    /** With the bills (BankController::ensureMonthlyBills). */
    public const DAY = 7;

    public const MODES = ['full', 'minimum', 'fixed'];

    /** The current account's line and the card's own lines (lang/<locale>/card.php 'lines'). */
    public const LINE_PAYMENT = 'Credit card payment';
    public const LINE_INTEREST = 'Credit card interest';

    public static function offered(?User $user): bool
    {
        return Level::saver($user);
    }

    public static function card(int $userId): ?CreditCard
    {
        return CreditCard::where('user_id', $userId)->where('status', 'active')->first();
    }

    public static function monthlyRate(): float
    {
        return (1 + self::TAE) ** (1 / 12) - 1;
    }

    /** What is owed on the card now (or at the end of $at). */
    public static function owed(CreditCard $card, ?Carbon $at = null): float
    {
        return (float) (CardMovement::where('card_id', $card->id)->when($at, fn ($q) => $q->where('moved_at', '<=', $at))
            ->orderByDesc('moved_at')->orderByDesc('id')->value('balance') ?? 0);
    }

    public static function available(CreditCard $card): float
    {
        return round((float) $card->limit - self::owed($card), 2);
    }

    /** The minimum payment of a debt: 3 %, at least 25 Ƶ, or all of it when it is smaller. */
    public static function minimum(float $owed): float
    {
        return $owed <= 0 ? 0.0 : round(min($owed, max($owed * self::MIN_SHARE, self::MIN_FLOOR)), 2);
    }

    /** What the card's mode pays of a statement. */
    public static function dueFor(CreditCard $card, float $owed): float
    {
        return match ($card->mode) {
            'minimum' => self::minimum($owed),
            'fixed' => round(min($owed, max((float) $card->fixed_amount, self::minimum($owed))), 2),
            default => round($owed, 2),
        };
    }

    /**
     * Paying a debt month by month with no new purchases: ['months', 'interest'].
     * $payment: a fixed amount, or null for the minimum each month.
     */
    public static function payoff(float $owed, ?float $payment = null): array
    {
        $rate = self::monthlyRate();
        $months = 0;
        $interest = 0.0;
        while ($owed > 0.005 && $months < 600) {
            $owed -= min($owed, $payment ?? self::minimum($owed));
            $charge = round($owed * $rate, 2);
            $interest += $charge;
            $owed = round($owed + $charge, 2);
            $months++;
        }

        return ['months' => $months, 'interest' => round($interest, 2)];
    }

    /** The same payment every month that clears a debt in $months at the card's rate. */
    public static function equalPayment(float $owed, int $months): float
    {
        $rate = self::monthlyRate();

        return round($owed * $rate / (1 - (1 + $rate) ** -$months), 2);
    }

    /** Why the bank won't give the card (a flash.* key), or null. */
    public static function refusal(?User $user): ?string
    {
        return match (true) {
            !self::offered($user) => 'card_not_offered',
            self::card($user->id) !== null => 'card_has',
            LoanService::blocked($user->id) => 'loan_blocked',
            default => null,
        };
    }

    public static function open(User $user): CreditCard|string
    {
        if ($why = self::refusal($user)) {
            return $why;
        }

        return CreditCard::create([
            'user_id' => $user->id, 'sid' => $user->sid, 'limit' => self::LIMIT,
            'number' => '5' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT) . ' •••• •••• ' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
        ]);
    }

    /** How the statements are paid: all, the minimum or a fixed amount. Null, or why not. */
    public static function setMode(User $user, string $mode, ?float $fixed = null): ?string
    {
        $card = self::card($user->id);
        if (!$card || !in_array($mode, self::MODES, true)) {
            return 'card_none';
        }
        if ($mode === 'fixed' && (!$fixed || $fixed < self::MIN_FLOOR)) {
            return 'card_fixed';
        }
        $card->update(['mode' => $mode, 'fixed_amount' => $mode === 'fixed' ? round($fixed, 2) : null]);

        return null;
    }

    /** A purchase with the card, if it fits in what is left of the limit. Null, or why not. */
    public static function purchase(User $user, float $amount, string $description, ?int $orderId = null): ?string
    {
        $card = self::card($user->id);
        if (!$card) {
            return 'card_none';
        }
        if (round($amount, 2) > self::available($card) + 0.001) {
            return 'card_limit';
        }
        self::move($card, 'purchase', $amount, $description, $orderId);

        return null;
    }

    private static function move(CreditCard $card, string $type, float $amount, string $description, ?int $orderId = null, ?Carbon $at = null): CardMovement
    {
        $owed = self::owed($card);

        return CardMovement::create([
            'card_id' => $card->id, 'user_id' => $card->user_id, 'type' => $type, 'amount' => round($amount, 2),
            'balance' => round($owed + ($type === 'payment' ? -$amount : $amount), 2),
            'description' => $description, 'order_id' => $orderId, 'moved_at' => $at ?? now(),
        ]);
    }

    /**
     * The monthly statement and its payment: from the 1st, the month before's
     * statement (what was owed when it closed); from the 7th, its payment as
     * the card's mode says, from the current account when the money is there;
     * one not paid by the end of its month, taken anyway into overdraft. What
     * was deferred then pays a month's interest.
     */
    public static function settle(User $user, ?Carbon $now = null): void
    {
        $card = self::card($user->id);
        if (!$card) {
            return;
        }
        $now ??= now();
        $month = $now->copy()->startOfMonth()->subMonthNoOverflow();
        if ($card->created_at->lt($month->copy()->endOfMonth()) && !CardStatement::where('card_id', $card->id)->where('month', $month->format('Y-m'))->exists()) {
            $owed = self::owed($card, $month->copy()->endOfMonth());
            if ($owed > 0) {
                CardStatement::create(['card_id' => $card->id, 'user_id' => $user->id, 'month' => $month->format('Y-m'), 'balance' => $owed, 'due' => self::dueFor($card, $owed)]);
            }
        }

        foreach (CardStatement::where('card_id', $card->id)->where('status', 'pending')->orderBy('month')->get() as $statement) {
            $closed = Carbon::createFromFormat('!Y-m', $statement->month);
            $late = $now->gte($closed->copy()->addMonthsNoOverflow(2)->startOfMonth());   // its payment month is over
            if (!$late && $now->day < self::DAY) {
                continue;
            }
            $due = self::dueFor($card, (float) $statement->balance);
            DB::transaction(function () use ($user, $card, $statement, $due, $late) {
                $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
                if ($late) {
                    Overdraft::charge($user, $due, self::LINE_PAYMENT, ['card' => $card->id, 'statement' => $statement->month]);
                } elseif (!LedgerService::post($user->id, 'debit', 'Loan', $due, self::LINE_PAYMENT,
                    ['sid' => $user->sid, 'bank_account_id' => $account?->id, 'only_if_enough' => true, 'meta' => ['card' => $card->id, 'statement' => $statement->month]])) {
                    return;   // tried again every day until the money is there, or the month ends
                }
                self::move($card, 'payment', $due, self::LINE_PAYMENT);
                $interest = round(((float) $statement->balance - $due) * self::monthlyRate(), 2);
                if ($interest > 0) {
                    self::move($card, 'interest', $interest, self::LINE_INTEREST);
                }
                $statement->update(['due' => $due, 'paid' => $due, 'interest' => $interest, 'status' => $late ? 'late' : 'paid', 'paid_at' => now()]);
            });
        }
    }
}
