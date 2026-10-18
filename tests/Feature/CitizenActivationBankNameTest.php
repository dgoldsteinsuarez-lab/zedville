<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: a student already typed their real name at
 * registration - asking again on the Bank Account application just wastes
 * time and risks the bank account ending up with a different name than
 * the account itself. The "Full Name" field is now read-only, always the
 * registered name; a typo gets fixed in Settings (profile.update), not
 * re-typed here. Server-side also no longer trusts a submitted name, so a
 * tampered request can't create a mismatched bank account either.
 */
class CitizenActivationBankNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_account_always_uses_the_students_registered_name(): void
    {
        $student = User::factory()->create(['role' => 4, 'name' => 'Esperanza Gutierrez']);

        $response = $this->actingAs($student)->post(route('CitizenActivation.store'), [
            'email' => 'esperanza@example.com',
            'homeAddress' => '123 Main St',
        ]);

        $response->assertRedirect(route('CitizenActivation.salaryAuthorization'));
        $this->assertDatabaseHas('bank_accounts', [
            'student_id' => $student->id,
            'student_name' => 'Esperanza Gutierrez',
        ]);
    }

    public function test_a_tampered_fullname_field_is_ignored(): void
    {
        $student = User::factory()->create(['role' => 4, 'name' => 'Esperanza Gutierrez']);

        $this->actingAs($student)->post(route('CitizenActivation.store'), [
            'fullName' => 'Someone Else Entirely',
            'email' => 'esperanza2@example.com',
            'homeAddress' => '123 Main St',
        ]);

        $this->assertDatabaseHas('bank_accounts', [
            'student_id' => $student->id,
            'student_name' => 'Esperanza Gutierrez',
        ]);
        $this->assertDatabaseMissing('bank_accounts', [
            'student_name' => 'Someone Else Entirely',
        ]);
    }
}
