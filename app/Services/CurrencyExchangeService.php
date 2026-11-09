<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\CurrencyExchange;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The Money Factory's currency exchange (Daniel, 2026-11-02: "quiero que haya
 * un intercambio de divisas, con un panel que parece que está en vivo dando el
 * exchange rate de las divisas reales más importantes ... con una leve
 * comisión"). The Zed is worth 0.85 euro; the other currencies follow the
 * European Central Bank's real reference rates of one day, and move a little
 * all day - three slow waves, the same for everyone at the same second, so
 * the board and the exchange agree without storing anything. The Zeds leave
 * the current account in two lines, the exchange and its commission; the
 * foreign money is handed over in cash (there is no foreign account).
 */
class CurrencyExchangeService
{
    /** What 1 Ƶ is worth in euros. */
    public const ZED_IN_EUROS = 0.85;

    /** The ECB's euro reference rates the board starts from: units for 1 euro. */
    public const ECB_DATE = '2026-10-30';
    public const ECB_RATES = [
        'USD' => 1.1172, 'EUR' => 1.0, 'GBP' => 0.83380, 'JPY' => 175.68,
        'CHF' => 0.9194, 'CNY' => 7.4326, 'CAD' => 1.5606, 'AUD' => 1.5904,
    ];

    public const SYMBOLS = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'CHF' => 'Fr', 'CNY' => '¥', 'CAD' => '$', 'AUD' => '$'];

    /** The yen has no cents. */
    private const NO_CENTS = ['JPY'];

    /** 2 % of the Zeds exchanged, on top. */
    public const COMMISSION = 0.02;

    public const MIN = 5;
    public const MAX = 2000;

    /** How a rate moves: [share of the rate, period in seconds] - at most ±0.43 % together. */
    private const WAVES = [[0.0022, 83], [0.0013, 337], [0.0008, 1789]];

    /** Units of a currency for 1 Ƶ, before the day's moves. */
    public static function base(string $code): float
    {
        return self::ZED_IN_EUROS * self::ECB_RATES[$code];
    }

    /** One currency's waves, [share, period, phase] each - the page's script moves the board with them. */
    public static function waves(string $code): array
    {
        return array_map(fn ($wave, $i) => [$wave[0], $wave[1], (crc32($code . $i) % 6283) / 1000], self::WAVES, array_keys(self::WAVES));
    }

    /** Units of a currency for 1 Ƶ at a moment (to the second). */
    public static function rate(string $code, ?Carbon $at = null): float
    {
        $second = ($at ?? now())->getTimestamp();
        $move = 0.0;
        foreach (self::waves($code) as [$share, $period, $phase]) {
            $move += $share * sin(2 * M_PI * ($second % $period) / $period + $phase);
        }

        return self::base($code) * (1 + $move);
    }

    public static function decimals(string $code): int
    {
        return in_array($code, self::NO_CENTS, true) ? 0 : 2;
    }

    /** "94.96 USD" the way the student's language writes numbers. */
    public static function amount(float $amount, string $code): string
    {
        return \App\Support\Money::number($amount, self::decimals($code)) . "\u{00A0}" . $code;
    }

    /** The board: every currency, its rate now and at the start of the day (Zedville's time). */
    public static function board(?Carbon $at = null): array
    {
        $at ??= now();
        $open = $at->copy()->setTimezone('Europe/Berlin')->startOfDay();

        return array_map(fn ($code) => [
            'code' => $code,
            'name' => __('money.currencies.' . $code),
            'symbol' => self::SYMBOLS[$code],
            'cents' => self::decimals($code),
            'base' => self::base($code),
            'waves' => self::waves($code),
            'open' => self::rate($code, $open),
            'rate' => self::rate($code, $at),
        ], array_keys(self::ECB_RATES));
    }

    /** What exchanging $zeds gives and costs at the rate of that second. */
    public static function quote(string $code, float $zeds, ?Carbon $at = null): array
    {
        $rate = self::rate($code, $at);
        $commission = round($zeds * self::COMMISSION, 2);

        return [
            'rate' => $rate,
            'foreign' => round($zeds * $rate, self::decimals($code)),
            'commission' => $commission,
            'total' => round($zeds + $commission, 2),
        ];
    }

    /** The bank line's text, in English like every line ("Currency exchange: 94.96 USD"). */
    public static function lineText(float $foreign, string $code): string
    {
        return 'Currency exchange: ' . number_format($foreign, self::decimals($code)) . ' ' . $code;
    }

    /** How long the rate the student confirmed holds, in seconds - like a real exchange office's quote. */
    public const QUOTE_HOLDS = 120;

    /**
     * Exchanges Zeds from the student's current account at the rate of $at (the
     * second they saw it, if recent): the exchange and the commission, two lines.
     * Null when the money isn't there (no balance goes below zero).
     */
    public static function exchange(User $user, string $code, float $zeds, ?Carbon $at = null): ?CurrencyExchange
    {
        $account = BankAccount::where('student_id', $user->id)->first();
        if (!$account) {
            return null;
        }
        if (!$at || abs(now()->getTimestamp() - $at->getTimestamp()) > self::QUOTE_HOLDS) {
            $at = now();
        }

        $exchange = DB::transaction(function () use ($user, $account, $code, $zeds, $at) {
            BankAccount::whereKey($account->id)->lockForUpdate()->first();
            $quote = self::quote($code, $zeds, $at);
            if (round(LedgerService::lockedBalance($user->id), 2) < $quote['total']) {
                return null;
            }

            $extra = ['sid' => $user->sid, 'bank_account_id' => $account->id];
            $line = LedgerService::post($user->id, 'debit', 'Currency Exchange', $zeds, self::lineText($quote['foreign'], $code), $extra);
            LedgerService::post($user->id, 'debit', 'Currency Exchange', $quote['commission'], 'Exchange commission', $extra);

            return CurrencyExchange::create([
                'user_id' => $user->id, 'sid' => $user->sid, 'currency' => $code, 'zeds' => $zeds,
                'commission' => $quote['commission'], 'rate' => $quote['rate'], 'foreign_amount' => $quote['foreign'],
                'transaction_id' => $line->id,
            ]);
        });

        if ($exchange) {
            app(EasyActivityService::class)->exchangeMade($user, $code, $zeds);
        }

        return $exchange;
    }
}
