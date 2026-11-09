<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Consumer Profile survey as "Your first month in Zedville" (Daniel,
 * 2026-10-23): Need/Want split and a consumer type at the end.
 */
class SurveyWizardTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $student = User::factory()->create(['role' => 4, 'sid' => '1']);
        $account = BankAccount::create([
            'student_id' => $student->id, 'sid' => '1', 'student_name' => $student->name, 'student_email' => $student->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        Transaction1::create([
            'user_id' => $student->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now(),
            'description' => 'Monthly Salary', 'type' => 'credit', 'category' => 'Income', 'amount' => 3952.40, 'balance' => 3952.40,
        ]);

        return $student;
    }

    public function test_the_consumer_type_follows_needs_and_wants(): void
    {
        // basket, lunch, transport, activities, eating out
        $this->assertSame('cruiser', ConsumerSurvey::profileType(150, 25, 240, 0, 0));
        $this->assertSame('foodie', ConsumerSurvey::profileType(130, 25, 80, 50, 80));
        $this->assertSame('explorer', ConsumerSurvey::profileType(130, 25, 80, 155, 50));
        $this->assertSame('saver', ConsumerSurvey::profileType(150, 20, 0, 0, 0));
        $this->assertSame('balanced', ConsumerSurvey::profileType(150, 25, 80, 50, 0));

        $this->assertSame(['needs' => 175.0, 'wants' => 290.0, 'total' => 465.0], ConsumerSurvey::needsWants(150, 25, 240, 50, 0));
    }

    public function test_settings_shows_the_new_survey_and_no_longer_loads_surveys_js(): void
    {
        $this->actingAs($this->student())->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Your first month in Zedville')
            ->assertSee('Ms. Rodriguez')
            ->assertDontSee('asset/front/js/surveys.js', false);
    }

    public function test_saving_returns_the_consumer_type_and_settings_shows_it(): void
    {
        $student = $this->student();

        $this->actingAs($student)->postJson('/profile/storesurvey', [
            'diet' => 'vegan', 'groceries' => [['id' => 1, 'name' => 'Rice', 'unit_price' => 2, 'quantity' => 5, 'total_price' => 10]],
            'basketCost' => 10, 'lunch' => 20, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'totalCost' => 30,
        ])->assertOk()
            ->assertJsonPath('profile.type', 'saver')
            ->assertJsonPath('profile.title', 'The Smart Saver 🐷')
            ->assertJsonPath('starts_next_month', false);

        $this->actingAs($student)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('The Smart Saver')
            ->assertSee('Needs');
    }

    public function test_activation_uses_the_same_survey_with_a_quiet_skip(): void
    {
        // A student at activation step 5 (bank account, salary and auto-debit done).
        $student = $this->student();
        BankAccount::where('student_id', $student->id)->update(['primary_savings_account_amount' => 500]);
        \App\Models\AutoDebitRequest::create(['user_id' => $student->id, 'sid' => '1', 'type' => 'school']);

        $this->actingAs($student)->get(route('CitizenActivation.consumerProfile'))
            ->assertOk()
            ->assertSee('Your first month in Zedville')
            ->assertSee(route('CitizenActivation.consumerProfile.skip'), false)
            ->assertSee('activation-complete', false);   // where its last screen continues
    }
}
