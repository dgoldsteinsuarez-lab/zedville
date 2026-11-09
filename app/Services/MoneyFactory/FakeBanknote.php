<?php

namespace App\Services\MoneyFactory;

/**
 * Real or fake banknote? (Junior, 2026-11-02 - "¿Billete falso?": find it with
 * feel, look and tilt). The euro's own three checks, from the European Central
 * Bank's leaflet: feel the raised print, look against the light for the
 * watermark and the security thread, tilt for the hologram and the number that
 * changes colour. Three sets of three Zed banknotes; in each set one is fake
 * and fails a different check. Then: what to do with a banknote you think is
 * fake (the ECB: take it to the police or a bank).
 */
class FakeBanknote
{
    public const CHECKS = ['feel', 'look', 'tilt'];

    /** Each set: its value, three notes (which checks each passes, its serial) and which one is fake. */
    public static function sets(int $studentId): array
    {
        $rand = Activities::random('mf_fake', $studentId);
        $sets = [];
        foreach ($rand->shuffle(self::CHECKS) as $i => $missing) {
            $fake = $rand->between(0, 2);
            $notes = [];
            for ($n = 0; $n < 3; $n++) {
                $notes[] = array_fill_keys(self::CHECKS, true) + ['serial' => 'ZV ' . $rand->between(1000, 9999) . ' ' . $rand->between(1000, 9999)];
            }
            $notes[$fake][$missing] = false;
            $sets[] = ['value' => [10, 20, 50][$i], 'notes' => $notes, 'fake' => $fake, 'missing' => $missing];
        }

        return $sets;
    }

    public static function stage(int $studentId): array
    {
        return ['sets' => array_map(fn ($set) => ['value' => $set['value'], 'notes' => $set['notes']], self::sets($studentId))];
    }

    public static function questions(int $studentId): array
    {
        $letters = ['A', 'B', 'C'];
        $questions = [];
        foreach (self::sets($studentId) as $i => $set) {
            $questions[] = Activities::fixed(
                __('money_tasks.fake.q_set', ['n' => $i + 1]),
                array_map(fn ($letter) => __('money_tasks.fake.note', ['letter' => $letter]), $letters),
                $set['fake'],
                ['why' => __('money_tasks.fake.why_' . $set['missing'])],
            );
        }

        $questions[] = Activities::choice(Activities::random('mf_fake:suspect', $studentId), __('money_tasks.fake.q_suspect'), [
            __('money_tasks.fake.a_suspect_right'), __('money_tasks.fake.a_suspect_spend'), __('money_tasks.fake.a_suspect_keep'),
        ], ['why' => __('money_tasks.fake.why_suspect')]);

        return $questions;
    }
}
