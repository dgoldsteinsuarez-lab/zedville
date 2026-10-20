<?php

namespace Tests\Feature\Admin;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel: "are you sure you made the change, i should see all info" - the
 * Card Number column on the City Bank Account admin screen masked every
 * row down to its last 4 digits ("xxxxxxxxxxxx4321"), which he didn't
 * want. This is a dummy-finance teaching platform with synthetic card
 * numbers, not real payment data - the admin view now shows the full
 * number, matching how the Primary Savings Account column already did
 * (bugs.md, 2026-10-20).
 */
class CityBankAccountsCardNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_full_card_number_is_shown_not_masked(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = User::factory()->create(['role' => 4]);
        BankAccount::create([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'card_number' => '2000123456789012',
            'card_pin' => '1234',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.accounts'));

        $response->assertOk();
        $response->assertSee('2000123456789012');
        $response->assertDontSee('xxxxxxxxxxxx');
    }
}
