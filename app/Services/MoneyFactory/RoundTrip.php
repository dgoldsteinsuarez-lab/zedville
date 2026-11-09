<?php

namespace App\Services\MoneyFactory;

use App\Services\CurrencyExchangeService;
use App\Support\Money;

/**
 * Back from the trip: buy and sell prices (advanced, 2026-11-02 - "volver a
 * cambiar los dólares que sobran a zeds y descubrir el precio de compra y de
 * venta"). An exchange office that, instead of a commission, sells a currency
 * 2 % above the board's rate and buys it back 2 % below: what the trip money
 * costs, what the leftover brings back, and that the difference is what the
 * office earns. Every student has their own currency and amounts.
 */
class RoundTrip
{
    public const CURRENCIES = ['USD', 'GBP', 'EUR', 'CHF'];

    /** The office's margin on each side of the board's rate. */
    public const MARGIN = 0.02;

    /** The student's currency, what they buy for the trip and what is left. */
    private static function pick(int $studentId): array
    {
        $rand = Activities::random('mf_roundtrip', $studentId);

        return [
            self::CURRENCIES[$rand->between(0, count(self::CURRENCIES) - 1)],
            [100, 150, 200][$rand->between(0, 2)],
            [20, 30, 40, 50][$rand->between(0, 3)],
        ];
    }

    /** Zeds for 1 unit of the currency: [the board, the office sells at, the office buys at] - as shown, 4 decimals. */
    public static function prices(string $code): array
    {
        $board = 1 / CurrencyExchangeService::base($code);

        return [round($board, 4), round($board * (1 + self::MARGIN), 4), round($board * (1 - self::MARGIN), 4)];
    }

    public static function stage(int $studentId): array
    {
        [$code, $out, $back] = self::pick($studentId);
        [, $sell, $buy] = self::prices($code);

        return [
            'code' => $code,
            'name' => __('money.currencies.' . $code),
            'sell' => Money::number($sell, 4),
            'buy' => Money::number($buy, 4),
            'story' => __('money_tasks.roundtrip.story', ['out' => CurrencyExchangeService::amount($out, $code), 'back' => CurrencyExchangeService::amount($back, $code)]),
        ];
    }

    public static function questions(int $studentId): array
    {
        [$code, $out, $back] = self::pick($studentId);
        [$board, $sell, $buy] = self::prices($code);
        $rand = Activities::random('mf_roundtrip:questions', $studentId);
        $zeds = fn (float $amount) => zeds(round($amount, 2));

        return [
            Activities::choice($rand, __('money_tasks.roundtrip.q_pay', ['amount' => CurrencyExchangeService::amount($out, $code)]),
                [$zeds($out * $sell), $zeds($out * $buy), $zeds($out * $board), $zeds($out)],
                ['why' => __('money_tasks.roundtrip.why_pay', ['amount' => $out, 'price' => Money::number($sell, 4)])]),
            Activities::choice($rand, __('money_tasks.roundtrip.q_get', ['amount' => CurrencyExchangeService::amount($back, $code)]),
                [$zeds($back * $buy), $zeds($back * $sell), $zeds($back * $board), $zeds($back)],
                ['why' => __('money_tasks.roundtrip.why_get', ['amount' => $back, 'price' => Money::number($buy, 4)])]),
            Activities::choice($rand, __('money_tasks.roundtrip.q_higher'), [
                __('money_tasks.roundtrip.a_higher_sell'), __('money_tasks.roundtrip.a_higher_buy'), __('money_tasks.roundtrip.a_higher_same'),
            ]),
            Activities::choice($rand, __('money_tasks.roundtrip.q_earns'), [
                __('money_tasks.roundtrip.a_earns_right'), __('money_tasks.roundtrip.a_earns_tax'), __('money_tasks.roundtrip.a_earns_free'),
            ], ['why' => __('money_tasks.roundtrip.why_earns')]),
            Activities::choice($rand, __('money_tasks.roundtrip.q_cheapest'), [
                __('money_tasks.roundtrip.a_cheapest_right'), __('money_tasks.roundtrip.a_cheapest_lot'), __('money_tasks.roundtrip.a_cheapest_daily'),
            ], ['why' => __('money_tasks.roundtrip.why_cheapest')]),
        ];
    }
}
