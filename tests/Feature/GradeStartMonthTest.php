<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\LoginQuestion;
use App\Models\Month;
use App\Models\SchoolClass;
use App\Models\SchoolMonthSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-31: "i should be able to choose the month that a grade
 * starts, because it can be independent from the school". A Grade's own
 * start month is Month 1 of its students' questions; without one, the
 * school's.
 */
class GradeStartMonthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-10 10:00');
        SchoolMonthSetting::create(['school_id' => 1, 'start_month' => 9]); // the school: September
    }

    private function student(Grade $grade): User
    {
        $class = SchoolClass::create(['sid' => $grade->sid, 'name' => $grade->name . 'A', 'grade_id' => $grade->id]);

        return User::factory()->create(['role' => 4, 'sid' => $grade->sid, 'grade' => $class->id]);
    }

    public function test_a_grade_with_its_own_start_month_overrides_the_school(): void
    {
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $followsSchool = $this->student(Grade::create(['sid' => '1', 'name' => 'Grade 8', 'grade_level_id' => $rookie->id]));
        $november = $this->student(Grade::create(['sid' => '1', 'name' => 'Grade 9', 'grade_level_id' => $rookie->id, 'start_month' => 11]));
        $january = $this->student(Grade::create(['sid' => '1', 'name' => 'Grade 10', 'grade_level_id' => $rookie->id, 'start_month' => 1]));

        $this->assertSame(3, SchoolMonthSetting::progressionMonthForStudent($followsSchool)); // September, October, November
        $this->assertSame(1, SchoolMonthSetting::progressionMonthForStudent($november));
        $this->assertSame(11, SchoolMonthSetting::progressionMonthForStudent($january));

        // the question of the day comes from each student's own month
        $question = fn ($month) => LoginQuestion::create(['sid' => null, 'grade_level_id' => $rookie->id, 'type' => 'true_false',
            'month_id' => Month::where('name', "Month $month")->value('id'), 'question' => "A Month $month question"]);
        $month1 = $question(1);
        $month3 = $question(3);

        $this->actingAs($followsSchool)->getJson('/login-quiz/today')->assertOk()->assertJson(['show' => true, 'id' => $month3->id]);
        $this->actingAs($november)->getJson('/login-quiz/today')->assertOk()->assertJson(['show' => true, 'id' => $month1->id]);
    }

    public function test_the_admin_chooses_when_a_grade_starts(): void
    {
        $admin = User::factory()->create(['role' => 2, 'sid' => 1]);
        $session = ['selected_school' => 1];

        // when the Grade is added
        $this->actingAs($admin)->withSession($session)->get(route('admin.grades.index'))->assertOk()
            ->assertSee('Starts in')->assertSee('Same as the school (September)');
        $this->actingAs($admin)->withSession($session)->post(route('admin.grades.store'), ['name' => 'Grade 11', 'start_month' => 1])->assertRedirect();
        $this->actingAs($admin)->withSession($session)->post(route('admin.grades.store'), ['name' => 'Grade 12'])->assertRedirect();

        $grade = Grade::where('name', 'Grade 11')->first();
        $this->assertSame(1, $grade->start_month);
        $this->assertNull(Grade::where('name', 'Grade 12')->value('start_month'));
        $this->actingAs($admin)->withSession($session)->get(route('admin.grades.index'))
            ->assertSeeInOrder(['Grade 11', 'January', 'Grade 12', "School's (September)"], false);

        // on the Grade's page: its own month, or the school's
        $this->actingAs($admin)->get(route('admin.grades.general-information', $grade))->assertOk()
            ->assertSee('Grade 11 starts in')->assertSee('Same as the school (September)')
            ->assertSee('November is <strong>Month 11</strong> for Grade 11', false);

        $page = route('admin.grades.general-information', $grade) . '#start-month';
        $this->actingAs($admin)->put(route('admin.grades.start-month', $grade), ['start_month' => 11])->assertRedirect($page)
            ->assertSessionHas('start_month_saved', 'Grade 11 starts in November. November is Month 1 for Grade 11.');
        $this->assertSame(11, $grade->fresh()->start_month);

        $this->actingAs($admin)->put(route('admin.grades.start-month', $grade), ['start_month' => ''])->assertRedirect($page)
            ->assertSessionHas('start_month_saved', 'Grade 11 follows the school’s start month. November is Month 3 for Grade 11.');
        $this->assertNull($grade->fresh()->start_month);

        // and the school's, for every Grade without its own
        $this->actingAs($admin)->put(route('admin.grades.school-start-month', $grade), ['school_start_month' => 10])->assertRedirect($page)
            ->assertSessionHas('start_month_saved', 'The school starts in October. November is Month 2 for Grade 11.');
        $this->assertSame(10, SchoolMonthSetting::schoolStartMonth(1));

        $this->actingAs($admin)->put(route('admin.grades.start-month', $grade), ['start_month' => 13])->assertSessionHasErrors('start_month');
    }

    public function test_a_school_admin_only_changes_their_own_schools_grades(): void
    {
        $other = Grade::create(['sid' => '2', 'name' => 'Other 9A']);
        $admin = User::factory()->create(['role' => 2, 'sid' => 1]);

        $this->actingAs($admin)->put(route('admin.grades.start-month', $other), ['start_month' => 3])->assertNotFound();
        $this->actingAs($admin)->put(route('admin.grades.school-start-month', $other), ['school_start_month' => 3])->assertNotFound();
        $this->assertNull($other->fresh()->start_month);
        $this->assertNull(SchoolMonthSetting::schoolStartMonth(2));
    }

    public function test_a_grade_email_campaign_reaches_that_grades_students_from_its_start(): void
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade 9', 'start_month' => 11]);
        $inGrade = $this->student($grade);
        $otherGrade = $this->student(Grade::create(['sid' => '1', 'name' => 'Grade 8']));
        $template = EmailTemplate::create(['name' => 'Test', 'subject' => 'Welcome', 'content' => 'Hello {{name}}!']);

        // 20 days in: 21 November for Grade 9 (the school's would be 21 September)
        EmailCampaign::create(['template_id' => $template->id, 'name' => 'Grade 9 has started', 'scope_type' => 'grade',
            'scope_school_id' => 1, 'scope_grade_id' => $grade->id, 'trigger_type' => 'relative_to_school_start',
            'relative_days_offset' => 20, 'status' => 'scheduled']);

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertSame(0, DB::table('mailbox')->count());

        Carbon::setTestNow('2026-11-21 08:00');
        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertDatabaseHas('mailbox', ['student_id' => $inGrade->id]);
        $this->assertDatabaseMissing('mailbox', ['student_id' => $otherGrade->id]);
    }
}
