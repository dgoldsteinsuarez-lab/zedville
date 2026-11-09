<?php

namespace App\Services\MoneyFactory;

/**
 * Print money or cut spending? (advanced, 2026-11-02 - "la opción de políticos
 * irresponsables ... que en vez de bajar el gasto público, imprimen dinero
 * creando inflación"). Zedland's government spends 120 million Ƶ and collects
 * 100 million: the deficit, borrowing with bonds, what printing the gap does,
 * why it tempts politicians, and the EU rule. The facts are those of "How does
 * money get into the economy?" (Germany 1923, Zimbabwe 2008, Article 123).
 */
class PrintOrCut
{
    public const SPENDING = 120;
    public const TAXES = 100;

    public static function stage(int $studentId): array
    {
        return ['spending' => self::SPENDING, 'taxes' => self::TAXES];
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('mf_print_or_cut', $studentId);
        $ask = fn (string $q, array $answers, bool $why = true) => Activities::choice($rand,
            __('money_tasks.print_or_cut.q_' . $q, ['spending' => self::SPENDING, 'taxes' => self::TAXES, 'gap' => self::SPENDING - self::TAXES]),
            array_map(fn ($a) => __('money_tasks.print_or_cut.a_' . $q . '_' . $a), $answers),
            $why ? ['why' => __('money_tasks.print_or_cut.why_' . $q)] : []);

        return [
            $ask('gap', ['deficit', 'profit', 'salary']),
            $ask('bonds', ['repay', 'gift', 'never']),
            $ask('print', ['inflation', 'richer', 'taxes']),
            $ask('tempting', ['unpopular', 'no_effect', 'law']),
            $ask('ecb', ['no', 'yes', 'schools']),
        ];
    }
}
