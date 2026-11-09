<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Update My Profile" on the survey results opens this page. It includes
 * the profile form, which needs the student's address - the controller
 * didn't pass it, so the page crashed (Undefined variable $myAddress).
 */
class ConsumerProfileSurveyPageTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $language = 'en'): User
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => 'locked', 'default_language' => $language,
        ]);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    public function test_the_consumer_profile_survey_page_opens(): void
    {
        $this->actingAs($this->student())
            ->get(route('consumer-profile-survey'))
            ->assertOk()
            ->assertSee('Consumer Profile Survey');
    }

    public function test_the_survey_shows_in_the_class_language(): void
    {
        $this->actingAs($this->student('fr'))
            ->get(route('consumer-profile-survey'))
            ->assertOk()
            ->assertSee('Ton premier mois à Zedville')
            ->assertSee('Toujours de la maison')
            ->assertDontSee('Always from Home');
    }
}
