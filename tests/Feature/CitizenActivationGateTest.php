<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: logged in as a brand-new student and could still use
 * the sidebar (City Mailbox, City, EFD, PISA, Calendar, ...) without
 * finishing the onboarding wizard (Bank Account -> Salary Authorization ->
 * Auto Debit -> Consumer Profile). Root cause: CitizenActivationService::
 * getNextRoute() was only ever checked once, at login - nothing re-checked
 * it on later requests. Fixed with EnsureCitizenActivation, a global 'web'
 * middleware that re-checks on every request for a student (role 4).
 */
class CitizenActivationGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Base TestCase disables this middleware by default for every other
        // test suite - re-enable it here since this is the one testing it.
        $this->withMiddleware(\App\Http\Middleware\EnsureCitizenActivation::class);
    }

    public function test_a_student_with_no_bank_account_is_redirected_away_from_the_dashboard(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertRedirect(route('CitizenActivation.bankAccount'));
    }

    public function test_the_wizard_itself_stays_reachable_mid_onboarding(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('CitizenActivation.bankAccount'));

        $response->assertOk();
    }

    public function test_a_student_is_redirected_to_the_next_incomplete_step_not_the_first(): void
    {
        $student = User::factory()->create(['role' => 4]);
        BankAccount::create([
            'student_id' => $student->id,
            'sid' => $student->sid,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'primary_savings_account_amount' => 100,
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertRedirect(route('CitizenActivation.autoDebit'));
    }

    public function test_a_fully_onboarded_student_reaches_the_dashboard_normally(): void
    {
        $student = User::factory()->create(['role' => 4]);
        BankAccount::create([
            'student_id' => $student->id,
            'sid' => $student->sid,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'primary_savings_account_amount' => 100,
        ]);
        AutoDebitRequest::create([
            'user_id' => $student->id,
            'sid' => $student->sid,
            'type' => 'Rent',
        ]);
        ConsumerSurvey::create([
            'user_id' => $student->id,
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertOk();
    }

    public function test_admins_and_tutors_are_unaffected(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }
}
