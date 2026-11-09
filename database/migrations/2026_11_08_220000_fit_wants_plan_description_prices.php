<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A plan's description lists what it pays for, each with its price ("the
 * cinema every Saturday (Ƶ 80)..."). When the Low plans went up a third and
 * then down a sixth (2026-11-08) only the plan's cost changed, so the parts no
 * longer added up to it: "Cinema month" cost Ƶ 720 but its parts made Ƶ 650.
 * Here every description's prices are scaled to add up to the cost again -
 * rounded to tens, the dearest part taking what rounding leaves - in English
 * and in each translation.
 */
return new class extends Migration {
    public function up()
    {
        foreach (DB::table('wants_plans')->get(['id', 'cost', 'description', 'translations']) as $plan) {
            $target = (int) round((float) $plan->cost);
            $translations = json_decode((string) $plan->translations, true) ?: [];
            foreach ($translations as $lang => $fields) {
                if (isset($fields['description'])) {
                    $translations[$lang]['description'] = self::fit($fields['description'], $target);
                }
            }
            DB::table('wants_plans')->where('id', $plan->id)->update([
                'description' => self::fit($plan->description, $target),
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    /** The "(Ƶ 80)" prices of a text, scaled to add up to $target. */
    public static function fit(?string $text, int $target): ?string
    {
        $pattern = '/\(Ƶ\s?([\d.,]+)\)/u';
        if (!$text || !preg_match_all($pattern, $text, $m)) {
            return $text;
        }
        $amounts = array_map(fn ($a) => (int) preg_replace('/\D/', '', $a), $m[1]);
        $sum = array_sum($amounts);
        if ($sum === 0 || $sum === $target) {
            return $text;
        }
        $scaled = array_map(fn ($a) => (int) (round($a * $target / $sum / 10) * 10), $amounts);
        $scaled[array_search(max($scaled), $scaled, true)] += $target - array_sum($scaled);
        $i = 0;

        return preg_replace_callback($pattern, function () use (&$i, $scaled) {
            return '(Ƶ ' . $scaled[$i++] . ')';
        }, $text);
    }

    public function down()
    {
        // the earlier prices are gone; they didn't add up anyway
    }
};
