<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\SchoolClass;
use App\Models\SubstituteGoodsPick;
use App\Models\Supermarket;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-23: the basket swap is an Engagement activity an admin
 * assigns; it only shows in the student's own diet section; the diet only
 * changes through the survey, once per month, from the next month.
 */
class SubstituteGoodsActivityTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $diet = 'vegan', array $items = []): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $student->id, 'sid' => '1', 'student_name' => $student->name, 'student_email' => $student->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        Transaction1::create([
            'user_id' => $student->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => 1000, 'balance' => 1000,
        ]);
        ConsumerSurvey::create([
            'user_id' => $student->id, 'sid' => '1', 'diet' => $diet,
            'groceries' => collect($items)->map(fn ($i) => ['id' => $i->id, 'name' => $i->product_name, 'unit_price' => (float) $i->price, 'quantity' => 1, 'total_price' => (float) $i->price])->all(),
            'basket_cost' => collect($items)->sum('price'), 'lunch' => 0, 'transport' => 0, 'activities' => 0, 'restaurants' => 0,
            'total_cost' => collect($items)->sum('price'), 'survey_month' => now()->format('M-Y'),
        ]);

        return $student;
    }

    private function item(string $diet, string $name, float $price): Supermarket
    {
        return Supermarket::create(['product_name' => $name, 'type' => $diet, 'category' => 'Needs', 'price' => $price, 'quantity' => 1]);
    }

    public function test_an_admin_assigns_it_to_a_class_and_it_lands_on_the_calendar(): void
    {
        $student = $this->student();
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin)->withSession(['selected_school' => '1'])
            ->get(route('admin.engagement.substitute-goods'))->assertOk()->assertSee('What the students do');

        $this->actingAs($admin)->withSession(['selected_school' => '1'])->post(route('admin.engagement.substitute-goods.store'), [
            'cid' => $student->grade, 'position' => 'required',
            'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('mba_position', ['activity_key' => 'substitute_goods', 'cid' => $student->grade, 'required' => 1]);
        $this->assertDatabaseHas('calendar_events', ['classId' => $student->grade, 'title' => 'Substitute Goods: My Shopping Basket']);

        // The student sees it in their language on the to-do list.
        $titles = array_column($this->actingAs($student)->get(route('dashboard'))->viewData('activities'), 'title');
        $this->assertContains('Substitute Goods: My Shopping Basket', $titles);

        $id = DB::table('mba_position')->where('activity_key', 'substitute_goods')->value('id');
        $this->actingAs($admin)->withSession(['selected_school' => '1'])
            ->delete(route('admin.engagement.substitute-goods.delete', $id))->assertRedirect();
        $this->assertDatabaseMissing('calendar_events', ['title' => 'Substitute Goods: My Shopping Basket']);
    }

    public function test_swapping_the_last_out_of_stock_item_completes_the_activity(): void
    {
        $tofu = $this->item('vegan', 'Tofu', 6);
        $seitan = $this->item('vegan', 'Seitan', 7);
        $student = $this->student('vegan', [$tofu]);
        $admin = User::factory()->create(['role' => 1]);
        $this->actingAs($admin)->withSession(['selected_school' => '1'])->post(route('admin.engagement.substitute-goods.store'), [
            'cid' => $student->grade, 'position' => 'required',
            'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $this->actingAs($student)->getJson(route('basket.state'))->assertJson(['active' => true])->assertJsonPath('items.0.blocked', true);
        $this->assertSame([$tofu->id], SubstituteGoodsPick::pluck('supermarket_id')->all());

        $this->actingAs($student)->postJson(route('basket.swap'), ['old_item_id' => $tofu->id, 'new_item_id' => $seitan->id])
            ->assertOk()->assertJson(['activity_completed' => true, 'engagement_point_awarded' => true]);

        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'substitute_goods']);
        $titles = array_column($this->actingAs($student)->get(route('dashboard'))->viewData('activities'), 'title');
        $this->assertNotContains('Substitute Goods: My Shopping Basket', $titles);
    }

    public function test_the_basket_only_shows_in_the_students_own_diet_section(): void
    {
        $student = $this->student('vegan');

        $this->actingAs($student)->get(route('supermarket.supermarket', 'vegan'))
            ->assertOk()->assertSee('id="myShoppingBasket"', false);
        $this->actingAs($student)->get(route('supermarket.supermarket', 'omnivore'))
            ->assertOk()->assertDontSee('id="myShoppingBasket"', false);
    }

    public function test_the_survey_is_once_a_month_and_a_new_one_starts_next_month(): void
    {
        // Answered last month: the vegan profile is in effect now.
        $student = $this->student('vegan');
        ConsumerSurvey::where('user_id', $student->id)->update(['created_at' => now()->startOfMonth()->subMonth(), 'survey_month' => now()->startOfMonth()->subMonth()->format('M-Y')]);

        $answers = ['diet' => 'omnivore', 'groceries' => [['id' => 1, 'name' => 'Rice', 'unit_price' => 2, 'quantity' => 5, 'total_price' => 10]],
            'basketCost' => 10, 'lunch' => 20, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'totalCost' => 30];

        $this->actingAs($student)->postJson('/profile/storesurvey', $answers)->assertOk()->assertJson(['status' => true]);

        $next = ConsumerSurvey::where('user_id', $student->id)->latest('id')->first();
        $this->assertSame(now()->startOfMonth()->addMonth()->format('M-Y'), $next->survey_month);
        // This month keeps the vegan basket, and nothing is charged now for next month.
        $this->assertSame('vegan', \App\Services\ConsumerSurveySummaryService::forUser($student->id)['diet']);
        $this->assertSame(1000.0, (float) Transaction1::where('user_id', $student->id)->latest('id')->value('balance'));

        // A second survey this month is refused.
        $this->actingAs($student)->postJson('/profile/storesurvey', $answers)->assertStatus(422)->assertJson(['status' => false]);
        $this->assertSame(2, ConsumerSurvey::where('user_id', $student->id)->count());

        // From next month the new diet applies.
        $this->travel(1)->months();
        $this->assertSame('omnivore', \App\Services\ConsumerSurveySummaryService::forUser($student->id)['diet']);
    }
}
