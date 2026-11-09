<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The onboarding pages showed the same sample data to every student: Citizen
 * ID ZV-2026-0076, account 123000-326474 and a Ƶ450.11 "Pescatarian"
 * spending snapshot - even after the survey had been skipped.
 */
class CitizenActivationPagesTest extends TestCase
{
    use RefreshDatabase;

    private function activatedStudent(string $language = 'en', float $surveyTotal = 0): User
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => 'locked', 'default_language' => $language,
        ]);
        $user = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id, 'citizenId' => 'ZV-2026-0099']);

        BankAccount::create([
            'student_id' => $user->id,
            'sid' => 1,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'bank_name' => 'Universal Bank',
            'primary_savings_account_number' => '123000-555123',
            'primary_savings_account_amount' => 3952.40,
            'card_pin' => '1234',
        ]);
        AutoDebitRequest::create([
            'user_id' => $user->id, 'sid' => 1, 'type' => 'City School', 'accountno' => 'SCHOOL-AUTO',
            'fullname' => $user->name, 'email' => $user->email, 'bankname' => 'Universal Bank',
            'bankaccountnumber' => '123000-555123', 'amount' => 1000, 'startDate' => now(),
            'signature' => $user->name, 'tandagree' => 1,
        ]);
        ConsumerSurvey::create([
            'user_id' => $user->id, 'sid' => 1, 'diet' => $surveyTotal ? 'vegan' : null, 'groceries' => [],
            'basket_cost' => $surveyTotal ? $surveyTotal - 100 : 0, 'lunch' => $surveyTotal ? 20 : 0,
            'transport' => $surveyTotal ? 80 : 0, 'activities' => 0, 'restaurants' => 0,
            'total_cost' => $surveyTotal, 'survey_month' => now()->format('M-Y'),
        ]);

        return $user;
    }

    public function test_the_welcome_page_shows_the_students_own_citizen_id(): void
    {
        $this->actingAs($this->activatedStudent())
            ->get(route('CitizenActivation.index'))
            ->assertOk()
            ->assertSee('ZV-2026-0099')
            ->assertDontSee('ZV-2026-0076');
    }

    public function test_the_completion_page_uses_the_students_own_account_and_says_the_survey_was_skipped(): void
    {
        $this->actingAs($this->activatedStudent())
            ->get(route('CitizenActivation.activationComplete'))
            ->assertOk()
            ->assertSee('123000-555123')
            ->assertSee('You skipped the', false)
            ->assertDontSee('123000-326474')
            ->assertDontSee('450.11');
    }

    public function test_the_completion_page_shows_the_students_own_spending_snapshot(): void
    {
        $this->actingAs($this->activatedStudent('en', 250))
            ->get(route('CitizenActivation.activationComplete'))
            ->assertOk()
            ->assertSee('Ƶ250.00')
            ->assertDontSee('You skipped the', false);
    }

    public function test_the_onboarding_pages_follow_the_class_language(): void
    {
        $this->actingAs($this->activatedStudent('de'))
            ->get(route('CitizenActivation.activationComplete'))
            ->assertOk()
            ->assertSee('Bürger-Aktivierung abgeschlossen')
            ->assertDontSee('Citizen Activation Complete');
    }
}
