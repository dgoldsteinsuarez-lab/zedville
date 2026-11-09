<?php

use App\Services\BadgeCalculatorService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Engagement badges already worked out, again with the fairer rules
 * (BadgeCalculatorService). Daniel, 2026-11-02: Esperanza did every Required
 * activity of October with the full participation points and got no badge,
 * because Bronze also needed an Optional activity and none was on any
 * calendar; then "si no hay opcional, puede recibir la de plata, porque no es
 * culpa de ellos" and "Oro si hace otras actividades no asignadas, pero si hay
 * opcionales, no". So each month now keeps how many Optional activities the
 * class had (optional_assigned) and how many activities the student did on
 * their own, not on the class calendar (own_completed).
 *
 * Month by month, oldest first, as the year's total adds up the months before.
 * A badge an admin set by hand stays (the override is not touched).
 */
return new class extends Migration {
    public function up()
    {
        Schema::table('student_badge_records', function (Blueprint $table) {
            if (!Schema::hasColumn('student_badge_records', 'optional_assigned')) {
                $table->unsignedSmallInteger('optional_assigned')->default(0)->after('required_completed');
            }
            if (!Schema::hasColumn('student_badge_records', 'own_completed')) {
                $table->unsignedSmallInteger('own_completed')->default(0)->after('optional_completed');
            }
        });

        $calculator = app(BadgeCalculatorService::class);
        $months = DB::table('student_badge_records')->select('year', 'month')->distinct()->orderBy('year')->orderBy('month')->get();

        foreach ($months as $m) {
            foreach (DB::table('student_badge_records')->where('year', $m->year)->where('month', $m->month)->pluck('student_id') as $studentId) {
                $calculator->calculateForStudent((int) $studentId, (int) $m->month, (int) $m->year);
            }
        }
    }

    public function down()
    {
        // the old rule's badges are not kept: only the new columns go
        foreach (['optional_assigned', 'own_completed'] as $column) {
            if (Schema::hasColumn('student_badge_records', $column)) {
                Schema::table('student_badge_records', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
