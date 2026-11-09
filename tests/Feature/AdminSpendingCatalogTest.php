<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Models\WantsPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Spending Activity in Admin (2026-10-31): the shopping lists and the plans of the month - Super Admin only. */
class AdminSpendingCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    public function test_the_pages_show_the_lists_and_the_plans_in_the_menu(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.spending.lists'))->assertOk()
            ->assertSee('Spending Activity: shopping lists')->assertSee('Birthday Party Budget')->assertSee('Budget Ƶ 65.00')
            ->assertSee('list costs Ƶ 21.38–86.98 · suggested budget Ƶ 65')
            ->assertSee('3 proteins')->assertSee('Shopping lists')->assertSee('Spending 4');
        $this->actingAs($admin)->get(route('admin.spending.plans'))->assertOk()
            ->assertSee('Spending 4: the plan of the month')->assertSee('Cinema month')->assertSee('ES: Mes de cine')
            ->assertSee('Long weekend in Smith City')->assertSee('Luxury staycation in Zedville')->assertSee('Classes')
            ->assertSee('Cinema afternoons');   // the Intermediate's own plans (2026-11-08)
        // each plan's price level within its kind (1-4): 40 Low, 12 High, 12 Intermediate
        $levels = $this->actingAs($admin)->get(route('admin.spending.plans'))->viewData('levels');
        $this->assertSame(64, count($levels));
        $this->assertEqualsCanonicalizing([1, 2, 3, 4], array_values(array_unique($levels)));

        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);
        $this->actingAs($schoolAdmin)->get(route('admin.spending.lists'))->assertForbidden();
        $this->actingAs($schoolAdmin)->get(route('admin.spending.plans'))->assertForbidden();
    }

    public function test_a_new_activity_with_its_list_and_a_list_from_two_shops_is_refused(): void
    {
        $admin = $this->admin();
        $form = ['type' => 'low', 'title' => 'Picnic in the Park', 'mission' => 'A picnic for four friends.', 'destination' => 'A Friend', 'budget' => 30,
            'translations' => ['es' => ['title' => 'Pícnic en el parque']]];

        $this->actingAs($admin)->from(route('admin.spending.lists'))->post(route('admin.spending.lists.store'), $form + ['lines' => [
            ['qty' => 1, 'kind' => 'products', 'products' => ['Desk Lamp', 'Cushion']],
        ]])->assertSessionHasErrors('lines.0.products');

        $this->actingAs($admin)->post(route('admin.spending.lists.store'), $form + ['lines' => [
            ['qty' => 2, 'kind' => 'group', 'group' => 'fruit'],
            ['qty' => 1, 'kind' => 'products', 'products' => ['Cushion', 'Throw Blanket']],
        ]])->assertRedirect(route('admin.spending.lists'));

        $activity = Activity::where('title', 'Picnic in the Park')->first();
        $this->assertSame([['qty' => 2, 'group' => 'fruit'], ['qty' => 1, 'products' => ['Cushion', 'Throw Blanket']]], $activity->shopping_list);
        // a food list's budget leaves room for what is chosen: Ƶ 30 is raised
        $range = \App\Services\ShoppingList::range($activity->shopping_list);
        $this->assertSame(\App\Services\ShoppingList::suggestedBudget($activity->shopping_list), (float) $activity->budget);
        $this->assertGreaterThan(30.0, (float) $activity->budget);
        $this->assertLessThan($range['max'], (float) $activity->budget);
        $this->assertSame('Supermarket,Comfort Zone', $activity->shop);
        $this->assertSame(['low', 13], [$activity->type, $activity->number]); // numbered within Low Spending
        app()->setLocale('es');
        $this->assertSame('Pícnic en el parque', $activity->text('title'));
    }

    public function test_a_plan_is_added_with_its_translations_and_switched_off(): void
    {
        $admin = $this->admin();

        // a High Spending 4 plan must be more than 20% of the salary on its own
        $this->actingAs($admin)->from(route('admin.spending.plans'))->post(route('admin.spending.plans.store'), [
            'kind' => 'high', 'name' => 'Weekend away', 'description' => 'A short trip.', 'cost' => 300, 'times_per_month' => 1,
        ])->assertSessionHasErrors('cost');

        $this->actingAs($admin)->post(route('admin.spending.plans.store'), [
            'kind' => 'low', 'name' => 'Escape Room: Liquidity Crisis', 'description' => 'An escape room with friends: Ƶ 25 each.', 'cost' => 25, 'times_per_month' => 1,
            'translations' => ['es' => ['name' => 'Sala de escape: crisis de liquidez', 'description' => 'Una sala de escape con amigos: Ƶ 25 cada uno.']],
        ])->assertRedirect(route('admin.spending.plans'));

        $plan = WantsPlan::where('name', 'Escape Room: Liquidity Crisis')->first();
        $this->assertTrue($plan->is_active);
        app()->setLocale('es');
        $this->assertSame('Sala de escape: crisis de liquidez', $plan->text('name'));

        $this->actingAs($admin)->post(route('admin.spending.plans.toggle', $plan));
        $this->assertFalse($plan->fresh()->is_active);
    }

    /** Daniel, 2026-10-31: Low Spending 4 next to a Low Spending activity (21% together), High on its own (e.g. Intermediate). */
    public function test_each_class_gets_low_or_high_spending_4_with_its_check(): void
    {
        $admin = $this->admin();
        $level = \App\Models\GradeLevel::where('name', 'Intermediate')->first() ?? \App\Models\GradeLevel::create(['name' => 'Intermediate', 'sort_order' => 2]);
        $grade = \App\Models\Grade::create(['sid' => '1', 'name' => 'Grade 10', 'grade_level_id' => $level->id]);
        $class = \App\Models\SchoolClass::create(['sid' => '1', 'name' => '10A', 'grade_id' => $grade->id]);
        $other = \App\Models\SchoolClass::create(['sid' => '1', 'name' => '8B']);
        \Illuminate\Support\Facades\DB::table('mba_position')->insert(['user_id' => $admin->id, 'activity_key' => 'lba_position', 'sid' => '1', 'cid' => $other->id,
            'activity_id' => Activity::where('title', 'Cosy Winter')->value('id'), 'required' => 1, 'optional' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->get(route('admin.spending.plans'))->assertOk()
            ->assertSeeInOrder(['8B', 'Low Spending 4', 'Cosy Winter', '✓'], false)
            ->assertSee('none assigned');

        // every class at Intermediate gets High Spending 4
        $this->actingAs($admin)->post(route('admin.spending.level-kind'), ['grade_level_id' => $level->id, 'spending4' => 'high'])->assertRedirect();
        $this->assertSame('high', $class->fresh()->spending4);
        $this->assertSame('low', $other->fresh()->spending4);

        $this->actingAs($admin)->post(route('admin.spending.class-kind', $other), ['spending4' => 'high'])->assertRedirect();
        $this->assertSame('high', $other->fresh()->spending4);
    }
}
