<?php

namespace App\Support;

/**
 * Translations for content that lives in the database in English - product
 * names, supermarket items, the daily questions (Daniel, 2026-10-25: "el
 * supermercado está en inglés... las preguntas del día también").
 *
 * The database keeps the English text (admin screens, orders and baskets
 * stay as they are); only what a student sees is translated, looked up by
 * the English text in resources/content-translations/<locale>/<group>.php.
 * Anything without a translation yet - e.g. a product an admin adds later -
 * shows in English, and an edited English text falls back to English until
 * its translation is updated too, so a stale translation never shows.
 */
class ContentTranslations
{
    private static array $cache = [];

    public static function get(string $group, ?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $locale = app()->getLocale();
        if ($locale === 'en') {
            return $text;
        }

        $map = self::$cache[$locale][$group] ??= self::load($locale, $group);
        $translated = $map[trim($text)] ?? self::localNumbers($text, $locale);

        // a hybrid class: products and names in the language to learn, longer
        // texts with their English (App\Support\Hybrid)
        return Hybrid::on() ? Hybrid::mix($translated, $text) : $translated;
    }

    /**
     * An answer that is only an amount or a percentage ("Ƶ 1,800.00", "15%")
     * has no words to translate, but its number is written the local way:
     * es/de "Ƶ 1.800,00", fr "Ƶ 1 800,00", and "15 %" - the same style as the
     * translated explanations next to it.
     */
    private static function localNumbers(string $text, string $locale): string
    {
        if (preg_match('/[A-Za-z]/', $text) || !preg_match('/\d/', $text)) {
            return $text;
        }

        [$thousands, $decimal] = $locale === 'fr' ? [' ', ','] : ['.', ','];

        $text = preg_replace_callback('/\d{1,3}(?:,\d{3})+(?:\.\d+)?|\d+\.\d+/', function ($m) use ($thousands, $decimal) {
            [$int, $dec] = array_pad(explode('.', str_replace(',', '', $m[0])), 2, null);
            $int = strlen($int) > 3 ? number_format((int) $int, 0, '', $thousands) : $int;

            return $dec === null ? $int : $int . $decimal . $dec;
        }, $text);

        return preg_replace('/(\d)%/', '$1 %', $text);
    }

    /**
     * A picture of an Academy item (public/uploads/curriculum) in the
     * student's language: "pisa_pay_slip.svg" has "pisa_pay_slip.es.svg"
     * next to it with the words drawn in Spanish (Daniel, 2026-10-25: "no
     * los puedes cambiar a los idiomas?"). A picture without a translated
     * copy - e.g. one an admin uploads later - is shown as it is.
     */
    public static function image(string $file): string
    {
        $locale = app()->getLocale();
        if ($locale === 'en') {
            return $file;
        }

        $translated = preg_replace('/(\.[A-Za-z0-9]+)$/', '.' . $locale . '$1', $file, 1);

        return $translated !== $file && is_file(public_path('uploads/curriculum/' . $translated)) ? $translated : $file;
    }

    private static function load(string $locale, string $group): array
    {
        $file = resource_path("content-translations/{$locale}/{$group}.php");

        return is_file($file) ? require $file : [];
    }
}
