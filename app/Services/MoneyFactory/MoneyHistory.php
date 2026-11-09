<?php

namespace App\Services\MoneyFactory;

/**
 * The story of money (Junior, 2026-11-02 - "ordenar la historia del dinero:
 * trueque, monedas, billetes, tarjeta, móvil"). Six cards to put in order,
 * oldest first, then three simple questions. The facts: the first coins in
 * Lydia, 600-560 BC, and paper money in China under the Song dynasty, about
 * the year 1000 (Citéco, the Banque de France's economics museum); the first
 * charge card, Diners Club, New York 1950; euro banknotes in 2002; M-Pesa,
 * money on a mobile phone, Kenya 2007 (World Bank). After checking, each card
 * says when.
 */
class MoneyHistory
{
    /** The cards, oldest first, with their pictures. */
    public const CARDS = ['swap' => '🐚', 'coins' => '🪙', 'paper' => '📜', 'card' => '💳', 'euro' => '💶', 'mobile' => '📱'];

    /** The cards as the student sees them, mixed: their keys. */
    public static function shown(int $studentId): array
    {
        $rand = Activities::random('mf_history', $studentId);
        $keys = array_keys(self::CARDS);
        do {
            $shown = $rand->shuffle($keys);
        } while ($shown === $keys); // never already in order

        return $shown;
    }

    /** The cards are the first question (put them in order). */
    public static function stage(int $studentId): array
    {
        return [];
    }

    public static function questions(int $studentId): array
    {
        $shown = self::shown($studentId);
        $rand = Activities::random('mf_history:questions', $studentId);

        return [
            [
                'kind' => 'order',
                'text' => __('money_tasks.history.q_order'),
                'options' => array_map(fn ($key) => __('money_tasks.history.cards.' . $key), $shown),
                'answer' => array_map(fn ($key) => array_search($key, $shown, true), array_keys(self::CARDS)),
                // each card: its picture, name, a line about it, and when (shown once checked)
                'cards' => array_map(fn ($key) => [
                    'icon' => self::CARDS[$key],
                    'name' => __('money_tasks.history.cards.' . $key),
                    'text' => __('money_tasks.history.texts.' . $key),
                    'when' => __('money_tasks.history.when.' . $key),
                ], $shown),
            ],
            Activities::choice($rand, __('money_tasks.history.q_coins'), [
                __('money_tasks.history.a_lydia'), __('money_tasks.history.a_china'), __('money_tasks.history.a_kenya'),
            ]),
            Activities::choice($rand, __('money_tasks.history.q_paper'), [
                __('money_tasks.history.a_china'), __('money_tasks.history.a_lydia'), __('money_tasks.history.a_europe'),
            ]),
            Activities::choice($rand, __('money_tasks.history.q_swap'), [
                __('money_tasks.history.a_swap_right'), __('money_tasks.history.a_swap_heavy'), __('money_tasks.history.a_swap_easy'),
            ], ['why' => __('money_tasks.history.why_swap')]),
        ];
    }
}
