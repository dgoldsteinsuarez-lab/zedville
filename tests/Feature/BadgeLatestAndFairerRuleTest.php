<?php

namespace Tests\Feature;

use App\Models\FinheroBadgeRecord;
use App\Models\SchoolClass;
use App\Models\StudentBadgeRecord;
use App\Models\User;
use App\Services\ActivityCompletionService;
use App\Services\BadgeCalculatorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-11-02: "if someone gets a Badge for last month, he should be
 * able to see it in the main part ... the kids should be proud of having a
 * badge" and "why there is no Engagement Badge, Esperanza should have gotten
 * minimum the least valuable one ... should we make it less rigid". Every
 * Required activity done (and 5 participation points) is Bronze; each
 * Optional activity raises a level, and when the class has none that month
 * Silver needs none ("si no hay opcional, puede recibir la de plata, porque no
 * es culpa de ellos"). The latest badge earned is shown first.
 */
class BadgeLatestAndFairerRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-10 12:00:00');
    }

    public function test_all_required_activities_are_bronze_and_each_optional_one_raises_a_level(): void
    {
        $calculator = app(BadgeCalculatorService::class);

        // 5 of 5 Required and 15 points, but an Optional activity on the calendar left undone
        $this->assertSame('BRONZE', $calculator->determineMonthlyBadge(5, 5, 0, 15, 1));
        $this->assertSame('BRONZE', $calculator->determineMonthlyBadge(5, 5, 0, 15));
        $this->assertSame('SILVER', $calculator->determineMonthlyBadge(5, 5, 1, 7));
        $this->assertSame('GOLD', $calculator->determineMonthlyBadge(5, 5, 2, 10));
        $this->assertSame('PLATINUM', $calculator->determineMonthlyBadge(5, 5, 3, 15));
        // still: every Required activity for any badge, and none without a Required one (spec v2, §7)
        $this->assertSame('NONE', $calculator->determineMonthlyBadge(5, 4, 3, 15));
        $this->assertSame('NONE', $calculator->determineMonthlyBadge(0, 0, 3, 15));
        $this->assertSame('NONE', $calculator->determineMonthlyBadge(5, 5, 0, 4));

        // what the next badge still needs
        $this->assertSame(['badge' => 'BRONZE', 'required' => 2, 'optional' => 0, 'own' => 0, 'points' => 3], $calculator->nextLevel(5, 3, 0, 2));
        $this->assertSame(['badge' => 'SILVER', 'required' => 0, 'optional' => 1, 'own' => 0, 'points' => 0], $calculator->nextLevel(5, 5, 0, 15));
        $this->assertNull($calculator->nextLevel(5, 5, 3, 15));
    }

    public function test_with_no_optional_activity_on_the_class_calendar_silver_needs_none(): void
    {
        $calculator = app(BadgeCalculatorService::class);

        // Esperanza in October: 5 of 5 Required, 15 points, and no Optional activity on any calendar
        $this->assertSame('SILVER', $calculator->determineMonthlyBadge(5, 5, 0, 15, 0));
        $this->assertSame('SILVER', $calculator->determineMonthlyBadge(5, 5, 0, 7, 0));
        $this->assertSame('BRONZE', $calculator->determineMonthlyBadge(5, 5, 0, 6, 0));
        // still every Required activity first, and the Optional ones when the class has them
        $this->assertSame('NONE', $calculator->determineMonthlyBadge(5, 4, 0, 15, 0));
        $this->assertSame('GOLD', $calculator->determineMonthlyBadge(5, 5, 2, 15, 2));

        // the next badge: Silver without an Optional one; none above what the class has
        $this->assertSame(['badge' => 'SILVER', 'required' => 0, 'optional' => 0, 'own' => 0, 'points' => 2], $calculator->nextLevel(5, 5, 0, 5, 0));
        $this->assertNull($calculator->nextLevel(5, 5, 1, 15, 1));
        $this->assertSame(['badge' => 'GOLD', 'required' => 0, 'optional' => 1, 'own' => 0, 'points' => 0], $calculator->nextLevel(5, 5, 1, 15, 2));
    }

    public function test_with_no_optional_activity_gold_takes_activities_done_on_their_own(): void
    {
        $calculator = app(BadgeCalculatorService::class);

        // Daniel, 2026-11-02: "Oro si hace otras actividades no asignadas, pero si hay opcionales, no"
        $this->assertSame('GOLD', $calculator->determineMonthlyBadge(5, 5, 0, 10, 0, 2));
        $this->assertSame('SILVER', $calculator->determineMonthlyBadge(5, 5, 0, 10, 0, 1));
        $this->assertSame('SILVER', $calculator->determineMonthlyBadge(5, 5, 0, 9, 0, 2));
        // Platinum always needs the calendar's Optional activities
        $this->assertSame('GOLD', $calculator->determineMonthlyBadge(5, 5, 0, 15, 0, 6));
        // with Optional activities on the calendar, activities on their own don't stand in for them
        $this->assertSame('BRONZE', $calculator->determineMonthlyBadge(5, 5, 0, 15, 1, 6));
        $this->assertSame('SILVER', $calculator->determineMonthlyBadge(5, 5, 1, 15, 2, 6));

        // the next badge: Gold, with the activities on their own still needed; at Gold, the best possible
        $this->assertSame(['badge' => 'GOLD', 'required' => 0, 'optional' => 0, 'own' => 2, 'points' => 0], $calculator->nextLevel(5, 5, 0, 15, 0));
        $this->assertSame(['badge' => 'GOLD', 'required' => 0, 'optional' => 0, 'own' => 1, 'points' => 3], $calculator->nextLevel(5, 5, 0, 7, 0, 1));
        $this->assertNull($calculator->nextLevel(5, 5, 0, 15, 0, 2));
    }

    public function test_the_month_is_worked_out_with_the_optional_activities_the_class_has(): void
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        foreach (['Bank Account', 'Emergency Fund'] as $title) {
            $eventId = DB::table('calendar_events')->insertGetId(['classId' => $class->id, 'sid' => '1', 'title' => $title, 'position' => 'required',
                'start' => '2026-11-03 00:00:00', 'end' => '2026-11-30 00:00:00', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('activity_completions')->insert(['student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'required',
                'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('participation_logs')->insert(['student_id' => $student->id, 'action_type' => 'optional', 'action_detail' => 'shopping',
            'points_earned' => 8, 'month' => 11, 'year' => 2026, 'academic_year' => '2026-2027', 'earned_at' => now()]);
        $calculator = app(BadgeCalculatorService::class);

        $record = $calculator->calculateForStudent($student->id, 11, 2026);
        $this->assertSame('SILVER', $record->monthly_badge);
        $this->assertSame(0, (int) $record->optional_assigned);

        // two activities done on their own (a Money Factory one as practice, the Emergency Fund with no calendar
        // event for the class) and 10 points: Gold
        app(ActivityCompletionService::class)->completeOwn($student->id, 'mf_history');
        $this->assertFalse(app(ActivityCompletionService::class)->completeOwn($student->id, 'mf_history'));
        DB::table('activity_completions')->insert(['student_id' => $student->id, 'activity_id' => 0, 'activity_type' => 'emergency_fund',
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('participation_logs')->insert(['student_id' => $student->id, 'action_type' => 'optional', 'action_detail' => 'voting',
            'points_earned' => 2, 'month' => 11, 'year' => 2026, 'academic_year' => '2026-2027', 'earned_at' => now()]);
        $record = $calculator->calculateForStudent($student->id, 11, 2026);
        $this->assertSame('GOLD', $record->monthly_badge);
        $this->assertSame(2, (int) $record->own_completed);
        $this->assertSame(2, (int) $record->required_completed);

        // an Optional activity on the class calendar: Silver needs it done, and the ones on their own don't count
        DB::table('calendar_events')->insert(['classId' => $class->id, 'sid' => '1', 'title' => 'Wants Plan', 'position' => 'optional',
            'start' => '2026-11-12 00:00:00', 'end' => '2026-11-30 00:00:00', 'created_at' => now(), 'updated_at' => now()]);
        $record = $calculator->calculateForStudent($student->id, 11, 2026);
        $this->assertSame('BRONZE', $record->monthly_badge);
        $this->assertSame(1, (int) $record->optional_assigned);
    }

    public function test_the_badges_tab_without_optional_activities(): void
    {
        $student = $this->silverWithoutOptional(['default_language' => 'es']);

        // Silver; Gold with 2 activities on their own and 1 point more
        $this->actingAs($student)->get('/profile')->assertOk()
            ->assertSee('De momento, este mes: Plata')
            ->assertSee('ninguna este mes')
            ->assertSee('Actividades por tu cuenta')
            ->assertSee('Siguiente insignia: Oro')
            ->assertSee('2 actividades por tu cuenta · 1 punto de participación')
            ->assertSee('Si en un mes tu clase no tiene actividades opcionales, la Plata no necesita ninguna y el Oro se consigue con 2 actividades por tu cuenta.')
            ->assertSee('Actividades por tu cuenta: las que no están en el calendario de tu clase, como las de la Fábrica de dinero.');

        // Gold: the best possible this month
        $this->doneOnTheirOwn($student, 2, 1);
        $this->actingAs($student)->get('/profile')->assertOk()
            ->assertSee('De momento, este mes: Oro')
            ->assertSee('Es la mejor insignia que puedes conseguir este mes: el Platino necesita actividades opcionales y tu clase no tiene ninguna.');

        // a hybrid class: the short versions, in both languages
        $student = $this->silverWithoutOptional(['default_language' => 'es', 'language_level' => 'hybrid']);
        $this->doneOnTheirOwn($student, 2, 1);
        $this->actingAs($student)->get('/profile')->assertOk()
            ->assertSee('Es la mejor insignia que puedes conseguir este mes.')
            ->assertSee('It&#039;s the best badge you can get this month.', false)
            ->assertSee('Sin opcionales: Plata sin ellas, Oro con 2 actividades por tu cuenta.')
            ->assertDontSee('el Platino necesita');
    }

    /** Activities done on their own this month (Money Factory ones as practice), and participation points more. */
    private function doneOnTheirOwn(User $student, int $activities, int $points): void
    {
        foreach (array_slice(['mf_history', 'mf_fake', 'mf_prices'], 0, $activities) as $type) {
            app(ActivityCompletionService::class)->completeOwn($student->id, $type);
        }
        DB::table('participation_logs')->insert(['student_id' => $student->id, 'action_type' => 'optional', 'action_detail' => 'voting',
            'points_earned' => $points, 'month' => 11, 'year' => 2026, 'academic_year' => '2026-2027', 'earned_at' => now()]);
    }

    /** A student of a new class who did this month's Required activity, with 9 points, and no Optional activity on the calendar. */
    private function silverWithoutOptional(array $class): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()] + $class);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $eventId = DB::table('calendar_events')->insertGetId(['classId' => $class->id, 'sid' => '1', 'title' => 'Bank Account', 'position' => 'required',
            'start' => '2026-11-03 00:00:00', 'end' => '2026-11-30 00:00:00', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('activity_completions')->insert(['student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'required',
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('participation_logs')->insert(['student_id' => $student->id, 'action_type' => 'optional', 'action_detail' => 'shopping',
            'points_earned' => 9, 'month' => 11, 'year' => 2026, 'academic_year' => '2026-2027', 'earned_at' => now()]);

        return $student;
    }

    public function test_last_months_badge_is_the_big_one_on_the_badges_tab_and_the_home_page(): void
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        StudentBadgeRecord::create(['student_id' => $student->id, 'month' => 10, 'year' => 2026, 'academic_year' => '2026-2027',
            'required_assigned' => 5, 'required_completed' => 5, 'optional_completed' => 0, 'participation_points' => 15,
            'monthly_badge' => 'BRONZE', 'monthly_badge_points' => 1, 'accumulated_points' => 1, 'accumulated_badge' => 'NONE']);
        FinheroBadgeRecord::create(['student_id' => $student->id, 'month' => 10, 'year' => 2026, 'academic_year' => '2026-2027',
            'quiz_pts' => 3, 'library_pts' => 10, 'activity_pts' => 3, 'total_earned' => 16, 'total_available' => 23, 'monthly_pct' => 69.57,
            'monthly_badge' => 'FINHERO', 'monthly_badge_points' => 2, 'accumulated_points' => 2, 'accumulated_badge' => 'NONE']);

        $page = $this->actingAs($student)->get('/profile')->assertOk()
            ->assertSee('Well done! In October you earned a new badge:')
            ->assertSee("Last month&#039;s badge", false)
            ->assertSee('Engagement: Bronze · FinHero: FinHero')
            ->assertSee('This month');
        $badges = $page->viewData('badges');
        $this->assertSame('BRONZE', $badges['engagement']['latest']['badge']);
        $this->assertTrue($badges['engagement']['latest']['last_month']);
        $this->assertSame('FINHERO', $badges['finhero']['latest']['badge']);
        $this->assertSame(['badge' => 'BRONZE', 'points' => 8], $badges['engagement']['year']['next']);

        // the home page shows it too, with its month and the medal itself, though November has none yet
        $this->actingAs($student)->get('/dashboard')->assertOk()
            ->assertSee('Engagement (Bronze)')->assertSee('FinHero (FinHero)')->assertSee('October 2026')
            ->assertSee('<symbol id="m-bronze"', false)->assertSee('<use href="#m-bronze"/>', false)->assertSee('<use href="#m-finhero"/>', false);
    }

    public function test_the_badges_tab_in_spanish(): void
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'default_language' => 'es']);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        StudentBadgeRecord::create(['student_id' => $student->id, 'month' => 10, 'year' => 2026, 'academic_year' => '2026-2027',
            'required_assigned' => 5, 'required_completed' => 5, 'optional_completed' => 0, 'participation_points' => 15,
            'monthly_badge' => 'BRONZE', 'monthly_badge_points' => 1, 'accumulated_points' => 1, 'accumulated_badge' => 'NONE']);
        DB::table('calendar_events')->insert(['classId' => $class->id, 'sid' => '1', 'title' => 'Emergency Fund', 'position' => 'required',
            'start' => '2026-11-03 00:00:00', 'end' => '2026-11-30 00:00:00', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->get('/profile')->assertOk()
            ->assertSee('La insignia del mes pasado')->assertSee('Bronce')
            ->assertSee('Siguiente insignia: Bronce')->assertSee('1 actividad obligatoria')
            ->assertSee('Bronce: todas las actividades obligatorias y 5 puntos de participación');
    }
}
