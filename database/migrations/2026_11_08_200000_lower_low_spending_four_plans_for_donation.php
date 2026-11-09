<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-11-08: the monthly donation (4-6% of the salary, DonationService)
 * takes the place of part of the spending activities ("podríamos reducir los
 * Spending activities y añadir esto como otra opción para gastar dinero"). A
 * Spending activity and a Low plan now need 20% of the salary instead of 24%
 * (WantsPlanService::LOW_TOTAL), so the Low plans go down a sixth, 850-1,080 Ƶ
 * to 710-900 Ƶ, rounded to tens: about 158 Ƶ less on average, the smallest
 * donation.
 */
return new class extends Migration {
    private const FACTOR = 5 / 6;

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
