<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-31: "el alumno al final va a cliquear sin mirar hasta llegar
 * a la cantidad mínima. Me gustaría que la cantidad sea menos y más
 * selectiva, por ejemplo, en supermercado, buscar 3 de proteínas, carbos,
 * etc." - a Spending Activity is a short shopping list with a budget.
 */
class SpendingShoppingListTest extends TestCase
{
    use RefreshDatabase;

    private function studentWith(string $title): array
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => 2000, 'balance' => 2000, 'is_penalty' => false]);
        $activity = Activity::where('title', $title)->firstOrFail();
        [$slotKey, $slotTitle] = $activity->slot();
        DB::table('mba_position')->insert(['user_id' => $user->id, 'activity_key' => $slotKey, 'sid' => '1', 'cid' => $class->id,
            'activity_id' => $activity->id, 'required' => 1, 'optional' => 0, 'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addWeek()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('calendar_events')->insert(['sid' => '1', 'classId' => $class->id, 'title' => $slotTitle, 'position' => 'required',
            'repeatActivity' => '0', 'start' => now()->subDays(2), 'end' => now()->addWeek(), 'created_at' => now(), 'updated_at' => now()]);

        return [$user, $activity];
    }

    private function ids(string $table, string $type, array $names): array
    {
        return collect($names)->map(fn ($name) => ['id' => DB::table($table)->where('type', $type)->where('product_name', $name)->value('id'), 'quantity' => 1])->all();
    }

    private function pay(User $user, Activity $activity, string $catalog, array $cart)
    {
        return $this->actingAs($user)->postJson('/order/placeActivity', ['pin' => '1234', 'catalog' => $catalog, 'cart' => $cart, 'activity_id' => $activity->id]);
    }

    public function test_every_supermarket_product_has_a_food_group_and_every_activity_a_list(): void
    {
        $this->assertSame(0, DB::table('supermarkets')->whereNull('food_group')->count());
        $this->assertSame('protein', DB::table('supermarkets')->where('product_name', 'Lentils')->value('food_group'));
        $this->assertSame('carbs', DB::table('supermarkets')->where('product_name', 'Potatoes')->value('food_group'));
        $this->assertSame(10, DB::table('products')->where('type', 'Comfort Zone')->count());   // the shop was empty
        foreach (Activity::all() as $activity) {
            $this->assertNotEmpty($activity->shopping_list, $activity->title);
            $this->assertGreaterThan(0, $activity->budget, $activity->title);
        }
    }

    public function test_the_birthday_party_takes_exactly_its_food_groups_within_the_budget(): void
    {
        [$user, $activity] = $this->studentWith('Birthday Party Budget');

        $this->actingAs($user)->get(route('spendingactivities.supermarket', ['type' => 'omnivore', 'activity_id' => $activity->id]))
            ->assertRedirect(); // no survey yet: the market list first - the list itself is checked below

        // something off the list, half the list, over the budget
        $this->pay($user, $activity, 'supermarkets', $this->ids('supermarkets', 'omnivore', ['Cooking Oil']))
            ->assertStatus(422)->assertJson(['message' => 'Cooking Oil is not on your list.']);
        $this->pay($user, $activity, 'supermarkets', $this->ids('supermarkets', 'omnivore', ['Eggs', 'Rice']))
            ->assertStatus(422)->assertJsonFragment(['message' => 'Still missing from this shop: 2 proteins; 1 carbohydrate; 2 fruits; 1 dairy product']);
        // two of the same count twice (Daniel, 2026-11-02: "puede poner dos veces el mismo tipo de fruta")
        $this->pay($user, $activity, 'supermarkets', [['id' => DB::table('supermarkets')->where('product_name', 'Eggs')->where('type', 'omnivore')->value('id'), 'quantity' => 2]])
            ->assertStatus(422)->assertJsonFragment(['message' => 'Still missing from this shop: 1 protein; 2 carbohydrates; 2 fruits; 1 dairy product']);
        // the food list's budget leaves room (55 before): from Ƶ 21.38 to Ƶ 86.98, 60 % of the way, rounded up
        $this->assertSame(65.0, (float) $activity->fresh()->budget);
        $this->pay($user, $activity, 'supermarkets', $this->ids('supermarkets', 'omnivore',
            ['Seafood', 'Fish', 'Beef', 'Rice', 'Pasta', 'Mixed National Fruits', 'Mixed International Fruits', 'Cheese']))
            ->assertStatus(422)->assertJson(['message' => 'That is over the budget: Ƶ 67.23 of Ƶ 65.00.']);
        $this->assertSame(0, DB::table('orders')->count());

        // Daniel's party basket (Ƶ 56.51, over the old Ƶ 55): fish, seafood, two fruits the same
        $fruit = DB::table('supermarkets')->where('product_name', 'Mixed National Fruits')->where('type', 'pescatarian')->value('id');
        $this->pay($user, $activity, 'supermarkets', array_merge($this->ids('supermarkets', 'pescatarian',
            ['Fish Burger', 'Seafood', 'Fish', 'Rice', 'Pasta', 'Yogurt']), [['id' => $fruit, 'quantity' => 2]]))
            ->assertOk()->assertJson(['status' => true, 'list_complete' => true, 'list_message' => 'Your list is complete. Well done!']);

        $order = DB::table('orders')->first();
        $this->assertSame($activity->id, (int) $order->activity_id);
        $this->assertSame(8, (int) DB::table('order_items')->where('order_id', $order->id)->where('catalog', 'supermarkets')->whereNotNull('product_id')->sum('qty'));
        $this->assertDatabaseHas('activity_completions', ['student_id' => $user->id, 'activity_type' => 'low_budget_activity']);

        // done: nothing more for this activity
        $this->pay($user, $activity, 'supermarkets', $this->ids('supermarkets', 'omnivore', ['Chicken']))
            ->assertStatus(422)->assertJson(['message' => 'Your list is complete. Well done!']);
    }

    public function test_a_list_over_two_shops_is_finished_in_the_second(): void
    {
        [$user, $activity] = $this->studentWith('Closet Restock'); // The Basics Co. + BeU - BeLuxury

        $this->pay($user, $activity, 'products', $this->ids('products', 'The Basics Co.', ['Shirt', 'Trousers', 'Shoes']))
            ->assertOk()->assertJson(['list_complete' => false, 'list_message' => 'Done here! Now go to BeU - BeLuxury for the rest of your list.']);
        $this->assertDatabaseMissing('activity_completions', ['student_id' => $user->id, 'activity_type' => 'low_budget_activity']);

        // the budget counts what was already spent: Ƶ 28 + a Ƶ 120 sweater is over Ƶ 80
        $this->pay($user, $activity, 'products', $this->ids('products', 'BeU - BeLuxury', ['Nice Sweater']))
            ->assertStatus(422)->assertJson(['message' => 'That is over the budget: Ƶ 148.00 of Ƶ 80.00.']);
        $this->pay($user, $activity, 'products', $this->ids('products', 'BeU - BeLuxury', ['Premium Socks']))
            ->assertOk()->assertJson(['list_complete' => true]);
        $this->assertDatabaseHas('activity_completions', ['student_id' => $user->id, 'activity_type' => 'low_budget_activity']);
    }

    public function test_the_shop_page_and_the_task_show_the_list(): void
    {
        [$user, $activity] = $this->studentWith('Digital Upgrade'); // High Spending, Tech Hub

        $page = $this->actingAs($user)->get('/spending-activities/spending-tracker/tech-hub?activity_id=' . $activity->id)->assertOk();
        $page->assertSee('window.ZV_LIST', false)->assertSee('id="zvList"', false)
            ->assertSee('1 × Bluetooth Headphones')->assertSee('Your shopping list');

        $this->actingAs($user)->get('/education/high-spending-activities')->assertOk()
            ->assertSee('1 × Bluetooth Headphones')->assertSee('Find these things')->assertSee('This list costs between Ƶ');

        $user->update(['preferred_language' => 'es']);
        $this->actingAs($user)->get('/education/high-spending-activities')->assertOk()
            ->assertSee('Tu lista de la compra')->assertSee('1 × Auriculares Bluetooth', false);
    }
}
