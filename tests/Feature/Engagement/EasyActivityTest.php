<?php

namespace Tests\Feature\Engagement;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\EasyActivity;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\BadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-29: "I want to create some activities where student also
 * need to transfer money to a friend ... like in real life, adding the bank
 * account number ... Under easy activities, with Engagement Badge, we can
 * start adding activities in Admin, like this transfer activity".
 */
class EasyActivityTest extends TestCase
{
    use RefreshDatabase;

    // 5000: opening the bank charges the month's bills
    private function student(SchoolClass $class, float $balance = 5000): array
    {
        $user = User::factory()->create(['role' => 4, 'sid' => $class->sid, 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => $class->sid, 'student_name' => $user->name, 'student_email' => $user->email,
            'primary_savings_account_number' => '123000-' . random_int(100000, 999999),
            'card_iban' => 'ZV' . random_int(1000000000, 9999999999), 'card_pin' => '1234', 'primary_savings_account_amount' => $balance,
        ]);
        AutoDebitRequest::create(['user_id' => $user->id, 'sid' => $class->sid, 'type' => 'Rent']);
        Transaction1::create(['user_id' => $user->id, 'sid' => $class->sid, 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);

        return [$user, $account];
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    /** A super admin creates the activity and assigns it to the class. */
    private function assigned(SchoolClass $class, string $position = 'required', ?float $min = null, ?string $from = null, ?string $until = null): EasyActivity
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->withSession(['selected_school' => $class->sid])->post(route('admin.engagement.easy-activities.store'), [
            'type' => 'bank_transfer', 'title' => 'Transfer money to a friend', 'min_amount' => $min,
        ])->assertSessionHasNoErrors();
        $activity = EasyActivity::where('title', 'Transfer money to a friend')->firstOrFail();
        $this->actingAs($admin)->withSession(['selected_school' => $class->sid])->post(route('admin.engagement.easy-activities.assign', $activity->id), [
            'cid' => $class->id, 'position' => $position,
            'start_date' => $from ?? now()->startOfMonth()->toDateString(), 'end_date' => $until ?? now()->endOfMonth()->toDateString(),
        ])->assertSessionHasNoErrors();

        return $activity;
    }

    private function send(User $from, BankAccount $to, User $toUser, float $amount, ?string $memo = 'Pizza on Friday')
    {
        return $this->actingAs($from)->post(route('bank.transfer_store'), [
            'from_account' => 'Primary Savings Account', 'to' => 'friend', 'type' => 'now',
            'account_number' => $to->primary_savings_account_number, 'beneficiary_name' => $toUser->name, 'amount' => $amount, 'memo' => $memo,
        ]);
    }

    private function completions(User $user): int
    {
        return DB::table('activity_completions')->where('student_id', $user->id)->where('activity_type', 'like', 'easy\_%')->count();
    }

    public function test_an_admin_creates_it_and_assigns_it_to_a_class_calendar(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        $this->student($class);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->withSession(['selected_school' => 1])->get(route('admin.engagement.easy-activities'))
            ->assertOk()->assertSee('What the students do')->assertSee('Bank: transfer money to another student')
            ->assertSee('value="Transfer money to a friend"', false);

        $activity = $this->assigned($class, 'required', 20);

        $this->assertNull($activity->sid); // a super admin's is for every school
        $this->assertEquals(20, $activity->min_amount);
        // found by its key: activity_id belongs to the spending activities
        $this->assertDatabaseHas('mba_position', ['activity_key' => 'easy_' . $activity->id, 'activity_id' => null, 'cid' => $class->id, 'required' => 1]);
        $this->assertDatabaseHas('calendar_events', ['classId' => $class->id, 'title' => 'easy-Transfer money to a friend', 'position' => 'required']);

        $this->actingAs($admin)->withSession(['selected_school' => 1])->get(route('admin.engagement.easy-activities'))
            ->assertSeeInOrder(['Transfer money to a friend', 'at least Ƶ 20.00', '9A', 'Required', '0 of 1 students']);

        // two activities can't have the same title (the calendar finds them by it)
        $this->actingAs($admin)->post(route('admin.engagement.easy-activities.store'), ['type' => 'bank_transfer', 'title' => 'Transfer money to a friend'])
            ->assertSessionHasErrors('title');
        $this->actingAs($admin)->post(route('admin.engagement.easy-activities.store'), ['type' => 'fly_to_the_moon', 'title' => 'Moon'])
            ->assertSessionHasErrors('type');
    }

    public function test_renaming_and_deleting_keep_the_calendar_in_step(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        $activity = $this->assigned($class);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put(route('admin.engagement.easy-activities.update', $activity->id), ['title' => 'Pay a friend back', 'min_amount' => 5])->assertRedirect();
        $this->assertDatabaseHas('calendar_events', ['classId' => $class->id, 'title' => 'easy-Pay a friend back']);
        $this->assertDatabaseMissing('calendar_events', ['title' => 'easy-Transfer money to a friend']);

        $assignment = DB::table('mba_position')->where('activity_key', 'easy_' . $activity->id)->value('id');
        $this->actingAs($admin)->withSession(['selected_school' => 1])->delete(route('admin.engagement.easy-activities.unassign', $assignment))->assertRedirect();
        $this->assertDatabaseMissing('calendar_events', ['title' => 'easy-Pay a friend back']);

        $this->actingAs($admin)->delete(route('admin.engagement.easy-activities.delete', $activity->id))->assertRedirect();
        $this->assertDatabaseMissing('easy_activities', ['id' => $activity->id]);
    }

    public function test_a_school_admin_works_only_with_their_own_school(): void
    {
        $mine = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        $other = SchoolClass::create(['sid' => 2, 'name' => '7B']);
        $global = $this->assigned($mine);
        $otherSchools = EasyActivity::create(['type' => 'bank_transfer', 'title' => 'Other school transfer', 'sid' => 2]);
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);

        $this->actingAs($schoolAdmin)->get(route('admin.engagement.easy-activities'))->assertOk()
            ->assertSee('Transfer money to a friend')->assertDontSee('Other school transfer')->assertDontSee('7B');

        $this->actingAs($schoolAdmin)->post(route('admin.engagement.easy-activities.store'), ['type' => 'bank_transfer', 'title' => 'Our class transfer'])->assertRedirect();
        $this->assertDatabaseHas('easy_activities', ['title' => 'Our class transfer', 'sid' => 1]);

        // they assign the shared one, but only change their own
        $this->actingAs($schoolAdmin)->put(route('admin.engagement.easy-activities.update', $global->id), ['title' => 'Mine now'])->assertForbidden();
        $this->actingAs($schoolAdmin)->delete(route('admin.engagement.easy-activities.delete', $global->id))->assertForbidden();
        $this->actingAs($schoolAdmin)->post(route('admin.engagement.easy-activities.assign', $otherSchools->id), [
            'cid' => $mine->id, 'position' => 'optional', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
        ])->assertNotFound();
        $this->actingAs($schoolAdmin)->post(route('admin.engagement.easy-activities.assign', $global->id), [
            'cid' => $other->id, 'position' => 'optional', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
        ])->assertSessionHasErrors('cid');
    }

    public function test_sending_money_to_a_classmate_completes_it_and_counts_for_the_engagement_badge(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        [$ana] = $this->student($class);
        [$ben, $benAccount] = $this->student($class);
        $activity = $this->assigned($class, 'required', 20);

        $this->send($ana, $benAccount, $ben, 12)->assertSessionHas('success'); // under the minimum
        $this->send($ana, $benAccount, $ben, 25, '  ')->assertSessionHas('success'); // no reference (Concepto)
        $this->assertSame(0, $this->completions($ana));

        $this->send($ana, $benAccount, $ben, 20)->assertSessionHas('success');
        $this->send($ana, $benAccount, $ben, 30)->assertSessionHas('success'); // once is enough
        $this->assertSame(1, $this->completions($ana));
        $this->assertSame(0, $this->completions($ben)); // receiving isn't doing it

        $event = DB::table('calendar_events')->where('classId', $class->id)->where('title', $activity->calendarTitle())->value('id');
        $this->assertDatabaseHas('activity_completions', ['student_id' => $ana->id, 'activity_type' => 'easy_' . $activity->id, 'activity_id' => $event]);

        $record = app(BadgeCalculatorService::class)->calculateForStudent($ana->id, now()->month, now()->year);
        $this->assertSame(1, $record->required_assigned);
        $this->assertSame(1, $record->required_completed);
    }

    public function test_it_isnt_done_by_moving_money_between_own_accounts_or_outside_its_dates(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        [$ana, $anaAccount] = $this->student($class);
        [$ben, $benAccount] = $this->student($class);
        $anaAccount->is_open_emergency_account = 1;
        $anaAccount->emergency_fund_account_number = '7000000001';
        $anaAccount->save();
        $this->assigned($class, 'optional', null, now()->addDays(2)->toDateString(), now()->addDays(9)->toDateString());

        $this->actingAs($ana)->post(route('bank.transfer_store'), ['from_account' => 'Primary Savings Account', 'to' => 'own', 'own_to' => 'emergency', 'type' => 'now', 'amount' => 50, 'memo' => 'Savings'])
            ->assertSessionHas('success');
        $this->send($ana, $benAccount, $ben, 50)->assertSessionHas('success'); // it hasn't started yet
        $this->assertSame(0, $this->completions($ana));

        // another class's activity isn't theirs
        $otherClass = SchoolClass::create(['sid' => 1, 'name' => '9B']);
        $this->travel(3)->days();
        DB::table('mba_position')->update(['cid' => $otherClass->id]);
        $this->send($ana, $benAccount, $ben, 50)->assertSessionHas('success');
        $this->assertSame(0, $this->completions($ana));
    }

    public function test_the_student_sees_what_to_do_in_the_bank_and_then_that_it_is_done(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        [$ana] = $this->student($class);
        [$ben, $benAccount] = $this->student($class);
        $this->assigned($class, 'required', 20);

        $this->actingAs($ana)->get(route('bank.index'))->assertOk()
            ->assertSeeInOrder(['Easy activity', 'Required', 'Counts for your Engagement badge', 'Transfer money to a friend',
                'Ask a classmate for their account number', 'Send them at least Ƶ 20.00', 'under Reference write what the money is for', 'Check the summary and confirm.', 'Make the transfer']);
        // while it runs, the reference is required on the form
        $this->actingAs($ana)->get(route('bank.transfer', ['to' => 'friend']))->assertSee('Transfer money to a friend')
            ->assertSee('Your activity needs it: write what the money is for.')
            ->assertSee('placeholder="e.g. pizza on Friday" required', false);
        $this->actingAs($ana)->get(route('bank.transfer', ['to' => 'bill']))->assertDontSee('Transfer money to a friend');

        $this->send($ana, $benAccount, $ben, 25)->assertSessionHas('success');

        $this->actingAs($ana)->get(route('bank.index'))->assertSee('Done! Your transfer arrived.')->assertDontSee('Ask a classmate');
        $this->actingAs($ana)->get(route('bank.transfer', ['to' => 'friend']))->assertSee('Reference (optional)')
            ->assertDontSee('placeholder="e.g. pizza on Friday" required', false);
    }

    public function test_it_shows_on_the_calendar_home_page_and_board_in_the_students_language(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        [$ana] = $this->student($class);
        [$ben, $benAccount] = $this->student($class);
        $this->assigned($class, 'required');
        $ana->update(['preferred_language' => 'es']);

        $activities = $this->actingAs($ana)->get(route('dashboard'))->viewData('activities');
        $this->assertContains('Haz una transferencia a un amigo o amiga', array_column($activities, 'title'));
        $this->assertContains(route('bank.transfer', ['to' => 'friend']), array_column($activities, 'url'));

        $event = collect($this->actingAs($ana)->getJson(route('education.get-event-by-user'))->json())->firstWhere('title', 'Haz una transferencia a un amigo o amiga');
        $this->assertStringContainsString('Pide a un compañero o compañera de clase', $event['description']);

        $this->actingAs($ana)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSee('Haz una transferencia a un amigo o amiga');
        $this->actingAs($ana)->get(route('bank.index'))->assertSee('Actividad fácil · Obligatoria · Cuenta para tu insignia de Participación');

        $this->send($ana, $benAccount, $ben, 5)->assertSessionHas('success');
        $this->actingAs($ana)->get(route('education.educational_finance_department'))->assertDontSee('Haz una transferencia a un amigo o amiga');
    }

    /** Daniel, 2026-11-03: "esta actividad la hice con mi cuenta uno y no desapareció". */
    public function test_a_done_activity_leaves_the_board_even_after_one_not_done(): void
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        [$student] = $this->student($class);
        foreach (['mf_journey', 'mf_fake'] as $type) {
            DB::table('mba_position')->insert(['user_id' => 1, 'activity_key' => EasyActivity::where('type', $type)->firstOrFail()->key(),
                'sid' => $class->sid, 'cid' => $class->id, 'required' => 1, 'optional' => 0,
                'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString(),
                'created_at' => now(), 'updated_at' => now()]);
        }
        // the second one on the list is done, the first isn't
        [$first, $second] = EasyActivity::runningFor($class->id)->values()->all();
        DB::table('activity_completions')->insert(['student_id' => $student->id, 'activity_id' => 0, 'activity_type' => $second->key(),
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $this->assertSame([$first->key() => false, $second->key() => true],
            app(\App\Services\EasyActivityService::class)->forStudent($student)->mapWithKeys(fn ($activity) => [$activity->key() => $activity->done])->all());
        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSee($first->studentTitle())->assertDontSee($second->studentTitle());
    }

    public function test_every_language_has_the_texts(): void
    {
        foreach (['es', 'fr', 'de'] as $locale) {
            app()->setLocale($locale);
            foreach (['easy_tag', 'easy_required', 'easy_optional', 'easy_badge', 'easy_until', 'easy_done', 'easy_start',
                         'easy_transfer_step1', 'easy_transfer_step2', 'easy_transfer_step2_min', 'easy_transfer_step3'] as $key) {
                $this->assertNotSame(trans('engagement.' . $key, [], 'en'), __('engagement.' . $key), "$locale: $key");
            }
            $this->assertNotSame('Transfer money to a friend', ct('activities', 'Transfer money to a friend'), $locale);
        }
    }
}
