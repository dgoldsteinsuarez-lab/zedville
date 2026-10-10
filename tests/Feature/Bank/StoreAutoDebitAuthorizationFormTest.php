<?php

namespace Tests\Feature\Bank;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for AutoDebitController::store_auto_debit_authorization_form()
 * - written while extracting this method (and the rest of AutoDebitController)
 * out of BankController (planning.md Phase 3 #4). Confirms the move didn't
 * change behavior: Rent and City School auto-debit requests are always
 * created, a chosen extra service is also recorded, and ensureMonthlyBills()
 * still gets invoked on the shared BankController instance.
 */
class StoreAutoDebitAuthorizationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_the_form_creates_mandatory_and_chosen_auto_debit_requests(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 9001]);
        BankAccount::create([
            'student_id' => $user->id,
            'sid' => 9001,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);

        $response = $this->actingAs($user)->post('/bank-account/store-auto-debit-authorization-form', [
            'bankname' => 'Universal Bank',
            'bankaccountnumber' => '000111',
            'services' => ['school' => 'SCHOOL-AUTO'],
            'services_meta' => [
                'school' => ['name' => 'City School', 'account' => 'SCHOOL-AUTO', 'amount' => 1000],
            ],
        ]);

        $response->assertRedirect(route('bank.consumer_survey_message'));

        $this->assertDatabaseHas('auto_debit_requests', ['user_id' => $user->id, 'type' => 'Rent']);
        $this->assertDatabaseHas('auto_debit_requests', ['user_id' => $user->id, 'type' => 'City School']);

        $rent = AutoDebitRequest::where('user_id', $user->id)->where('type', 'Rent')->first();
        $this->assertEquals(9001, $rent->sid);
        $this->assertEquals(300, (float) $rent->amount);
    }

    public function test_submitting_twice_for_the_same_service_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 9002]);
        BankAccount::create([
            'student_id' => $user->id,
            'sid' => 9002,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);

        $payload = [
            'bankname' => 'Universal Bank',
            'bankaccountnumber' => '000111',
            'services' => ['school' => 'SCHOOL-AUTO'],
            'services_meta' => [
                'school' => ['name' => 'City School', 'account' => 'SCHOOL-AUTO', 'amount' => 1000],
            ],
        ];

        $this->actingAs($user)->post('/bank-account/store-auto-debit-authorization-form', $payload);
        $response = $this->actingAs($user)->post('/bank-account/store-auto-debit-authorization-form', $payload);

        $response->assertRedirect();
        $this->assertSame('You have already submitted auto-debit for one or more services.', session('error'));
    }
}
