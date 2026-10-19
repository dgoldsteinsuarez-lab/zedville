<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-19: this was wired into the wrong badge - "no deberia
 * ser en Finance Badge... deberia ser en Engagement badge... no deberia
 * llamarse Task 6." Moving it out of FinHero entirely (see
 * BasketController/ParticipationService) - renaming the picks table to
 * match. No data worth preserving (feature shipped same day).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::rename('finhero_task6_student_picks', 'substitute_goods_picks');
    }

    public function down()
    {
        Schema::rename('substitute_goods_picks', 'finhero_task6_student_picks');
    }
};
