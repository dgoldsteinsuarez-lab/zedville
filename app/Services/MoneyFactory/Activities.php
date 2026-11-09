<?php

namespace App\Services\MoneyFactory;

/**
 * The Money Factory's activities with questions (Daniel, 2026-11-02: "Para las
 * ideas que pones, deberás ponerlas en Admin, bajo Money Factory, pero los
 * alumnos lo harán en Educational Finance Department", then "haz también lo
 * que falta"). Each one is built in - a migration makes it, an admin assigns it
 * from the level's Money Factory screen - and has its own page at the Money
 * Factory, opened from the Education Finance Department board. It's done when
 * every answer is right (App\Services\EasyActivityService::quizAnswered).
 *
 * Every student gets their own numbers, sets and order (a seeded generator
 * per student), so they can't copy each other's answers. A question:
 *   - kind: 'choice' (one option; answer = its index) or 'order' (put the
 *     options in order; answer = their indexes in the right order);
 *   - text, options, answer;
 *   - optionally lead (a headline or a situation shown big), facts (figures
 *     shown as chips) and why (said once it's answered right; not to hybrid
 *     classes: "no es necesario explicar, solo informar").
 */
class Activities
{
    /** Each activity: its lang key (money_tasks.<key>), page, class and icon, and whether it is for the advanced levels. */
    public const ALL = [
        'mf_fake' => ['key' => 'fake', 'slug' => 'real-or-fake', 'class' => FakeBanknote::class, 'icon' => '🔍', 'advanced' => false],
        'mf_history' => ['key' => 'history', 'slug' => 'story-of-money', 'class' => MoneyHistory::class, 'icon' => '📜', 'advanced' => false],
        'mf_prices' => ['key' => 'prices', 'slug' => 'prices-abroad', 'class' => PricesAbroad::class, 'icon' => '🍦', 'advanced' => false],
        'mf_roundtrip' => ['key' => 'roundtrip', 'slug' => 'back-from-the-trip', 'class' => RoundTrip::class, 'icon' => '🧳', 'advanced' => true],
        'mf_bigmac' => ['key' => 'bigmac', 'slug' => 'big-mac-index', 'class' => BigMacIndex::class, 'icon' => '🍔', 'advanced' => true],
        'mf_headlines' => ['key' => 'headlines', 'slug' => 'headlines', 'class' => Headlines::class, 'icon' => '📰', 'advanced' => true],
        'mf_central_bank' => ['key' => 'central_bank', 'slug' => 'central-bank-for-a-day', 'class' => CentralBankDay::class, 'icon' => '🏛️', 'advanced' => true],
        'mf_print_or_cut' => ['key' => 'print_or_cut', 'slug' => 'print-or-cut', 'class' => PrintOrCut::class, 'icon' => '⚖️', 'advanced' => true],
    ];

    /** Where each activity's facts come from (shown under its questions). */
    public const SOURCES = [
        'mf_fake' => [
            ['European Central Bank – The security features of the Europa series (leaflet)', 'https://www.ecb.europa.eu/euro/pdf/material/discover5/Leaflet_Public.en.pdf'],
            ['European Central Bank – Counterfeiting', 'https://www.ecb.europa.eu/euro/html/counterfeiting.en.html'],
        ],
        'mf_history' => [
            ['Citéco (Banque de France) – Birth of metal coins', 'https://citeco.fr/10000-years-history-economics/the-origins/birth-of-metal-coins'],
            ['Citéco (Banque de France) – First banknotes or paper money', 'https://citeco.fr/en/first-banknotes-or-paper-money'],
            ['Diners Club – Our history', 'https://dinersclub.com/about-us/history'],
            ['European Central Bank – Initial changeover (2002)', 'https://www.ecb.europa.eu/euro/changeover/2002/html/index.en.html'],
            ['World Bank – M-PESA: Mobile money for the "unbanked"', 'https://documents1.worldbank.org/curated/zh/304221468001788072/930107812_201408251051724/additional/634310PUB0Yes0061512B09780821387450.pdf'],
        ],
        'mf_prices' => [
            ['European Central Bank – Euro foreign exchange reference rates', 'https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html'],
        ],
        'mf_roundtrip' => [
            ['Banco de España – ¿Necesitas cambiar divisas? Esto te interesa', 'https://clientebancario.bde.es/pcb/es/blog/necesitas-cambiar-divisas--esto-te-interesa.html'],
            ['Consumer (Fundación Eroski) – Cambio de divisas', 'https://www.consumer.es/economia-domestica/finanzas/cambio-de-divisas.html'],
            ['European Central Bank – Euro foreign exchange reference rates', 'https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html'],
        ],
        'mf_bigmac' => [
            ['The Economist – The Big Mac index', 'https://www.economist.com/big-mac-index'],
            ['The Economist – Big Mac index data (July 2026)', 'https://github.com/TheEconomist/big-mac-data'],
        ],
        'mf_headlines' => [
            ['Bank of England – Who sets exchange rates?', 'https://www.bankofengland.co.uk/explainers/who-sets-exchange-rates'],
            ['Deutsches Historisches Museum (LeMO) – Inflation 1923', 'https://www.dhm.de/lemo/kapitel/weimarer-republik/innenpolitik/inflation-1923.html'],
        ],
        'mf_central_bank' => [
            ['European Central Bank – Monetary policy strategy', 'https://www.ecb.europa.eu/mopo/strategy/html/index.en.html'],
            ['European Central Bank – Why do interest rates change?', 'https://www.ecb.europa.eu/ecb-and-you/explainers/html/interest-rates-changes.en.html'],
            ['Treaty on the Functioning of the European Union – Article 123', 'https://eur-lex.europa.eu/eli/treaty/tfeu_2016/art_123/oj'],
        ],
        'mf_print_or_cut' => [
            ['Treaty on the Functioning of the European Union – Article 123', 'https://eur-lex.europa.eu/eli/treaty/tfeu_2016/art_123/oj'],
            ['Deutsches Historisches Museum (LeMO) – Inflation 1923', 'https://www.dhm.de/lemo/kapitel/weimarer-republik/innenpolitik/inflation-1923.html'],
            ['Steve H. Hanke (Cato Institute) – Hyperinflation: much talked about, little understood', 'https://www.cato.org/commentary/hyperinflation-much-talked-about-little-understood'],
        ],
    ];

    public static function typeOfSlug(string $slug): ?string
    {
        foreach (self::ALL as $type => $activity) {
            if ($activity['slug'] === $slug) {
                return $type;
            }
        }

        return null;
    }

    /** The questions of a student's activity, with the right answers. */
    public static function questions(string $type, int $studentId): array
    {
        return self::ALL[$type]['class']::questions($studentId);
    }

    /** What the activity's page shows above the questions (banknotes, a board, a table...). */
    public static function stage(string $type, int $studentId): array
    {
        return self::ALL[$type]['class']::stage($studentId);
    }

    /** The questions as the page gets them: without the answers and the explanations. */
    public static function forPage(array $questions): array
    {
        return array_map(fn ($q) => array_diff_key($q + ['kind' => 'choice'], array_flip(['answer', 'why'])), $questions);
    }

    /** @return array<int, bool> whether each answer is right */
    public static function grade(array $questions, array $answers): array
    {
        return array_map(function ($q, $i) use ($answers) {
            $given = $answers[$i] ?? null;
            if (($q['kind'] ?? 'choice') === 'order') {
                return is_array($given) && array_map('intval', array_values($given)) === $q['answer'];
            }

            return is_numeric($given) && (int) $given === $q['answer'];
        }, $questions, array_keys($questions));
    }

    /** A question whose right option is the first given; the options are mixed. */
    public static function choice(SeededRandom $rand, string $text, array $options, array $extra = []): array
    {
        $order = $rand->shuffle(array_keys($options));

        return ['kind' => 'choice', 'text' => $text, 'options' => array_map(fn ($i) => $options[$i], $order),
            'answer' => array_search(0, $order, true)] + $extra;
    }

    /** A question with options in a fixed order (A, B, C - up, down) and the right one's index. */
    public static function fixed(string $text, array $options, int $answer, array $extra = []): array
    {
        return ['kind' => 'choice', 'text' => $text, 'options' => $options, 'answer' => $answer] + $extra;
    }

    /** "4%" in English, "4 %" in Spanish, French and German (and 2,5 % with a comma). */
    public static function percent(float $n): string
    {
        $s = rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');

        return app()->getLocale() === 'en' ? $s . '%' : str_replace('.', ',', $s) . "\u{00A0}%";
    }

    /** The seeded generator of a student's activity. */
    public static function random(string $type, int $studentId): SeededRandom
    {
        return new SeededRandom(crc32($type . ':' . $studentId));
    }
}
