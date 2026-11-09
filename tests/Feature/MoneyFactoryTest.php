<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\CurrencyExchange;
use App\Models\EasyActivity;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Models\WantsPlanCharge;
use App\Models\WantsPlanChoice;
use App\Services\BankStatementService;
use App\Services\CurrencyExchangeService;
use App\Support\TransactionText;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Money Factory (Daniel, 2026-11-02): Zedville, the capital of Zedland,
 * prints the Zed; what money is and why it's printed; a currency exchange with
 * a live board and a small commission; easy activities that send students to
 * exchange money for a reason; the money supply (advanced) and too little or
 * too much money, apart. And on the City Map, the buildings you can go into,
 * before the districts.
 */
class MoneyFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-03 10:00:00');
    }

    private function student(float $balance = 300, array $class = [], ?int $levelOrder = null): User
    {
        $grade = $levelOrder ? Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(),
            'grade_level_id' => GradeLevel::create(['name' => 'Level ' . $levelOrder, 'sort_order' => $levelOrder])->id]) : null;
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade?->id] + $class);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0]);
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->subDay(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);

        return $user;
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    /** An exchange activity running for the student's class, as an admin assigns it. */
    private function assign(string $title, User $student): EasyActivity
    {
        $activity = EasyActivity::where('title', $title)->firstOrFail();
        DB::table('mba_position')->insert(['user_id' => 1, 'activity_key' => $activity->key(), 'sid' => '1', 'cid' => $student->grade,
            'required' => 1, 'optional' => 0, 'start_date' => '2026-11-01', 'end_date' => '2026-11-30', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('calendar_events')->insert(['classId' => $student->grade, 'sid' => '1', 'title' => $activity->calendarTitle(), 'position' => 'required',
            'start' => '2026-11-01 00:00:00', 'end' => '2026-12-01 00:00:00', 'created_at' => now(), 'updated_at' => now()]);

        return $activity;
    }

    public function test_the_first_page_explains_money_and_has_the_exchange(): void
    {
        $this->actingAs($this->student())->get(route('education.money-factory'))->assertOk()
            ->assertSee('Zedville is the capital of Zedland')
            ->assertSee('What is money?')->assertSee('Why does Zedland print money?')->assertSee('How a banknote is made')
            ->assertSee('Currency exchange')->assertSee('Exchange rates')->assertSee('Live')
            ->assertSee('Too little or too much money?')
            ->assertDontSee('The money supply: M1, M2 and M3'); // for the advanced levels
    }

    public function test_the_picture_opens_what_money_is_why_it_is_printed_and_how(): void
    {
        // Daniel, 2026-11-02: "la información sobre el dinero puede darse tras pinchar sobre una moneda ...
        // Dinero, como tal, creo que tiene 3 funciones" - the ECB's three: medium of exchange, unit of account, store of value
        $page = $this->actingAs($this->student())->get(route('education.money-factory'))->assertOk()
            ->assertSee('Explore the Money Factory')
            ->assertSee('Medium of exchange')->assertSee('Unit of account')->assertSee('Store of value')
            ->assertSee('Hello! I am the president of the Central Bank of Zedland.')
            ->assertSee(route('education.money-factory.circulation'), false)
            ->assertDontSee(route('education.money-factory.supply'), false);
        $this->assertSame(['board', 'coin', 'factory', 'president', 'scanner', 'stall'], array_keys($page->viewData('scene')['spots']));
        $this->assertSame('what', $page->viewData('scene')['spots']['coin']['open']);
        $this->assertSame('#exchange', $page->viewData('scene')['spots']['board']['open']);

        // the advanced levels also go to the money supply (the vault) and how money gets in (the armoured truck)
        $advanced = $this->actingAs($this->student(300, [], 4))->get(route('education.money-factory'))->viewData('scene')['spots'];
        $this->assertSame(route('education.money-factory.supply'), $advanced['vault']['open']);
        $this->assertSame(route('education.money-factory.enter'), $advanced['truck']['open']);
    }

    public function test_the_rates_start_from_the_ecb_and_move_less_than_half_a_percent(): void
    {
        $this->assertEqualsWithDelta(0.85 * 1.1172, CurrencyExchangeService::base('USD'), 1e-9);
        $this->assertEqualsWithDelta(149.328, CurrencyExchangeService::base('JPY'), 1e-3);

        foreach (array_keys(CurrencyExchangeService::ECB_RATES) as $code) {
            $seen = [];
            for ($s = 0; $s < 4000; $s += 37) {
                $rate = CurrencyExchangeService::rate($code, Carbon::createFromTimestamp(1793700000 + $s));
                $this->assertEqualsWithDelta(1, $rate / CurrencyExchangeService::base($code), 0.005, $code);
                $seen[] = round($rate, 6);
            }
            $this->assertGreaterThan(50, count(array_unique($seen)), "$code moves");
        }
    }

    public function test_an_exchange_takes_the_zeds_and_a_commission_and_finishes_the_task(): void
    {
        $student = $this->student(300);
        $task = $this->assign('Pocket money for New York', $student);
        $quote = CurrencyExchangeService::quote('USD', 100);

        $this->actingAs($student)->get(route('education.money-factory', ['task' => $task->id]))->assertOk()
            ->assertSee('Pocket money for New York')->assertSee('Your class is going on a school trip to New York.')
            ->assertSee('Exchange Ƶ 100.00 into US dollars.');

        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'USD', 'amount' => 100])->assertOk()
            ->assertJson(['text' => 'You receive ' . number_format($quote['foreign'], 2) . "\u{00A0}USD in cash. Ƶ 102.00 left your current account.",
                'done' => [$task->id]]);

        $this->assertSame(198.0, $this->balance($student));
        $lines = Transaction1::where('user_id', $student->id)->where('category', 'Currency Exchange')->orderBy('id')->get();
        $this->assertSame(['Currency exchange: ' . number_format($quote['foreign'], 2) . ' USD', 'Exchange commission'], $lines->pluck('description')->all());
        $this->assertSame([100.0, 2.0], $lines->pluck('amount')->map(fn ($a) => (float) $a)->all());
        $this->assertSame(1, CurrencyExchange::where('user_id', $student->id)->where('currency', 'USD')->count());
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => $task->key()]);

        // another currency doesn't count for it, and nothing is done twice
        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'EUR', 'amount' => 10])->assertOk();
        $this->assertSame(1, DB::table('activity_completions')->where('student_id', $student->id)->count());
    }

    public function test_the_rate_confirmed_is_the_rate_paid(): void
    {
        $student = $this->student(300);
        $seen = now()->subSeconds(40);

        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'GBP', 'amount' => 100, 'at' => $seen->getTimestamp()])->assertOk();
        $this->assertSame(CurrencyExchangeService::quote('GBP', 100, $seen)['foreign'], CurrencyExchange::latest('id')->value('foreign_amount'));

        // a quote older than two minutes doesn't hold: the rate of now
        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'GBP', 'amount' => 10, 'at' => now()->subMinutes(10)->getTimestamp()])->assertOk();
        $this->assertEqualsWithDelta(CurrencyExchangeService::rate('GBP'), CurrencyExchange::latest('id')->value('rate'), 1e-6);
    }

    public function test_the_banknote_tracker_tells_a_notes_month_and_its_questions_finish_the_task(): void
    {
        // Daniel, 2026-11-02: "que descubran ... por qué manos ha pasado un billete en el último mes"
        $student = $this->student();
        $task = $this->assign('Where has this banknote been?', $student);
        $serial = \App\Services\MoneyFactory\BanknoteJourney::serialFor($student->id);

        $this->actingAs($student)->get(route('education.money-factory', ['task' => $task->id]))->assertOk()
            ->assertSee('Banknote tracker')->assertSee('Your banknote has the serial number ' . $serial . '.');

        $month = $this->actingAs($student)->getJson(route('education.money-factory.tracker', ['serial' => strtolower(str_replace(' ', '', $serial))]))->assertOk()->json();
        $this->assertSame($serial, $month['serial']);
        $this->assertSame('Money Factory: printed and checked', $month['stops'][0]['name']);
        $this->assertSame('You', end($month['stops'])['name']);
        $this->assertGreaterThanOrEqual(10, count($month['stops']));
        $this->assertCount(4, $month['questions']);
        $this->assertArrayNotHasKey('answer', $month['questions'][0]); // the answers stay on the server

        // the same note, the same month; another note, no questions
        $this->assertSame($month['stops'], $this->actingAs($student)->getJson(route('education.money-factory.tracker', ['serial' => $serial]))->json('stops'));
        $this->actingAs($student)->getJson(route('education.money-factory.tracker', ['serial' => 'ZV 1111 2222']))->assertOk()->assertJson(['questions' => []]);
        $this->actingAs($student)->getJson(route('education.money-factory.tracker', ['serial' => '12']))->assertStatus(422);

        $right = array_column(\App\Services\MoneyFactory\BanknoteJourney::questions($serial), 'answer');
        $wrong = $right;
        $wrong[0] = ($wrong[0] + 1) % 4;
        $this->actingAs($student)->postJson(route('education.money-factory.tracker.answers'), ['answers' => $wrong])->assertOk()
            ->assertJson(['right' => [false, true, true, true], 'done' => false, 'message' => '1 answer is not right yet. Look again and try once more.']);
        $this->assertDatabaseMissing('activity_completions', ['student_id' => $student->id]);

        $this->actingAs($student)->postJson(route('education.money-factory.tracker.answers'), ['answers' => $right])->assertOk()
            ->assertJson(['right' => [true, true, true, true], 'done' => true, 'message' => 'All right! Activity done.']);
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => $task->key()]);
    }

    public function test_the_tracker_says_how_each_hand_passed_it_on_with_real_times(): void
    {
        // Daniel, 2026-11-02: "calculate how much really does money stay in someone hands. I guess that the
        // street musician paid the teacher, can you also add the address for US America to track the money ... also from Europe"
        $student = $this->student();
        $serial = \App\Services\MoneyFactory\BanknoteJourney::serialFor($student->id);
        $month = $this->actingAs($student)->getJson(route('education.money-factory.tracker', ['serial' => $serial]))->assertOk()->json();
        $this->assertSame('Sent to the Central Bank.', $month['stops'][0]['passed']);
        $this->assertStringEndsWith('took it out.', $month['stops'][3]['passed']);
        $this->assertNull(end($month['stops'])['passed']);
        $this->assertStringStartsWith('On average it stayed', $month['average']);
        foreach (array_slice($month['stops'], 4, -1) as $stop) {
            $this->assertNotEmpty($stop['passed']);
        }

        // about 2½ days in a person's hands, hours in a shop, a shop only between people, within the month
        foreach (range(1, 300) as $id) {
            $stops = \App\Services\MoneyFactory\BanknoteJourney::stops(\App\Services\MoneyFactory\BanknoteJourney::serialFor($id));
            $this->assertLessThanOrEqual(31 * 24, array_sum(array_column($stops, 'hours')));
            $hands = \App\Services\MoneyFactory\BanknoteJourney::hands($stops);
            $this->assertGreaterThanOrEqual(8, count($hands));
            $this->assertSame('person', $hands[0]['kind']);
            foreach ($hands as $i => $hand) {
                $this->assertLessThanOrEqual($hand['kind'] === 'shop' ? 26 : 120, $hand['hours']);
                if ($hand['kind'] === 'shop') {
                    $this->assertSame('person', $hands[$i - 1]['kind']);
                }
            }
        }

        $this->actingAs($student)->get(route('education.money-factory'))->assertOk()
            ->assertSee('https://www.wheresgeorge.com/', false)->assertSee('https://www.eurobilltracker.com/', false)
            ->assertSee('147 times a year');
    }

    public function test_how_money_gets_into_the_economy_for_the_advanced_levels(): void
    {
        $advanced = $this->student(300, [], 4);

        $this->actingAs($advanced)->get(route('education.money-factory'))->assertSee('How does money get into the economy?');
        $this->actingAs($this->student(300, [], 2))->get(route('education.money-factory'))->assertDontSee('How does money get into the economy?');
        $this->actingAs($advanced)->get(route('education.money-factory.enter'))->assertOk()
            ->assertSee('When politicians print money')->assertSee('Article 123')->assertSee('4.2 trillion marks')->assertSee('Printing to pay the bills');
    }

    public function test_no_exchange_without_the_money_or_outside_the_limits(): void
    {
        $student = $this->student(50);

        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'USD', 'amount' => 50])
            ->assertStatus(422)->assertJson(['message' => 'There isn\'t enough money in your current account for this exchange.']);
        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'USD', 'amount' => 1])->assertStatus(422);
        $this->actingAs($student)->postJson(route('education.money-factory.exchange'), ['currency' => 'XYZ', 'amount' => 10])->assertStatus(422);

        $this->assertSame(50.0, $this->balance($student));
        $this->assertSame(0, CurrencyExchange::count());
    }

    public function test_the_bank_lines_in_the_students_language(): void
    {
        app()->setLocale('es');
        $this->assertSame("Cambio de divisas: 1.234,56\u{00A0}USD", TransactionText::forStudent('Currency exchange: 1,234.56 USD'));
        $this->assertSame("Cambio de divisas: 14.933\u{00A0}JPY", TransactionText::forStudent('Currency exchange: 14,933 JPY'));
        $this->assertSame('Comisión de cambio', TransactionText::forStudent('Exchange commission'));
        app()->setLocale('de');
        $this->assertSame("Geldwechsel: 94,96\u{00A0}USD", TransactionText::forStudent('Currency exchange: 94.96 USD'));
    }

    public function test_the_money_supply_is_offered_to_the_advanced_levels(): void
    {
        $advanced = $this->student(300, [], 4);
        $rookie = $this->student(300, [], 1);

        $this->actingAs($advanced)->get(route('education.money-factory'))->assertSee('The money supply: M1, M2 and M3')->assertSee('Advanced');
        $this->actingAs($rookie)->get(route('education.money-factory'))->assertDontSee('The money supply: M1, M2 and M3');

        $this->actingAs($advanced)->get(route('education.money-factory.supply'))->assertOk()
            ->assertSee('Overnight deposits')->assertSee('17,717')->assertSee('1,618')->assertSee('3.5%');
        $this->actingAs($rookie)->get(route('education.money-factory.circulation'))->assertOk()
            ->assertSee('Too much money')->assertSee('4.2 trillion marks')->assertSee('Inflation in the euro area')->assertSee('8.4%');
    }

    public function test_too_much_money_often_comes_from_paying_debts_with_new_money(): void
    {
        // Daniel, 2026-11-02: "¿no tienes que añadir también que se imprime dinero para pagar indirectamente las deudas del país?"
        $this->actingAs($this->student())->get(route('education.money-factory.circulation'))->assertOk()
            ->assertSee('Printing money to pay the debts')
            ->assertSee('In the euro area this is forbidden (Article 123 of the EU Treaty).')
            ->assertSee('the EU Court of Justice said so in 2018')
            ->assertSee('Whoever owes wins; whoever saved loses.')
            ->assertSee('164 billion marks')->assertSee('16.4 pfennigs');

        $this->actingAs($this->student(300, ['default_language' => 'es']))->get(route('education.money-factory.circulation'))->assertOk()
            ->assertSee('Imprimir dinero para pagar deudas')->assertSee('Una deuda de 100,00')->assertSee('Gana quien debe; pierde quien ahorró.');

        // an explanation: not for a hybrid class
        $this->actingAs($this->student(300, ['default_language' => 'es', 'language_level' => 'hybrid']))->get(route('education.money-factory.circulation'))
            ->assertOk()->assertDontSee('Imprimir dinero para pagar deudas');
    }

    public function test_a_hybrid_class_is_informed_not_told_why(): void
    {
        $student = $this->student(300, ['default_language' => 'es', 'language_level' => 'hybrid']);
        $this->assign('A weekend in Paris', $student);

        // Daniel, 2026-11-02: "en híbrido, haz todo más corto y sencillo" - the short texts, in both languages
        $this->actingAs($student)->get(route('education.money-factory'))->assertOk()
            ->assertSee('Fábrica de dinero')->assertSee('Cambio de divisas')
            ->assertSee('El dinero tiene tres funciones:')->assertSee('Money has three functions:')
            ->assertDontSee('El dinero es algo que todo el mundo acepta')->assertDontSee('Money is something everyone accepts')
            ->assertSee('Decidimos cuánto dinero necesita Zedland.')
            ->assertDontSee(route('education.money-factory.circulation'), false) // the pages apart aren't offered
            ->assertDontSee('Tu familia pasa un fin de semana en París')->assertDontSee('Your family is spending a weekend in Paris')
            ->assertSee('Cambia 150,00', false);
    }

    public function test_the_spanish_page(): void
    {
        $student = $this->student(300, ['default_language' => 'es']);
        $this->assign('Ski day in the Swiss Alps', $student);

        $this->actingAs($student)->get(route('education.money-factory'))->assertOk()
            ->assertSee('Zedville es la capital de Zedland')->assertSee('¿Qué es el dinero?')->assertSee('«Toque, mire y gire»')
            ->assertSee('Tipos de cambio')->assertSee('Día de esquí en los Alpes suizos')
            ->assertSee('Necesitas francos suizos para el forfait.')->assertSee("Cambia 100,00\u{00A0}Ƶ por francos suizos.", false);
    }

    public function test_the_city_map_lists_the_buildings_you_can_go_into_before_the_districts(): void
    {
        $this->actingAs($this->student())->get(route('education.city'))->assertOk()
            ->assertSeeInOrder(['Important Buildings', 'Universal Bank', 'Money Factory', 'City Hall', 'Districts', 'Senior Care Quarter'])
            ->assertSee(route('education.money-factory'));
    }

    public function test_an_admin_makes_exchange_activities(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin)->get(route('admin.money-factory'))->assertOk()
            ->assertSee('Money Factory activities')->assertSee('Pocket money for New York')->assertSee('Summer camp in Canada')
            ->assertSee('Where has this banknote been?');

        // the menu by level: on one level, only that level's activities (the exchange ones are Junior to Intermediate)
        $advance = \App\Models\GradeLevel::firstOrCreate(['name' => 'Advance'], ['sort_order' => 4]);
        $this->actingAs($admin)->get(route('admin.money-factory', ['level' => $advance->id]))->assertOk()
            ->assertSee('Level: Advance')->assertDontSee('Pocket money for New York');
        $this->actingAs($admin)->get(route('admin.money-factory', ['level' => 0]))->assertOk()->assertSee('All levels')->assertSee('Pocket money for New York');

        $this->actingAs($admin)->post(route('admin.engagement.easy-activities.store'), [
            'type' => 'currency_exchange', 'title' => 'Concert in Manchester', 'currency' => 'GBP', 'min_amount' => 60,
            'reason' => 'Your favourite band plays in Manchester.',
        ])->assertRedirect();
        $activity = EasyActivity::where('title', 'Concert in Manchester')->firstOrFail();
        $this->assertSame(['currency' => 'GBP', 'reason' => 'Your favourite band plays in Manchester.'], $activity->content);
        $this->assertSame(60.0, $activity->min_amount);

        $this->actingAs($admin)->post(route('admin.engagement.easy-activities.store'), [
            'type' => 'currency_exchange', 'title' => 'No currency', 'min_amount' => 60,
        ])->assertSessionHasErrors('currency');
    }

    public function test_the_bank_shows_the_plan_payment_waiting_for_money(): void
    {
        $student = $this->student(100);
        $choice = WantsPlanChoice::create(['user_id' => $student->id, 'month' => '2026-11', 'kind' => 'plan', 'wants_plan_id' => 1, 'plan_ids' => [1], 'total' => 800]);
        WantsPlanCharge::create(['choice_id' => $choice->id, 'wants_plan_id' => 1, 'user_id' => $student->id, 'due_date' => '2026-11-02', 'amount' => 800, 'status' => 'waiting']);

        $plans = app(BankStatementService::class)->bills(BankAccount::where('student_id', $student->id)->first())['plans'];

        $this->assertCount(1, $plans);
        $this->assertSame('waiting', $plans[0]['status']);
        $this->assertSame(800.0, $plans[0]['amount']);
    }
}
