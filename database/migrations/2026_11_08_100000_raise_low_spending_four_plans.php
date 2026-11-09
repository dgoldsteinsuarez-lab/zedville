<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-11-08: "entre Spending y Spending 4 Low, deberían sumar esos
 * 91% ... si no es así, súbelos". With the bills, the 20% to the fund and the
 * month's shopping from the consumer survey (charged every month from now on,
 * BankController::ensureMonthlyBills), a Spending activity and a Low plan
 * need at least 24% of the salary (WantsPlanService::LOW_TOTAL) to take a
 * student to 91%: the Low plans go up a third, 640-810 Ƶ to 850-1,080 Ƶ,
 * rounded to tens. The High plans (1,100-1,600 Ƶ) already reach it alone.
 */
return new class extends Migration {
    private const FACTOR = 1.33;

    public function up()
    {
        foreach (DB::table('wants_plans')->where('kind', 'low')->get(['id', 'cost']) as $plan) {
            DB::table('wants_plans')->where('id', $plan->id)->update(['cost' => round($plan->cost * self::FACTOR / 10) * 10, 'updated_at' => now()]);
        }
    }

    public function down()
    {
        foreach (DB::table('wants_plans')->where('kind', 'low')->get(['id', 'cost']) as $plan) {
            DB::table('wants_plans')->where('id', $plan->id)->update(['cost' => round($plan->cost / self::FACTOR / 10) * 10, 'updated_at' => now()]);
        }
    }
};
