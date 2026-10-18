<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\LoginQuestion;
use App\Models\Month;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\SchoolMonthSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel (2026-10-19): Log In Questions are fixed per Level (currently
 * only Rookie has real content), and the real calendar month that counts
 * as "month 1" of a Level's progression is set per school
 * (school_month_settings.start_month, already built - just never wired to
 * question selection until now). Covers the resolution chain: student ->
 * Class -> Grade -> Level, and school's start_month -> elapsed calendar
 * months -> progression month -> matching question.
 */
class LoginQuizLevelAndMonthTest extends TestCase
{
    use RefreshDatabase;

    private function studentAtLevel(string $levelName, int $schoolStartMonth): User
    {
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $level = GradeLevel::where('name', $levelName)->first();
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8', 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'grade_id' => $grade->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id]);

        SchoolMonthSetting::updateOrCreate(['school_id' => $school->id], ['start_month' => $schoolStartMonth]);

        return $student;
    }

    public function test_student_gets_a_question_matching_their_level_and_the_schools_current_progression_month(): void
    {
        $student = $this->studentAtLevel('Rookie', now()->month); // start_month = this month -> progression month 1
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $month1 = Month::where('name', 'Month 1')->first();

        $matching = LoginQuestion::create([
            'sid' => null, 'grade_level_id' => $rookie->id, 'month_id' => $month1->id,
            'question' => 'Rookie month-1 question', 'type' => 'true_false',
        ]);

        // A distractor for a different Level/month that must NOT be picked
        $intermediate = GradeLevel::where('name', 'Intermediate')->first();
        LoginQuestion::create([
            'sid' => null, 'grade_level_id' => $intermediate->id, 'month_id' => $month1->id,
            'question' => 'Intermediate question', 'type' => 'true_false',
        ]);

        $response = $this->actingAs($student)->getJson('/login-quiz/today');

        $response->assertOk();
        $response->assertJson(['show' => true, 'id' => $matching->id]);
    }

    public function test_progression_month_wraps_correctly_with_elapsed_calendar_months(): void
    {
        // start_month = 3 months ago -> student should now be in progression month 4
        $startMonth = ((now()->month - 3 - 1 + 12) % 12) + 1;
        $student = $this->studentAtLevel('Rookie', $startMonth);
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $month4 = Month::where('name', 'Month 4')->first();

        $matching = LoginQuestion::create([
            'sid' => null, 'grade_level_id' => $rookie->id, 'month_id' => $month4->id,
            'question' => 'Rookie month-4 question', 'type' => 'true_false',
        ]);

        $response = $this->actingAs($student)->getJson('/login-quiz/today');

        $response->assertOk();
        $response->assertJson(['show' => true, 'id' => $matching->id]);
    }

    public function test_falls_back_to_any_unanswered_question_when_nothing_matches_level_and_month_yet(): void
    {
        $student = $this->studentAtLevel('Rookie', now()->month);

        $other = GradeLevel::where('name', 'Advance')->first();
        $someMonth = Month::where('name', 'Month 7')->first();
        $fallback = LoginQuestion::create([
            'sid' => null, 'grade_level_id' => $other->id, 'month_id' => $someMonth->id,
            'question' => 'Unrelated question', 'type' => 'true_false',
        ]);

        $response = $this->actingAs($student)->getJson('/login-quiz/today');

        $response->assertOk();
        $response->assertJson(['show' => true, 'id' => $fallback->id]);
    }
}
