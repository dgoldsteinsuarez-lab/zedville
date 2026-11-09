<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\CardStatement;
use App\Models\CreditCard;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\CreditCardService;
use App\Services\LedgerService;
use App\Services\PriceRise;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The credit card, from the Intermediate (proposal «Tarjeta de crédito», step
 * 16): a 1,000 Ƶ limit, used in the City Mall and the spending activities;
 * each month's statement paid on the 7th - all of it for free, or the 3 %
 * minimum (at least 25 Ƶ) or a fixed amount, with 20 % APR on the rest; not
 * paid by the end of its month, into overdraft.
 */
class CreditCardTest extends TestCase
{
    use RefreshDatabase;

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    private function student(string $level = 'Intermediate', float $money = 3952.40): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        $account->forceFill(['primary_savings_account_amount' => 100])->save();
        AutoDebitRequest::create(['user_id' => $user->id, 'sid' => 1, 'type' => 'Rent']);
        LedgerService::post($user->id, 'credit', 'Income', $money, 'Monthly Salary Credit', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDays(5)]);

        return $user;
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    private function product(float $price): int
    {
        return DB::table('products')->insertGetId(['product_name' => 'Jacket ' . uniqid(), 'type' => 'shop', 'category' => 'Clothing', 'price' => $price, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_the_conditions_maths(): void
    {
        // the minimum: 3 %, at least 25, or all of a smaller debt
        $this->assertSame([30.0, 25.0, 20.0, 0.0], [CreditCardService::minimum(1000), CreditCardService::minimum(500), CreditCardService::minimum(20), CreditCardService::minimum(0)]);
        // 1,000 owed: a year of equal payments, or the minimum for years
        $this->assertSame(91.86, CreditCardService::equalPayment(1000, 12));
        $this->assertSame(['months' => 12, 'interest' => 84.19], CreditCardService::payoff(1000, 91.86));
        $this->assertSame(['months' => 58, 'interest' => 479.24], CreditCardService::payoff(1000));
        // near the BaFin example: 1,500 at the minimum, 85 months
        $this->assertSame(85, CreditCardService::payoff(1500)['months']);
    }

    public function test_asking_for_it_and_paying_with_it_in_the_city_mall(): void
    {
        $this->setDay('2026-06-10');
        $this->assertSame('card_not_offered', CreditCardService::open($this->student('Rookie')));
        $student = $this->student();
        $card = CreditCardService::open($student);
        $this->assertInstanceOf(CreditCard::class, $card);
        $this->assertSame('card_has', CreditCardService::open($student));

        $jacket = $this->product(120);
        $before = $this->balance($student);
        $response = $this->actingAs($student)->postJson('/order/place', ['pin' => '1234', 'cart' => [['id' => $jacket, 'quantity' => 2]], 'pay_with' => 'card'])
            ->assertOk()->assertJson(['status' => true, 'paid_with' => 'card']);
        $total = (float) $response->json('total');   // 240 and the Intermediate's price rise on clothes
        $this->assertSame(zeds(1000 - $total), $response->json('card_available_text'));
        $this->assertSame($before, $this->balance($student));   // the current account is untouched
        $this->assertSame($total, CreditCardService::owed($card));
        $this->assertSame(1, DB::table('orders')->where('user_id', $student->id)->count());

        // over the limit: refused, nothing bought
        $tv = $this->product(900);
        $this->actingAs($student)->postJson('/order/place', ['pin' => '1234', 'cart' => [['id' => $tv, 'quantity' => 1]], 'pay_with' => 'card'])
            ->assertStatus(422)->assertJson(['message' => __('flash.card_limit')]);
        $this->assertSame(1, DB::table('orders')->where('user_id', $student->id)->count());
        // without a card, the card can't pay
        $other = $this->student();
        $this->actingAs($other)->postJson('/order/place', ['pin' => '1234', 'cart' => [['id' => $jacket, 'quantity' => 1]], 'pay_with' => 'card'])
            ->assertStatus(422)->assertJson(['message' => __('flash.card_none')]);
    }

    public function test_the_statement_paid_in_full_or_the_minimum_with_interest(): void
    {
        $this->setDay('2026-06-10');
        $full = $this->student();
        $minimum = $this->student();
        foreach ([$full, $minimum] as $student) {
            CreditCardService::open($student);
            CreditCardService::purchase($student, 600, 'City Mall (Order #1)');
        }
        CreditCardService::setMode($minimum, 'minimum');
        $this->assertSame('card_fixed', CreditCardService::setMode($minimum, 'fixed', 10));

        // the 1st: the statement of June; the 7th: its payment, with the bills
        $this->setDay('2026-07-02');
        CreditCardService::settle($full);
        $this->assertSame(['pending', 600.0], [CardStatement::where('user_id', $full->id)->value('status'), (float) CardStatement::where('user_id', $full->id)->value('balance')]);
        $this->setDay('2026-07-07');
        foreach ([$full, $minimum] as $student) {
            LedgerService::post($student->id, 'credit', 'Income', 3952.40, 'Monthly Salary Credit', ['sid' => '1']);
            CreditCardService::settle($student);
        }
        $card = CreditCardService::card($full->id);
        $this->assertSame([0.0, 'paid', 0.0], [CreditCardService::owed($card), CardStatement::where('user_id', $full->id)->value('status'), (float) CardStatement::where('user_id', $full->id)->value('interest')]);
        $this->assertSame(600.0, (float) Transaction1::where('user_id', $full->id)->where('description', CreditCardService::LINE_PAYMENT)->value('amount'));

        // the minimum: 25 Ƶ (3 % is 18), and a month of 20 % APR on the other 575
        $interest = round(575 * CreditCardService::monthlyRate(), 2);
        $this->assertSame(8.8, $interest);
        $this->assertSame(round(575 + $interest, 2), CreditCardService::owed(CreditCardService::card($minimum->id)));
        $this->assertSame([25.0, $interest], [(float) CardStatement::where('user_id', $minimum->id)->value('paid'), (float) CardStatement::where('user_id', $minimum->id)->value('interest')]);
        CreditCardService::settle($minimum);   // once
        $this->assertSame(1, Transaction1::where('user_id', $minimum->id)->where('description', CreditCardService::LINE_PAYMENT)->count());
    }

    public function test_a_statement_not_paid_in_its_month_goes_into_overdraft(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student('Intermediate', 100);
        CreditCardService::open($student);
        CreditCardService::purchase($student, 300, 'City Mall (Order #1)');
        $this->setDay('2026-07-07');
        CreditCardService::settle($student);   // 100 in the account: not enough for 300, tried again later
        $this->assertSame('pending', CardStatement::where('user_id', $student->id)->value('status'));
        $this->assertSame(100.0, $this->balance($student));

        $this->setDay('2026-08-01');
        CreditCardService::settle($student);
        $this->assertSame('late', CardStatement::where('user_id', $student->id)->value('status'));
        $this->assertSame(-200.0, $this->balance($student));
        $this->assertSame(1, \App\Models\Mailbox::where('student_id', $student->id)->where('template', 'overdraft')->count());
    }

    public function test_the_pages(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student();
        $student->forceFill(['preferred_language' => 'es'])->save();
        $this->actingAs($student)->get(route('bank.card'))->assertOk()->assertSee('Lo que cuesta aplazar')->assertSee('Pedir la tarjeta')
            ->assertSee(__('bank_ui.tab_card', [], 'es'));
        $this->actingAs($student)->post(route('bank.card.store'), ['accept' => 1])->assertSessionHas('success');
        $this->actingAs($student)->post(route('bank.card.mode'), ['mode' => 'fixed', 'fixed' => 100])->assertSessionHas('success');
        $this->assertSame(['fixed', 100.0], [CreditCardService::card($student->id)->mode, (float) CreditCardService::card($student->id)->fixed_amount]);
        $this->actingAs($student)->get(route('bank.card'))->assertOk()->assertSee('Cómo pagas cada extracto')->assertSee('No debes nada del mes pasado');
        // a City Mall shop offers it at the checkout
        $this->actingAs($student)->get(route('education.spending-tracker', 'tech-hub'))->assertOk()
            ->assertSee(__('card.pay_with', [], 'es'))->assertSee('name="payWith" value="card"', false);
        $this->actingAs($this->student('Rookie'))->get(route('bank.card'))->assertNotFound();

        $this->actingAs(User::factory()->create(['role' => 1]))->get(route('admin.cards.index'))->assertOk()
            ->assertSee('Credit Cards')->assertSee('Fixed: 100.00 Ƶ');
    }
}
