<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Beneficiary;
use App\Models\Donation;
use App\Models\Mailbox;
use App\Models\Npo;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use App\Services\ActivityFineService;
use App\Services\BankTransferService;
use App\Services\ClassActivityAssigner;
use App\Services\DonationService;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The monthly donation (Daniel, 2026-11-08): every level learns it in the bank -
 * a charity saved as a beneficiary, then a standing order of 4-6% of the
 * salary that can't be cancelled, only moved to another charity, with a
 * thank-you letter each month; an activity admins assign like the Emergency Fund.
 */
class DonationTest extends TestCase
{
    use RefreshDatabase;

    private Npo $shelter;
    private Npo $library;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 12, 7, 10, 0, 0, 'Europe/Berlin'));
        // the city's charities (seeded by a migration)
        $this->shelter = Npo::firstOrCreate(['slug' => 'animal-shelter'], ['category' => 'Animals', 'name' => 'Animal Shelter', 'account_number' => '1234-5678-9012', 'status' => 1]);
        $this->library = Npo::firstOrCreate(['slug' => 'local-library'], ['category' => 'Education', 'name' => 'Local Library', 'account_number' => ' 5678-9012-3456', 'status' => 1]);
        Npo::whereIn('id', [$this->shelter->id, $this->library->id])->update(['status' => 1]);
    }

    private function student(float $balance = 3952.40): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => '2026-10-01']);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => '2026-12-06 09:00',
            'description' => 'Monthly Salary Credit - December 2026', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);

        return $user;
    }

    private function save(User $user, string $name, string $number)
    {
        return $this->actingAs($user)->post(route('bank.beneficiary_store'), ['for' => 'donation', 'name' => $name, 'account_number' => $number]);
    }

    private function charity(User $user, Npo $npo): Beneficiary
    {
        $this->save($user, $npo->name, $npo->account_number);

        return Beneficiary::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    public function test_four_to_six_percent_of_the_salary(): void
    {
        $this->assertSame([158.10, 237.14], DonationService::range());
    }

    public function test_a_charity_is_saved_with_its_name_in_any_language_and_its_account_number(): void
    {
        $user = $this->student();

        $this->save($user, 'Animal Shelter', '1234 5678 9012')->assertSessionHas('success');
        $this->save($user, 'biblioteca local', '567890123456')->assertSessionHas('success');   // its Spanish name
        $this->save($user, 'Refugio de animales', '0000-0000-0000')->assertSessionHas('error');
        $this->save($user, 'Some Other Name', '5678-9012-3456')->assertSessionHas('error');

        $this->assertCount(2, DonationService::saved($user->id));
    }

    public function test_the_donation_is_set_up_paid_now_and_thanked_by_letter(): void
    {
        $user = $this->student();
        $saved = $this->charity($user, $this->shelter);

        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 100, 'start_date' => '2026-12-07'])
            ->assertSessionHas('error');
        $this->assertNull(DonationService::active($user->id));

        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 197.62, 'start_date' => '2026-12-07'])
            ->assertSessionHas('success');

        $order = DonationService::active($user->id);
        $this->assertNotNull($order);
        $this->assertSame('recurring', $order->type);
        $this->assertSame('monthly', $order->frequency);
        $this->assertSame('2027-03-31', $order->end_date->toDateString());   // the end of the school year
        $this->assertSame('2027-01-07', $order->next_run_at->toDateString());

        $line = Transaction1::where('user_id', $user->id)->where('type', 'debit')->latest('id')->first();
        $this->assertSame(['Donation', 'Donation to Animal Shelter', 197.62], [$line->category, $line->description, (float) $line->amount]);
        $this->assertSame(1, Donation::where('user_id', $user->id)->where('npo_name', 'Animal Shelter')->count());

        $letter = Mailbox::where('student_id', $user->id)->where('template', 'npo-thanks')->firstOrFail();
        app()->setLocale('es');
        $this->assertStringContainsString('Soy Luna', $letter->content);
        $this->assertStringContainsString('197,62', $letter->content);
        $this->assertStringContainsString('Refugio de animales', $letter->content);
        $this->assertSame('Gracias por tu donación de diciembre de 2026', $letter->subject);

        // only one
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 160, 'start_date' => '2026-12-08'])
            ->assertSessionHas('error');
        $this->assertSame(1, Transfer::where('user_id', $user->id)->where('kind', DonationService::KIND)->count());
    }

    public function test_it_goes_every_month_with_a_new_story(): void
    {
        $user = $this->student();
        $saved = $this->charity($user, $this->shelter);
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 158.10, 'start_date' => '2026-12-07']);

        Carbon::setTestNow(Carbon::create(2027, 1, 7, 10, 0, 0, 'Europe/Berlin'));
        LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary Credit - January 2027', ['sid' => '1']);
        $this->artisan('transfers:run-due')->assertSuccessful();

        $this->assertSame(2, Donation::where('user_id', $user->id)->count());
        $letters = Mailbox::where('student_id', $user->id)->where('template', 'npo-thanks')->orderBy('id')->get();
        $this->assertCount(2, $letters);
        app()->setLocale('en');
        $this->assertStringContainsString('Max, a cat at the shelter', $letters[1]->content);
        $this->assertSame('2027-02-07', DonationService::active($user->id)->next_run_at->toDateString());
    }

    public function test_it_runs_to_the_end_of_the_school_year_and_is_set_up_again_the_next(): void
    {
        $user = $this->student();
        $saved = $this->charity($user, $this->shelter);
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 200, 'start_date' => '2026-12-07']);
        // the school year starts in April (config academic_year_start_month)
        $this->assertSame('2027-03-31', DonationService::active($user->id)->end_date->toDateString());

        foreach (['2027-01-07', '2027-02-07', '2027-03-07', '2027-04-07'] as $day) {
            Carbon::setTestNow(Carbon::parse($day . ' 10:00', 'Europe/Berlin'));
            LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary Credit', ['sid' => '1']);
            $this->artisan('transfers:run-due')->assertSuccessful();
        }

        $this->assertSame(4, Donation::where('user_id', $user->id)->count());   // December to March
        $this->assertNull(DonationService::active($user->id));
        // last year's, filled in to keep or change
        $this->actingAs($user)->get(route('bank.transfer', ['to' => 'donation']))->assertOk()
            ->assertSee(__('bank_ui.donation_last', ['name' => 'Animal Shelter', 'amount' => zeds(200)]));
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 200, 'start_date' => '2027-04-07'])
            ->assertSessionHas('success');
        $this->assertSame('2028-03-31', DonationService::active($user->id)->end_date->toDateString());
    }

    public function test_without_money_it_waits_for_next_month(): void
    {
        $user = $this->student(100);
        $saved = $this->charity($user, $this->shelter);

        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 158.10, 'start_date' => '2026-12-07'])
            ->assertSessionHas('error');

        $order = DonationService::active($user->id);
        $this->assertNotNull($order);
        $this->assertSame('insufficient_balance', $order->failure);
        $this->assertSame(0, Donation::where('user_id', $user->id)->count());
        $this->assertGreaterThanOrEqual(0.0, (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance'));
    }

    public function test_it_cannot_be_cancelled_only_moved_to_another_charity(): void
    {
        $user = $this->student();
        $shelter = $this->charity($user, $this->shelter);
        $library = $this->charity($user, $this->library);
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $shelter->id, 'amount' => 200, 'start_date' => '2026-12-20']);
        $order = DonationService::active($user->id);

        $this->actingAs($user)->post(route('bank.transfer_cancel', $order))->assertSessionHas('error');
        $this->assertSame('scheduled', $order->fresh()->status);

        $this->actingAs($user)->post(route('bank.donation_change'), ['beneficiary' => $library->id])->assertSessionHas('success');
        $this->assertSame('Local Library', $order->fresh()->beneficiary_name);
        $this->assertSame(200.0, (float) $order->fresh()->amount);
    }

    public function test_the_bank_page_shows_the_donation_tab(): void
    {
        $user = $this->student();
        $this->actingAs($user)->get(route('bank.transfer', ['to' => 'donation']))->assertOk()
            ->assertSee(__('bank_ui.donation_add'))->assertSee(__('bank_ui.donation_saved_empty'));

        $saved = $this->charity($user, $this->shelter);
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 160, 'start_date' => '2026-12-20']);

        $this->actingAs($user)->get(route('bank.transfer', ['to' => 'donation']))->assertOk()
            ->assertSee(__('bank_ui.donation_yours'))->assertSee(__('bank_ui.donation_lock'));
        // in the list of orders: the lock instead of Cancel
        $this->actingAs($user)->get(route('bank.transfer', ['to' => 'own']))->assertOk()
            ->assertSee(__('bank_ui.donation_change'))->assertDontSee(__('bank_ui.cancel_order') . '</button>', false);
    }

    public function test_admins_assign_it_like_the_emergency_fund_and_can_remove_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $class = SchoolClass::create(['sid' => '1', 'name' => '8D']);

        $this->actingAs($admin)->get(route('admin.education.donation'))->assertOk()->assertSee('Monthly Donation');
        $this->actingAs($admin)->withSession(['selected_school' => 1])->post(route('admin.education.donation-position'),
            ['cid' => (string) $class->id, 'position' => 'required', 'start_date' => '2026-12-01', 'end_date' => '2026-12-15'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mba_position', ['activity_key' => DonationService::KEY, 'cid' => $class->id, 'required' => 1]);
        $this->assertDatabaseHas('calendar_events', ['classId' => $class->id, 'title' => DonationService::ACTIVITY]);

        $id = \Illuminate\Support\Facades\DB::table('mba_position')->where('activity_key', DonationService::KEY)->value('id');
        $this->actingAs($admin)->withSession(['selected_school' => 1])->get('/admin/education/mba-position/delete/' . $id)->assertSessionHas('success');
        $this->assertDatabaseMissing('calendar_events', ['classId' => $class->id, 'title' => DonationService::ACTIVITY]);
    }

    public function test_the_activity_is_done_once_the_standing_order_exists(): void
    {
        $user = $this->student();
        $class = (object) ['id' => $user->grade, 'sid' => '1'];
        $this->be(User::factory()->create(['role' => 1]));   // the admin who assigns it
        ClassActivityAssigner::assign($class, ['key' => DonationService::KEY, 'title' => DonationService::ACTIVITY, 'description' => 'x',
            'position' => 'required', 'start_date' => '2026-12-01', 'end_date' => '2026-12-31']);

        $pending = fn () => app(ActivityFineService::class)->pending($user->fresh())->pluck('title')->all();
        $this->assertContains(DonationService::ACTIVITY, $pending());

        $saved = $this->charity($user, $this->shelter);
        $this->actingAs($user)->post(route('bank.donation_store'), ['beneficiary' => $saved->id, 'amount' => 160, 'start_date' => '2026-12-07']);

        $this->assertNotContains(DonationService::ACTIVITY, $pending());
    }
}
