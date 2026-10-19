<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: hit a 500 on the Auto Debit wizard step -
 * "SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'fullname'
 * cannot be null". Root cause: CitizenActivationController::
 * store_auto_debit_authorization_form() wrote 'fullname'/'signature' from
 * $user->nickname, but an admin-staged student (AdminStudentController::
 * add_student()) can log in directly - without ever visiting /register to
 * claim a nickname (see bugs.md, 2026-10-19, the staged-account flow) -
 * leaving nickname null and crashing this insert outright. Also wrong on
 * its own terms: 'fullname'/'signature' on a legal authorization document
 * should be the real name, not the casual nickname, same reasoning as the
 * Bank Account "Full Name" fix. Same bug fixed in AutoDebitController and
 * CitizenActivationService (Tutor auto-provisioning).
 */
class CitizenActivationAutoDebitNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_with_no_nickname_can_still_submit_auto_debit(): void
    {
        $student = User::factory()->create(['role' => 4, 'name' => 'Esperanza Gutierrez', 'nickname' => null]);

        $response = $this->actingAs($student)->post(route('CitizenActivation.store-auto-debit-authorization-form'), [
            'services' => ['Utility - Electricity' => '12345'],
            'services_meta' => [
                'Utility - Electricity' => ['name' => 'Utility - Electricity', 'account' => '12345', 'amount' => 50],
            ],
            'bankaccountnumber' => '123000-000000',
            'bankname' => 'Universal Bank',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('auto_debit_requests', [
            'user_id' => $student->id,
            'type' => 'Utility - Electricity',
            'fullname' => 'Esperanza Gutierrez',
            'signature' => 'Esperanza Gutierrez',
        ]);
        $this->assertDatabaseHas('auto_debit_requests', [
            'user_id' => $student->id,
            'type' => 'Rent',
            'fullname' => 'Esperanza Gutierrez',
        ]);
    }
}
