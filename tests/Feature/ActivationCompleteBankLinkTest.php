<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: "I can not go to bank account by pressing the green
 * tap" on the activation-complete page. The "Go to My Bank Account" button
 * was a plain href="#" placeholder that was never wired up - it never
 * pointed anywhere. Fixed to route('bank.index').
 */
class ActivationCompleteBankLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_go_to_bank_account_button_links_to_the_real_bank_route(): void
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
        ConsumerSurvey::create(['user_id' => $student->id]);

        $response = $this->actingAs($student)->get(route('CitizenActivation.activationComplete'));

        $response->assertOk();
        $response->assertSee('href="' . route('bank.index') . '"', false);
    }
}
