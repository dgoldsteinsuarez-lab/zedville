<?php

namespace App\Support;

/**
 * An amount of zeds written one way across the bank, the way the student's
 * language writes money (bank review, 2026-10-29: the bank showed "Ƶ 2,269.91",
 * "2,269.91 ZEDS" and "Ƶ 0" side by side): en "Ƶ 1,234.56", es/de
 * "1.234,56 Ƶ", fr "1 234,56 Ƶ".
 */
class Money
{
    private const STYLES = [
        // decimal mark, thousands mark, symbol before the number
        'en' => ['.', ',', true],
        'es' => [',', '.', false],
        'de' => [',', '.', false],
        'fr' => [',', "\u{202F}", false],
    ];

    /** The same rules for a page's script: {dec, thou, before}. */
    public static function style(): array
    {
        [$decimal, $thousands, $before] = self::STYLES[app()->getLocale()] ?? self::STYLES['en'];

        return ['dec' => $decimal, 'thou' => $thousands, 'before' => $before];
    }

    /** Just the number, written the same way - for texts that say "ZEDS" themselves. */
    public static function number(float|int|string|null $amount, int $decimals = 2): string
    {
        [$decimal, $thousands] = self::STYLES[app()->getLocale()] ?? self::STYLES['en'];

        return number_format(round((float) $amount, $decimals), $decimals, $decimal, $thousands);
    }

    public static function format(float|int|string|null $amount, bool $sign = false): string
    {
        [$decimal, $thousands, $before] = self::STYLES[app()->getLocale()] ?? self::STYLES['en'];
        $value = round((float) $amount, 2);
        $number = number_format(abs($value), 2, $decimal, $thousands);
        $prefix = $value < 0 ? '−' : ($sign && $value > 0 ? '+' : '');

        return $before ? $prefix . 'Ƶ ' . $number : $prefix . $number . "\u{00A0}Ƶ";
    }
}
