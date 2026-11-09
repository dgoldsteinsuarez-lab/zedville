<?php

namespace App\Services\MoneyFactory;

/**
 * Headlines that move the exchange rate (advanced, 2026-11-02 - "adivinar si el
 * zed sube o baja"). As the Bank of England explains, a currency gets stronger
 * when people want more of it: higher interest rates and a safe, sound economy
 * bring investors, tourists and buyers of Zedland's goods need Zeds. Printing
 * money to pay the bills makes it weaker (Germany 1923, Zimbabwe 2008). Six of
 * the eight headlines per student, in their own order.
 */
class Headlines
{
    /** Each headline and whether the Zed goes up (0) or down (1). */
    public const NEWS = [
        'rate_up' => 0, 'rate_down' => 1, 'tourists' => 0, 'exports' => 0,
        'safe' => 0, 'chaos' => 1, 'printing' => 1, 'imports' => 1,
    ];

    public static function picked(int $studentId): array
    {
        return array_slice(Activities::random('mf_headlines', $studentId)->shuffle(array_keys(self::NEWS)), 0, 6);
    }

    public static function stage(int $studentId): array
    {
        return [];
    }

    public static function questions(int $studentId): array
    {
        return array_map(fn ($key) => Activities::fixed(
            __('money_tasks.headlines.q'),
            [__('money_tasks.headlines.up'), __('money_tasks.headlines.down')],
            self::NEWS[$key],
            ['lead' => __('money_tasks.headlines.news.' . $key), 'why' => __('money_tasks.headlines.why.' . $key)],
        ), self::picked($studentId));
    }
}
