<?php

namespace Tests\Feature\Bank;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\StatementGenerator;
use App\Support\TransactionText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: "I don't want the bank statement to be full of
 * items." A grocery trip or a Spending Activity is one bank line (a
 * Spending Activity is always a Want, even a donation in it), and the
 * month-end penalty is one line called "Entertainment".
 */
class OneLinePerOrderTest extends TestCase
{
    use RefreshDatabase;

    private function student(float $balance = 1000): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        Transaction1::create([
            'user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income',
            'amount' => $balance, 'balance' => $balance, 'is_penalty' => false,
        ]);

        return $user;
    }

    private function item(string $table, string $name, string $category, float $price): int
    {
        return DB::table($table)->insertGetId([
            'product_name' => $name, 'type' => 'shop', 'category' => $category, 'price' => $price,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function purchases(User $user)
    {
        return Transaction1::where('user_id', $user->id)->where('description', '!=', 'Opening balance')->get();
    }

    public function test_a_grocery_trip_is_one_needs_line(): void
    {
        $user = $this->student();
        $chicken = $this->item('supermarkets', 'Chicken', 'Needs', 7.72);
        $soap = $this->item('supermarkets', 'Soap', 'Personal Care & Household', 5);
        $bread = $this->item('supermarkets', 'Bread', 'Needs', 2.50);

        $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234', 'catalog' => 'supermarkets',
            'cart' => [['id' => $chicken, 'quantity' => 2], ['id' => $soap, 'quantity' => 1], ['id' => $bread, 'quantity' => 1]],
        ])->assertOk()->assertJson(['status' => true]);

        $lines = $this->purchases($user);
        $this->assertCount(1, $lines);
        $this->assertSame('Needs', $lines[0]->category);
        $this->assertMatchesRegularExpression('/^Supermarket \(Order #\d+\)$/', $lines[0]->description);
        $this->assertEquals(22.94, (float) $lines[0]->amount);
        $this->assertEquals(1000 - 22.94, (float) $lines[0]->balance);
        $this->assertSame(3, DB::table('order_items')->count());   // the items are still kept on the order
    }

    public function test_a_spending_activity_is_one_wants_line_even_with_a_donation_or_groceries(): void
    {
        $user = $this->student();
        $ball = $this->item('products', 'Basketball', 'Sports', 70);
        $gift = $this->item('products', 'Charity Gift', 'Donation', 20);
        $milk = $this->item('supermarkets', 'Milk', 'Needs', 1.06);
        $activityId = DB::table('activities')->insertGetId(['type' => 'high', 'number' => 99, 'title' => 'Free shopping', 'mission' => 'Buy what you like.',
            'shop' => 'Tech Hub', 'destination' => 'Your Own Home', 'created_at' => now(), 'updated_at' => now()]); // no shopping list: any basket

        $this->actingAs($user)->postJson('/order/placeActivity', [
            'pin' => '1234', 'activity_id' => $activityId,
            'cart' => [['id' => $ball, 'quantity' => 1], ['id' => $gift, 'quantity' => 1]],
        ])->assertOk();
        $this->actingAs($user)->postJson('/order/placeActivity', [
            'pin' => '1234', 'activity_id' => $activityId, 'catalog' => 'supermarkets',
            'cart' => [['id' => $milk, 'quantity' => 3]],
        ])->assertOk();

        $lines = $this->purchases($user);
        $this->assertCount(2, $lines);
        foreach ($lines as $line) {
            $this->assertSame('Wants', $line->category);
            // it says which activity (Daniel, 2026-11-02: "Compra Fiesta de Cumpleaños, para que lo ponga en deseos")
            $this->assertMatchesRegularExpression('/^Spending Activity: Free shopping \(Order #\d+\)$/', $line->description);
        }
        $this->assertEqualsCanonicalizing([90.0, 3.18], $lines->pluck('amount')->map(fn ($a) => (float) $a)->all());
    }

    public function test_a_purchase_has_a_generic_clear_name_in_each_language(): void
    {
        // Daniel, 2026-11-02: "un nombre genérico y claro ... por ejemplo, Compra Supermercado"
        $this->assertSame('Supermarket purchase', TransactionText::forStudent('Supermarket (Order #12)'));
        $this->assertSame('Purchase: Birthday party', TransactionText::forStudent('Spending Activity: Birthday Party Budget (Order #13)'));
        app()->setLocale('es');
        $this->assertSame('Compra en el supermercado', TransactionText::forStudent('Supermarket (Order #12)'));
        $this->assertSame('Compra en el centro comercial', TransactionText::forStudent('City Mall (Order #14)'));
        $this->assertSame('Compra: fiesta de cumpleaños', TransactionText::forStudent('Spending Activity: Birthday Party Budget (Order #13)'));
        $this->assertSame('Compra: Free shopping', TransactionText::forStudent('Spending Activity: Free shopping (Order #2)')); // an activity of an admin's own
        app()->setLocale('fr');
        $this->assertSame('Achat : journée à la plage', TransactionText::forStudent('Spending Activity: Beach Day (Order #3)'));
        app()->setLocale('de');
        $this->assertSame('Einkauf im Supermarkt', TransactionText::forStudent('Supermarket (Order #12)'));
    }

    public function test_the_statement_shows_an_older_order_as_one_purchase(): void
    {
        // Daniel, 2026-11-02: the statement still listed "Purchase: Soap (Order #3)" item by item
        $user = $this->student(100);
        $account = BankAccount::where('student_id', $user->id)->first();
        $balance = 100.0;
        foreach ([['Purchase: Soap (Order #3)', 'Personal Care & Household', 5], ['Purchase: Toilet Paper (Order #3)', 'Personal Care & Household', 10],
                     ['Purchase: Acoustic Guitar (Order #1)', 'Music Instruments', 30]] as [$description, $category, $amount]) {
            $balance -= $amount;
            Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now(),
                'description' => $description, 'category' => $category, 'type' => 'debit', 'amount' => $amount, 'balance' => $balance, 'is_penalty' => false]);
        }

        app()->setLocale('es');
        $month = app(\App\Services\BankStatementService::class)->month($account, \App\Services\BankTransferService::CURRENT, now());

        // after the opening balance: one line per order, the shop guessed from the items (the old orders don't say it)
        $purchases = array_slice($month['lines'], 1);
        $this->assertSame(['Compra en el supermercado', 'Compra en el centro comercial'], array_column($purchases, 'text'));
        $this->assertSame([15.0, 85.0], [$purchases[0]['out'], $purchases[0]['balance']]); // the order's total, the balance after its last item
        $this->assertSame([30.0, 55.0], [$purchases[1]['out'], $purchases[1]['balance']]);
        $this->assertSame(['needs' => 15.0, 'wants' => 30.0], $month['groups']);
        $this->assertSame(55.0, $month['closing']);
    }

    public function test_a_city_mall_purchase_is_one_wants_line(): void
    {
        $user = $this->student();
        $guitar = $this->item('products', 'Acoustic Guitar', 'Music Instruments', 80);
        $ball = $this->item('products', 'Basketball', 'Sports', 70);

        $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234', 'cart' => [['id' => $guitar, 'quantity' => 1], ['id' => $ball, 'quantity' => 1]],
        ])->assertOk();

        $lines = $this->purchases($user);
        $this->assertCount(1, $lines);
        $this->assertSame('Wants', $lines[0]->category);
        $this->assertStringStartsWith('City Mall (Order #', $lines[0]->description);
        $this->assertEquals(150.0, (float) $lines[0]->amount);
    }

    public function test_the_month_end_fine_is_one_line_saying_why(): void
    {
        // No plan of the month chosen (2026-11-07): one City Hall fine takes the
        // month's spending up to 99% of the salary.
        $user = $this->student(5000);
        $salary = \App\Services\MonthEndPenaltyService::salary();

        app(BankController::class)->banks_penalty(app(StatementGenerator::class), $user);

        $fines = Transaction1::where('user_id', $user->id)->where('type', 'debit')->get();
        $this->assertCount(1, $fines);
        $this->assertSame('Fine', $fines[0]->category);
        $this->assertTrue((bool) $fines[0]->is_penalty);
        $this->assertSame('Fine: activity not done - Monthly plan', $fines[0]->description);
        $this->assertEqualsWithDelta($salary * 0.99, (float) $fines[0]->amount, 0.02);
    }

    public function test_the_needs_and_wants_activity_folds_older_per_item_orders_into_one_line(): void
    {
        $user = $this->student();
        $lastMonth = now()->startOfMonth()->subMonth()->addDays(2);
        foreach ([
            ['Purchase: Chicken (Order #4)', 'Needs', 15.44],
            ['Purchase: Beef (Order #4)', 'Needs', 7.70],
            ['Purchase: Acoustic Guitar (Order #1)', 'Music Instruments', 80],
            ['Purchase: Competition Basketball (Order #1)', 'Sports', 70],
            ['Penalty Item: Cinema', 'Penalty', 25],
            ['Penalty Item: Concert', 'Penalty', 60],
        ] as [$description, $category, $amount]) {
            $t = Transaction1::create([
                'user_id' => $user->id, 'sid' => '1', 'description' => $description, 'category' => $category,
                'type' => 'debit', 'amount' => $amount, 'balance' => 0, 'transaction_date' => $lastMonth,
            ]);
            $t->forceFill(['created_at' => $lastMonth])->save();
        }

        $template = collect($this->actingAs($user)->get(route('education.index'))->viewData('template'))->keyBy('name');

        $this->assertCount(3, $template);
        $this->assertSame(['needs', -23.14, 'order-4'], [$template['Supermarket purchase']['category'], $template['Supermarket purchase']['amount'], $template['Supermarket purchase']['id']]);
        $this->assertSame(['wants', -150.0], [$template['City Mall purchase']['category'], $template['City Mall purchase']['amount']]);
        $this->assertSame(['wants', -85.0], [$template['Entertainment']['category'], (float) $template['Entertainment']['amount']]);
    }
}
