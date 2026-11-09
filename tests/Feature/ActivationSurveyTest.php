<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-23: "I can not do the survey". A student still in the
 * Citizen Activation wizard got redirected on the survey's grocery-list
 * request (/api/groceries/{diet}) - the list came back as the wizard page,
 * stayed empty, and the survey could never be finished.
 */
class ActivationSurveyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // TestCase switches the activation gate off for most tests; this one is about it.
        $this->withMiddleware(\App\Http\Middleware\EnsureCitizenActivation::class);
    }

    private function studentMidActivation(): User
    {
        $student = User::factory()->create(['role' => 4, 'sid' => '1']);
        // Bank account opened, but the rest of the wizard not done yet.
        BankAccount::create([
            'student_id' => $student->id, 'sid' => '1', 'student_name' => $student->name,
            'student_email' => $student->email, 'card_pin' => '1234',
            'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);

        return $student;
    }

    public function test_the_survey_grocery_list_loads_during_activation(): void
    {
        DB::table('supermarkets')->insert([
            'product_name' => 'Cooking Oil', 'type' => 'omnivore', 'category' => 'Needs', 'price' => 3,
            'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $student = $this->studentMidActivation();

        $this->actingAs($student)->get('/api/groceries/omnivore')
            ->assertOk()
            ->assertJsonFragment(['product_name' => 'Cooking Oil']);
    }

    public function test_pages_still_send_an_unfinished_student_back_to_the_wizard(): void
    {
        $student = $this->studentMidActivation();

        $this->actingAs($student)->get(route('education.city'))
            ->assertRedirect(route('CitizenActivation.salaryAuthorization'));
    }
}
