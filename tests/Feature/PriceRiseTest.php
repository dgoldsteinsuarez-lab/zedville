<?php

namespace Tests\Feature;

use App\Http\Controllers\BankController;
use App\Models\Activity;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Mailbox;
use App\Models\Product;
use App\Models\SchoolClass;
use App\Models\Supermarket;
use App\Models\Transaction1;
use App\Models\User;
use App\Models\WantsPlan;
use App\Models\WantsPlanCharge;
use App\Services\Finhero\Inflation\MyBasket;
use App\Services\LedgerService;
use App\Services\PriceRise;
use App\Services\ShoppingList;
use App\Services\WantsPlanService;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Intermediate's price rise (Daniel, 2026-11-04/08): in the level's second
 * month prices go up 5% on average, not all the same, with a reason and charts;
 * the salary stays. The student pays the new prices in the bills, the survey's
 * monthly shopping, the plan of the month and the shops; a City Hall letter
 * says so and the Statistics Office's page shows what rose and their own month.
 */
class PriceRiseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // the school year starts on 1 April: May is the Intermediate's second month
        Carbon::setTestNow(Carbon::create(2026, 5, 10, 10, 0, 0, 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
    }

    private function student(string $level = 'Intermediate', ?string $startedOn = null): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => $level . ' grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id, 'intermediate_started_on' => $startedOn]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => '2026-03-01']);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->subDays(4)]);

        return $user;
    }

    private function survey(User $user): ConsumerSurvey
    {
        $oil = Supermarket::where('type', 'omnivore')->where('food_group', 'fats')->first();
        $veg = Supermarket::where('type', 'omnivore')->where('food_group', 'vegetables')->first();

        return ConsumerSurvey::create(['user_id' => $user->id, 'sid' => '1', 'diet' => 'omnivore', 'survey_month' => 'Apr-2026',
            'groceries' => [
                ['id' => $oil->id, 'name' => $oil->product_name, 'unit_price' => 10, 'quantity' => 1, 'total_price' => 10],
                ['id' => $veg->id, 'name' => $veg->product_name, 'unit_price' => 5, 'quantity' => 4, 'total_price' => 20],
            ],
            'basket_cost' => 30, 'lunch' => 25, 'transport' => 80, 'activities' => 0, 'restaurants' => 0, 'total_cost' => 135]);
    }

    private function paid(User $user, string $description): float
    {
        return (float) Transaction1::where('user_id', $user->id)->where('description', 'like', $description)->value('amount');
    }

    public function test_prices_rise_in_the_second_month_of_the_intermediate(): void
    {
        $student = $this->student();
        $this->assertTrue(PriceRise::on($student));
        $this->assertSame('2026-05-01', PriceRise::since($student)->toDateString());
        $this->assertSame(309.0, PriceRise::price($student, 300, 'rent'));

        $this->assertFalse(PriceRise::on($this->student('Intermediate', '2026-05-01')));   // its first month
        $this->assertFalse(PriceRise::on($this->student('Rookie')));
        $this->assertSame(300.0, PriceRise::price($this->student('Rookie'), 300, 'rent'));
    }

    public function test_the_groups_make_the_five_percent_of_a_typical_month(): void
    {
        $before = $after = 0.0;
        foreach (PriceRise::bills() as $bill) {
            [$before, $after] = [$before + $bill['before'], $after + $bill['after']];
        }
        foreach (MyBasket::TYPICAL as $field => $amount) {
            [$before, $after] = [$before + $amount, $after + PriceRise::risen($amount, PriceRise::SURVEY[$field] ?? null)];
        }
        $plan = WantsPlanService::MID_TOTAL * config('zedville.monthly_salary');
        [$before, $after] = [$before + $plan, $after + PriceRise::risen($plan, PriceRise::PLAN)];

        $this->assertEqualsWithDelta(PriceRise::INDEX, ($after / $before - 1) * 100, 0.5);
        $this->assertSame(20, max(PriceRise::GROUPS));
        $this->assertSame(-3, min(PriceRise::GROUPS));
    }

    public function test_the_bills_and_the_monthly_shopping_cost_the_new_prices(): void
    {
        $student = $this->student();
        $this->survey($student);
        app(BankController::class)->ensureMonthlyBills($student);

        $this->assertSame(309.0, $this->paid($student, 'Rent'));
        $this->assertSame(1050.0, $this->paid($student, 'School Fees'));
        $this->assertSame(20.0, $this->paid($student, 'Internet'));
        $electricity = $this->paid($student, 'Utility - Electricity');
        $this->assertTrue($electricity >= 69 && $electricity <= 103.5, "electricity $electricity");
        // the groceries rise item by item: oil 10 × 1.20, vegetables 20 × 1.12
        $this->assertSame(34.4, $this->paid($student, 'Grocery – %'));
        $this->assertSame(26.5, $this->paid($student, 'School Lunch'));
        $this->assertSame(86.4, $this->paid($student, 'Transportation'));

        $rookie = $this->student('Rookie');
        $this->survey($rookie);
        app(BankController::class)->ensureMonthlyBills($rookie);
        $this->assertSame(300.0, $this->paid($rookie, 'Rent'));
        $this->assertSame(30.0, $this->paid($rookie, 'Grocery – %'));
    }

    public function test_the_shops_show_and_charge_the_new_prices(): void
    {
        $student = $this->student();
        $veg = Supermarket::where('type', 'omnivore')->where('food_group', 'vegetables')->first();
        $price = round($veg->price * 1.12, 2);

        $this->actingAs($student)->postJson('/order/place', ['pin' => '1234', 'catalog' => 'supermarkets', 'cart' => [['id' => $veg->id, 'quantity' => 2]]])
            ->assertOk()->assertJson(['status' => true]);
        $this->assertEquals($price, (float) DB::table('order_items')->where('product_id', $veg->id)->value('price'));
        $this->assertSame(round($price * 2, 2), $this->paid($student, 'Supermarket (Order #%'));

        $laptop = Product::where('category', 'Electronics')->orderByDesc('price')->first();
        $this->assertSame(round($laptop->price * 0.97, 2), PriceRise::productPrice($student, $laptop, 'products'));
        $this->actingAs($student)->get(route('education.spending-tracker', ['type' => 'tech-hub']))->assertOk()
            ->assertSee(__('prices.banner_link'))->assertSee(json_encode(round($laptop->price * 0.97, 2)), false);

        // the Rookie keeps the catalogue's prices and sees no notice
        $rookie = $this->student('Rookie');
        $this->assertSame((float) $veg->price, PriceRise::productPrice($rookie, $veg, 'supermarkets'));
        $this->actingAs($rookie)->get(route('education.spending-tracker', ['type' => 'tech-hub']))->assertOk()->assertDontSee(__('prices.banner_link'));
    }

    public function test_the_plan_of_the_month_costs_more_and_its_parts_still_add_up(): void
    {
        $student = $this->student();
        $plan = WantsPlanService::offers($student)->first();
        $card = WantsPlanService::card($plan, $student);
        $this->assertSame(round($plan->cost * 1.04, 2), $card['cost']);
        $this->assertSame(PriceRise::planText($student, $plan->text('description')), $card['description']);
        $this->assertSame('a ride (' . zeds(83.2) . ') and a film (' . zeds(208) . ')', PriceRise::planText($student, 'a ride (Ƶ 80) and a film (Ƶ 200)'));

        WantsPlanService::choose($student, $plan->id);
        $this->assertSame([round($plan->cost * 1.04, 2)], WantsPlanCharge::where('user_id', $student->id)->pluck('amount')->map(fn ($a) => (float) $a)->unique()->values()->all());
        $this->assertSame((float) $plan->cost, WantsPlanService::card($plan, $this->student('Rookie'))['cost'] + 0.0);
    }

    public function test_a_shopping_list_budget_rises_with_its_list(): void
    {
        $activity = Activity::where('title', 'Birthday Party Budget')->firstOrFail();   // fruit on the list: +12 %
        $state = ShoppingList::state($activity, $this->student());
        $this->assertSame(round($activity->budget * 1.12, 2), $state['budget']);
        $this->assertSame((float) $activity->budget, ShoppingList::state($activity, $this->student('Rookie'))['budget']);
    }

    public function test_the_city_hall_letter_comes_once(): void
    {
        $student = $this->student();
        $this->survey($student);
        PriceRise::announce($student);
        PriceRise::announce($student);

        $letters = Mailbox::where('student_id', $student->id)->where('template', 'price-rise')->get();
        $this->assertCount(1, $letters);
        $this->assertSame('2026-05', $letters[0]->template_data['month']);
        app()->setLocale('es');
        $this->assertStringContainsString('ha publicado su índice de precios', $letters[0]->content);
        $this->assertStringContainsString(route('prices.index'), $letters[0]->content);

        foreach ([$this->student('Intermediate', '2026-05-01'), $this->student('Rookie')] as $other) {
            PriceRise::announce($other);
            $this->assertSame(0, Mailbox::where('student_id', $other->id)->where('template', 'price-rise')->count());
        }
    }

    public function test_the_statistics_office_page(): void
    {
        $student = $this->student();
        $this->survey($student);

        $this->actingAs($student)->get(route('prices.index'))->assertOk()
            ->assertSee(__('prices.title'))->assertSee(__('prices.groups.oil'))->assertSee(PriceRise::pct(20))
            ->assertSee(__('prices.reasons.fruit_veg'))->assertSee(zeds(34.4));   // their groceries, now
        $this->actingAs($this->student('Rookie'))->get(route('prices.index'))->assertNotFound();
    }
}
