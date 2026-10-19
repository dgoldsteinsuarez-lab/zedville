<?php

namespace Tests\Feature;

use App\Models\ConsumerSurvey;
use App\Models\FinheroTask6StudentPick;
use App\Models\Supermarket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Substitute Goods feature (2026-10-19): swap one basket item for another
 * from the same diet catalog, without redoing the full Consumer Profile
 * survey. Also the mechanism FinHero Task 6 is built on.
 *
 * Task 6's "blocked" items are picked automatically by the system from
 * the student's OWN basket - no admin/tutor action beyond activating the
 * task (Daniel, 2026-10-19: "no quiero que el admin o el tutor hagan
 * nada... que tomes los productos de cada alumno"). Tests pre-seed a
 * FinheroTask6StudentPick row to simulate "this was already auto-picked
 * this month," since BasketController::blockedItemIdsFor() only
 * generates fresh picks when none exist yet for the student this month -
 * this keeps the tests deterministic instead of depending on the random
 * pick.
 */
class BasketSwapTest extends TestCase
{
    use RefreshDatabase;

    private function makeDietItem(string $diet, string $name, float $price): Supermarket
    {
        return Supermarket::create([
            'product_name' => $name,
            'type' => $diet,
            'category' => 'Needs',
            'price' => $price,
            'quantity' => 1,
        ]);
    }

    private function makeStudentWithBasket(array $items, string $diet = 'omnivore'): array
    {
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => null]);

        $groceries = array_map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->product_name,
            'unit_price' => (float) $item->price,
            'quantity' => 1,
            'total_price' => (float) $item->price,
        ], $items);

        $survey = ConsumerSurvey::create([
            'user_id' => $student->id,
            'sid' => 1,
            'diet' => $diet,
            'groceries' => $groceries,
            'basket_cost' => collect($groceries)->sum('total_price'),
            'lunch' => 10,
            'transport' => 5,
            'activities' => 0,
            'restaurants' => 0,
            'total_cost' => collect($groceries)->sum('total_price') + 15,
        ]);

        return [$student, $survey];
    }

    private function activateTask6(): void
    {
        DB::table('finhero_activity_registry')->insert([
            'sid' => 1, 'cid' => 99, 'type' => 'task', 'activity_key' => 'task_6',
            'activity_name' => 'Task 6', 'max_points' => 5, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function seedPick(User $student, Supermarket $item): void
    {
        FinheroTask6StudentPick::create([
            'student_id' => $student->id,
            'supermarket_id' => $item->id,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    public function test_a_student_can_swap_a_basket_item_for_a_same_diet_substitute(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $tofu = $this->makeDietItem('omnivore', 'Tofu', 6);
        [$student, $survey] = $this->makeStudentWithBasket([$chicken]);

        $response = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $chicken->id,
            'new_item_id' => $tofu->id,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true, 'basket_cost' => 6.0]);

        $survey->refresh();
        $this->assertSame('Tofu', $survey->groceries[0]['name']);
        $this->assertEquals(6.0, $survey->basket_cost);
        $this->assertEquals(21.0, $survey->total_cost); // 6 + lunch(10) + transport(5)
    }

    public function test_cannot_swap_into_an_item_already_in_the_basket(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $beans = $this->makeDietItem('omnivore', 'Beans', 4);
        [$student] = $this->makeStudentWithBasket([$chicken, $beans]);

        $response = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $chicken->id,
            'new_item_id' => $beans->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_swap_into_an_item_from_a_different_diet(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $veganItem = $this->makeDietItem('vegan', 'Lentils', 5);
        [$student] = $this->makeStudentWithBasket([$chicken]);

        $response = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $chicken->id,
            'new_item_id' => $veganItem->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_swap_an_item_not_actually_in_the_basket(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $tofu = $this->makeDietItem('omnivore', 'Tofu', 6);
        $notInBasket = $this->makeDietItem('omnivore', 'Eggs', 3);
        [$student] = $this->makeStudentWithBasket([$chicken]);

        $response = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $notInBasket->id,
            'new_item_id' => $tofu->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_task_6_auto_picks_items_from_the_students_own_basket(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $beans = $this->makeDietItem('omnivore', 'Beans', 4);
        [$student] = $this->makeStudentWithBasket([$chicken, $beans]);
        $student->update(['grade' => 99]);
        $this->activateTask6();

        $response = $this->actingAs($student)->getJson(route('basket.state'));

        $response->assertOk();
        $blockedIds = collect($response->json('items'))->where('blocked', true)->pluck('id');
        // Every auto-picked id must come from the student's own basket -
        // never an item they never had.
        $this->assertTrue($blockedIds->every(fn ($id) => in_array($id, [$chicken->id, $beans->id])));
        $this->assertTrue($blockedIds->isNotEmpty());

        // Idempotent: a second call doesn't re-pick/change the selection.
        $again = $this->actingAs($student)->getJson(route('basket.state'));
        $blockedIdsAgain = collect($again->json('items'))->where('blocked', true)->pluck('id');
        $this->assertEquals($blockedIds->sort()->values(), $blockedIdsAgain->sort()->values());
    }

    public function test_swapping_out_the_last_blocked_item_completes_task_6_and_awards_points_once(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $tofu = $this->makeDietItem('omnivore', 'Tofu', 6);
        $eggs = $this->makeDietItem('omnivore', 'Eggs', 3);
        [$student, $survey] = $this->makeStudentWithBasket([$chicken]);
        $student->update(['grade' => 99]);
        $this->activateTask6();
        $this->seedPick($student, $chicken);

        $response = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $chicken->id,
            'new_item_id' => $tofu->id,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true, 'task6_completed' => true]);
        $this->assertDatabaseHas('finhero_student_points', [
            'student_id' => $student->id,
            'source_type' => 'task',
            'source_key' => 'task_6',
            'points_earned' => 5,
        ]);

        // Swapping again (a different, non-blocked item) must not re-award.
        $survey->refresh();
        $response2 = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $tofu->id,
            'new_item_id' => $eggs->id,
        ]);
        $response2->assertOk();
        $response2->assertJson(['task6_completed' => false]);
        $this->assertSame(1, DB::table('finhero_student_points')->where('student_id', $student->id)->where('source_key', 'task_6')->count());
    }

    public function test_an_already_swapped_out_blocked_item_cannot_be_chosen_again(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $tofu = $this->makeDietItem('omnivore', 'Tofu', 6);
        $beans = $this->makeDietItem('omnivore', 'Beans', 4);
        [$student] = $this->makeStudentWithBasket([$chicken, $tofu]);
        $student->update(['grade' => 99]);
        $this->activateTask6();
        // "Beans" was auto-picked as blocked earlier this month, even
        // though it's no longer in the basket (already swapped out once).
        $this->seedPick($student, $beans);

        $response = $this->actingAs($student)->postJson(route('basket.swap'), [
            'old_item_id' => $chicken->id,
            'new_item_id' => $beans->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_options_endpoint_excludes_basket_items_and_still_blocked_items(): void
    {
        $chicken = $this->makeDietItem('omnivore', 'Chicken', 10);
        $tofu = $this->makeDietItem('omnivore', 'Tofu', 6);
        $beans = $this->makeDietItem('omnivore', 'Beans', 4);
        [$student] = $this->makeStudentWithBasket([$chicken]);
        $student->update(['grade' => 99]);
        $this->activateTask6();
        $this->seedPick($student, $beans); // blocked, not in basket

        $response = $this->actingAs($student)->getJson(route('basket.options'));

        $response->assertOk();
        $ids = collect($response->json('items'))->pluck('id');
        $this->assertTrue($ids->contains($tofu->id));
        $this->assertFalse($ids->contains($chicken->id)); // already in basket
        $this->assertFalse($ids->contains($beans->id)); // blocked
    }
}
