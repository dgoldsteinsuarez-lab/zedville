<?php

namespace App\Services\MoneyFactory;

use App\Services\CurrencyExchangeService;

/**
 * How much is it in other currencies? (Junior, 2026-11-02 - "¿Cuánto cuesta un
 * helado en dólares o en yenes?" using the board). A Zedville price, and the
 * board's rates for one currency and the Japanese yen: about how much it is in
 * each (the wrong options are ten times too big or too small, so an estimate
 * is enough), which number is biggest and why the numbers differ.
 */
class PricesAbroad
{
    /** Things to buy in Zedville: their picture and price in Zeds. */
    public const ITEMS = ['ice_cream' => ['🍦', 3], 'comic' => ['📚', 6], 'cinema' => ['🎬', 9], 'football' => ['⚽', 25]];

    /** The first currency (the second is always the yen). */
    public const CURRENCIES = ['USD', 'GBP', 'EUR', 'CHF'];

    /** The student's item and first currency. */
    private static function pick(int $studentId): array
    {
        $rand = Activities::random('mf_prices', $studentId);
        $item = array_keys(self::ITEMS)[$rand->between(0, count(self::ITEMS) - 1)];

        return [$item, self::CURRENCIES[$rand->between(0, count(self::CURRENCIES) - 1)]];
    }

    /** The price in a currency at the board's base rate. */
    private static function price(int $zeds, string $code, float $factor = 1): float
    {
        return round($zeds * CurrencyExchangeService::base($code) * $factor, CurrencyExchangeService::decimals($code));
    }

    /** "0.9496 USD": what 1 Ƶ is worth, as the board shows it. */
    private static function rate(string $code): string
    {
        return \App\Support\Money::number(CurrencyExchangeService::base($code), $code === 'JPY' ? 2 : 4) . "\u{00A0}" . $code;
    }

    public static function stage(int $studentId): array
    {
        [$item, $code] = self::pick($studentId);

        return [
            'icon' => self::ITEMS[$item][0],
            'name' => __('money_tasks.prices.names.' . $item),
            'price' => zeds(self::ITEMS[$item][1]),
            'rates' => array_map(fn ($c) => ['code' => $c, 'name' => __('money.currencies.' . $c), 'rate' => self::rate($c)], [$code, 'JPY']),
        ];
    }

    public static function questions(int $studentId): array
    {
        [$item, $code] = self::pick($studentId);
        $zeds = self::ITEMS[$item][1];
        $rand = Activities::random('mf_prices:questions', $studentId);
        $ask = fn (string $c) => Activities::choice($rand,
            __('money_tasks.prices.q_in', ['item' => __('money_tasks.prices.items.' . $item), 'price' => zeds($zeds), 'in' => __('money_tasks.in.' . $c)]),
            array_map(fn ($factor) => CurrencyExchangeService::amount(self::price($zeds, $c, $factor), $c), [1, 10, 0.1]),
            ['why' => __('money_tasks.prices.why_in', [
                'rate' => __('money.one_zed') . ' ' . self::rate($c),
                'zeds' => zeds($zeds),
                'price' => CurrencyExchangeService::amount(self::price($zeds, $c), $c),
            ])]);

        return [
            $ask($code),
            $ask('JPY'),
            Activities::choice($rand, __('money_tasks.prices.q_biggest'), [
                ucfirst(__('money_tasks.in.JPY')), ucfirst(__('money_tasks.in.' . $code)), ucfirst(__('money_tasks.in.ZED')),
            ], ['why' => __('money_tasks.prices.why_biggest')]),
            Activities::choice($rand, __('money_tasks.prices.q_why'), [
                __('money_tasks.prices.a_why_right'), __('money_tasks.prices.a_why_bigger'), __('money_tasks.prices.a_why_dearer'),
            ]),
        ];
    }
}
