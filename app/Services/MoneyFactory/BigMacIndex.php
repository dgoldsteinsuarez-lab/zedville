<?php

namespace App\Services\MoneyFactory;

use App\Services\CurrencyExchangeService;
use App\Support\Money;

/**
 * The Big Mac index: is the Zed cheap or dear? (advanced, 2026-11-02). The
 * Economist compares the price of the same burger around the world; the prices
 * are its July 2026 data (github.com/TheEconomist/big-mac-data), changed into
 * Zeds with the board's rates. In Zedville the same burger costs 6.00 Ƶ:
 * cheaper than in the United States (the Zed is undervalued against the
 * dollar), dearer than in Japan (overvalued against the yen).
 */
class BigMacIndex
{
    /** The Economist's Big Mac prices, July 2026, in each currency. */
    public const DATE = '2026-07';
    public const PRICES = ['USD' => 6.22, 'EUR' => 6.19, 'GBP' => 5.49, 'CHF' => 7.30, 'JPY' => 500, 'CNY' => 26.50, 'CAD' => 8.17, 'AUD' => 8.50];

    /** The same burger in Zedville. */
    public const ZEDVILLE = 6.00;

    /** The prices a student works out themselves (one of them). */
    private const ASKED = ['EUR', 'GBP', 'CHF', 'CNY', 'CAD', 'AUD'];

    public static function inZeds(string $code): float
    {
        return round(self::PRICES[$code] / CurrencyExchangeService::base($code), 2);
    }

    private static function asked(int $studentId): string
    {
        return self::ASKED[Activities::random('mf_bigmac', $studentId)->between(0, count(self::ASKED) - 1)];
    }

    /** The table: Zedville first, then each country - the asked one without its Zeds. */
    public static function stage(int $studentId): array
    {
        $asked = self::asked($studentId);

        return [
            'zedville' => zeds(self::ZEDVILLE),
            'rows' => array_map(fn ($code) => [
                'country' => __('money_tasks.bigmac.countries.' . $code),
                'price' => CurrencyExchangeService::amount(self::PRICES[$code], $code),
                'rate' => Money::number(CurrencyExchangeService::base($code), $code === 'JPY' ? 2 : 4) . "\u{00A0}" . $code,
                'zeds' => $code === $asked ? null : zeds(self::inZeds($code)),
            ], array_keys(self::PRICES)),
        ];
    }

    public static function questions(int $studentId): array
    {
        $asked = self::asked($studentId);
        $rand = Activities::random('mf_bigmac:questions', $studentId);
        $right = self::inZeds($asked);
        $country = fn ($code) => __('money_tasks.bigmac.countries.' . $code);
        $others = array_slice($rand->shuffle(['USD', 'JPY', 'CNY', 'GBP', 'EUR', 'CAD', 'AUD']), 0, 3);

        return [
            Activities::choice($rand, __('money_tasks.bigmac.q_zeds', ['in' => __('money_tasks.bigmac.countries_in.' . $asked)]), [
                zeds($right),
                zeds(round(self::PRICES[$asked] * CurrencyExchangeService::base($asked), 2)), // multiplied instead of divided
                zeds(self::PRICES[$asked]),                                                    // as if 1 Ƶ were 1 unit
                zeds(round($right * 1.15, 2)),
            ], ['why' => __('money_tasks.bigmac.why_zeds', [
                'price' => CurrencyExchangeService::amount(self::PRICES[$asked], $asked),
                'rate' => Money::number(CurrencyExchangeService::base($asked), 4),
                'zeds' => zeds($right),
            ])]),
            Activities::choice($rand, __('money_tasks.bigmac.q_dearest'), array_map($country, array_merge(['CHF'], $others))),
            Activities::choice($rand, __('money_tasks.bigmac.q_dollar', ['zedville' => zeds(self::ZEDVILLE), 'usa' => zeds(self::inZeds('USD'))]), [
                __('money_tasks.bigmac.a_under'), __('money_tasks.bigmac.a_over'), __('money_tasks.bigmac.a_right'),
            ], ['why' => __('money_tasks.bigmac.why_dollar')]),
            Activities::choice($rand, __('money_tasks.bigmac.q_yen', ['japan' => zeds(self::inZeds('JPY'))]), [
                __('money_tasks.bigmac.a_over'), __('money_tasks.bigmac.a_under'), __('money_tasks.bigmac.a_right'),
            ], ['why' => __('money_tasks.bigmac.why_yen')]),
            Activities::choice($rand, __('money_tasks.bigmac.q_compares'), [
                __('money_tasks.bigmac.a_compares_right'), __('money_tasks.bigmac.a_compares_eaten'), __('money_tasks.bigmac.a_compares_size'),
            ]),
        ];
    }
}
