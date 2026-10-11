<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for ConsumerSurveyController::storesurvey() - written
 * while extracting it (and consumerProfileSurvey()) out of
 * ProfileController (planning.md Phase 3 #4). Previously uncovered.
 * The two per-item Transaction1::create(...) calls were also migrated
 * onto LedgerService as part of the move (both already used $user->sid
 * correctly, so this is pure consolidation, not a bug fix).
 */
class StoreSurveyTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithFunds(float $balance): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 9601]);

        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 9601,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 9601,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => $balance,
            'balance' => $balance,
            'is_penalty' => false,
        ]);

        return $user;
    }

    public function test_submitting_a_survey_debits_groceries_and_other_expenses(): void
    {
        $user = $this->makeStudentWithFunds(200);

        $response = $this->actingAs($user)->postJson('/profile/storesurvey', [
            'diet' => 'omnivore',
            'groceries' => [['name' => 'Milk', 'total_price' => 20]],
            'basketCost' => 20,
            'lunch' => 10,
            'transport' => 5,
            'activities' => 0,
            'restaurants' => 0,
            'totalCost' => 35,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('transactions1', [
            'user_id' => $user->id,
            'category' => 'Needs',
            'amount' => 20,
            'sid' => 9601,
        ]);
        $this->assertDatabaseHas('transactions1', [
            'user_id' => $user->id,
            'description' => 'School Lunch',
            'amount' => 10,
        ]);
        $this->assertDatabaseHas('transactions1', [
            'user_id' => $user->id,
            'description' => 'Transportation',
            'amount' => 5,
        ]);

        $finalBalance = Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
        $this->assertEquals(165.0, (float) $finalBalance); // 200 - 20 - 10 - 5
    }

    public function test_submitting_a_survey_rejects_insufficient_balance(): void
    {
        $user = $this->makeStudentWithFunds(10);

        $response = $this->actingAs($user)->postJson('/profile/storesurvey', [
            'diet' => 'omnivore',
            'groceries' => [['name' => 'Milk', 'total_price' => 20]],
            'basketCost' => 20,
            'lunch' => 0,
            'transport' => 0,
            'activities' => 0,
            'restaurants' => 0,
            'totalCost' => 20,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['status' => false]);

        $this->assertDatabaseMissing('transactions1', [
            'user_id' => $user->id,
            'category' => 'Needs',
        ]);
    }
}
