<?php

namespace Tests\Feature\Engagement;

use App\Models\BankAccount;
use App\Models\Referendum;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Of the spec's 10 Participation actions, only transfer/donation/
 * emergency_fund_open/money_market_open/poster_room_enter were actually
 * wired up live. This covers the remaining 5 being wired this pass
 * (bugs.md, 2026-10-19): voting, building_enter (x4 slugs), shopping,
 * street_sign_press, closet_enter.
 */
class ParticipationWiringTest extends TestCase
{
    use RefreshDatabase;

    private function assertAwarded(User $student, string $actionType, ?string $detail = null): void
    {
        $query = DB::table('participation_logs')
            ->where('student_id', $student->id)
            ->where('action_detail', $actionType);

        if ($detail !== null) {
            $query->where('action_ref', $detail);
        }

        $this->assertTrue($query->exists(), "Expected a participation_logs row for {$actionType}");
    }

    public function test_voting_awards_a_participation_point(): void
    {
        $student = User::factory()->create(['role' => 4, 'sid' => null, 'grade' => null]);
        $referendum = Referendum::create([
            'question' => 'Test?',
            'status' => 'open',
            'sid' => null,
            'class_id' => null,
            'grade_id' => null,
            'created_by' => $student->id,
        ]);

        $this->actingAs($student)
            ->post("/education/referendum/{$referendum->id}/vote", ['vote' => 'yes'])
            ->assertOk();

        $this->assertAwarded($student, 'voting');
    }

    public function test_entering_city_hall_awards_building_enter(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/education/city-hall')->assertOk();

        $this->assertAwarded($student, 'building_enter', 'city_hall');
    }

    public function test_entering_city_mall_awards_building_enter(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/education/city-mall')->assertOk();

        $this->assertAwarded($student, 'building_enter', 'city_mall');
    }

    public function test_entering_npos_awards_building_enter(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/education/npos')->assertOk();

        $this->assertAwarded($student, 'building_enter', 'npo');
    }

    public function test_entering_supermarket_awards_building_enter(): void
    {
        $student = User::factory()->create(['role' => 4]);

        // The point is awarded before the controller's monthly-survey
        // redirect check, so this fires regardless of survey state.
        $this->actingAs($student)->get('/supermarket/supermarket');

        $this->assertAwarded($student, 'building_enter', 'supermarket');
    }

    public function test_entering_closet_awards_closet_enter(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/closet')->assertOk();

        $this->assertAwarded($student, 'closet_enter');
    }

    public function test_street_sign_press_endpoint_awards_a_point(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)
            ->postJson('/participation/award', ['action' => 'street_sign_press', 'detail' => '7'])
            ->assertOk()
            ->assertJson(['success' => true, 'awarded' => true]);

        $this->assertAwarded($student, 'street_sign_press', '7');
    }

    public function test_placing_an_order_awards_shopping(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 3001]);

        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 3001,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'card_pin' => '1234',
            'is_open_emergency_account' => 0,
            'is_open_money_market_account' => 0,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 3001,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 100,
            'balance' => 100,
            'is_penalty' => false,
        ]);

        $productId = DB::table('products')->insertGetId([
            'product_name' => 'Test Widget',
            'type' => 'good',
            'category' => 'Needs',
            'price' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234',
            'cart' => [['id' => $productId, 'quantity' => 1]],
        ])->assertOk();

        $this->assertAwarded($user, 'shopping', 'products');
    }

    public function test_street_sign_press_endpoint_rejects_unknown_actions(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)
            ->postJson('/participation/award', ['action' => 'donation', 'detail' => 'free-points'])
            ->assertStatus(422);
    }
}
