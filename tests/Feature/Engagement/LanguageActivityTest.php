<?php

namespace Tests\Feature\Engagement;

use App\Models\EasyActivity;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\BadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-29: "For that [hybrid classes], I need to create different
 * activities. Create in Admin a section for these activities, also, the
 * transfer, in a separate admin activity under Engagement - Easy Activities."
 */
class LanguageActivityTest extends TestCase
{
    use RefreshDatabase;

    private const PAIRS = "el ahorro = savings\nel alquiler = rent\nla nómina = salary\nel saldo = balance";

    private function admin(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    private function wordMatch(SchoolClass $class, string $position = 'required'): EasyActivity
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.engagement.easy-activities.store'), [
            'type' => 'word_match', 'title' => 'Bank words', 'language' => 'es', 'pairs' => self::PAIRS, 'instructions' => 'Words you see in the bank.',
        ])->assertSessionHasNoErrors();
        $activity = EasyActivity::where('title', 'Bank words')->firstOrFail();
        $this->actingAs($admin)->withSession(['selected_school' => $class->sid])->post(route('admin.engagement.easy-activities.assign', $activity->id), [
            'cid' => $class->id, 'position' => $position, 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString(),
        ])->assertSessionHasNoErrors();

        return $activity;
    }

    public function test_the_admin_has_two_sections_one_for_the_transfer_and_one_for_language_activities(): void
    {
        $admin = $this->admin();
        EasyActivity::create(['type' => 'bank_transfer', 'title' => 'Transfer money to a friend']);
        EasyActivity::create(['type' => 'word_match', 'title' => 'Shop words', 'language' => 'fr', 'content' => ['pairs' => [['le pain', 'bread'], ['le lait', 'milk'], ['le prix', 'price']]]]);

        $this->actingAs($admin)->get(route('admin.engagement.easy-activities'))->assertOk()
            ->assertSee('Engagement: Transfer money to a friend')
            ->assertSee('Transfer money to a friend')->assertDontSee('Shop words')
            ->assertSee('<span class="navTxt">Transfer money to a friend</span>', false) // the menu
            ->assertDontSee('Easy Activity')
            ->assertSee('Language activities (hybrid)'); // in each level of the menu by level (2026-11-02)

        $this->actingAs($admin)->get(route('admin.hybrid.activities'))->assertOk()
            ->assertSee('Hybrid Classes: Language Activities')
            ->assertSee('Language level: Hybrid')
            ->assertSee('Shop words')->assertSee('le pain = bread')
            ->assertSee('Cuenta corriente = Current account', false) // the bank's own words to start from
            ->assertDontSee('Minimum amount');
    }

    public function test_a_word_match_needs_its_language_and_three_to_twenty_clear_pairs(): void
    {
        $admin = $this->admin();
        $post = fn (array $fields) => $this->actingAs($admin)->post(route('admin.engagement.easy-activities.store'), $fields + ['type' => 'word_match', 'title' => 'Words ' . uniqid()]);

        $post(['language' => 'es', 'pairs' => "el pan = bread\nla leche = milk"])->assertSessionHasErrors('pairs');
        $post(['pairs' => self::PAIRS])->assertSessionHasErrors('language');
        $post(['language' => 'es', 'pairs' => "el pan = bread\nla barra = bread\nla leche = milk"])->assertSessionHasErrors('pairs');
        $post(['language' => 'es', 'pairs' => self::PAIRS . "\nnot a pair"])->assertSessionHasNoErrors();

        $this->assertSame([['el ahorro', 'savings'], ['el alquiler', 'rent'], ['la nómina', 'salary'], ['el saldo', 'balance']],
            EasyActivity::where('type', 'word_match')->sole()->pairs());
    }

    public function test_matching_every_word_completes_it_for_the_engagement_badge(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A', 'default_language' => 'es', 'language_level' => 'hybrid']);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
        $activity = $this->wordMatch($class);

        $this->actingAs($student)->get(route('easy.words', $activity))->assertOk()
            ->assertSee('el alquiler')->assertSee('salary')->assertSee('Words you see in the bank.');

        // a wrong answer doesn't count
        $this->actingAs($student)->postJson(route('easy.words.done', $activity), ['answers' => ['rent', 'savings', 'salary', 'balance']])
            ->assertOk()->assertJson(['ok' => false]);
        $this->assertSame(0, DB::table('activity_completions')->where('student_id', $student->id)->count());

        $this->actingAs($student)->postJson(route('easy.words.done', $activity), ['answers' => ['savings', 'rent', 'salary', 'balance']])
            ->assertOk()->assertJson(['ok' => true, 'counted' => true]);
        $event = DB::table('calendar_events')->where('classId', $class->id)->where('title', 'easy-Bank words')->value('id');
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'easy_' . $activity->id, 'activity_id' => $event]);

        $record = app(BadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);
        $this->assertSame(1, $record->required_completed);

        // it leaves the Education board once done
        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertDontSee(route('easy.words', $activity), false);
    }

    public function test_outside_its_class_it_is_practice_and_doesnt_count(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        $other = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => SchoolClass::create(['sid' => 1, 'name' => '9B'])->id]);
        $activity = $this->wordMatch($class, 'optional');

        $this->actingAs($other)->get(route('easy.words', $activity))->assertOk()->assertSee('you can practise, but it doesn’t count');
        $this->actingAs($other)->postJson(route('easy.words.done', $activity), ['answers' => ['savings', 'rent', 'salary', 'balance']])
            ->assertJson(['ok' => true, 'counted' => false]);
        $this->assertSame(0, DB::table('activity_completions')->count());
    }

    public function test_the_student_finds_it_on_the_board_and_the_calendar_in_their_language(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A', 'language_mode' => 'locked', 'default_language' => 'de']);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
        $activity = $this->wordMatch($class);

        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSee('Bankwörter')->assertSee(route('easy.words', $activity), false);
        $activities = $this->actingAs($student)->get(route('dashboard'))->viewData('activities');
        $this->assertContains(route('easy.words', $activity), array_column($activities, 'url'));
        $this->actingAs($student)->get(route('easy.words', $activity))->assertSee('Ordne jedem Wort seine Bedeutung auf Englisch zu.');
    }

    public function test_every_language_has_the_word_match_texts(): void
    {
        foreach (['es', 'fr', 'de'] as $locale) {
            app()->setLocale($locale);
            foreach (['words_intro', 'words_how', 'words_word', 'words_meaning', 'words_try_again', 'words_pick_first', 'words_done', 'words_counted', 'words_already', 'words_practice'] as $key) {
                $this->assertNotSame(trans('engagement.' . $key, [], 'en'), __('engagement.' . $key), "$locale: $key");
            }
        }
        app()->setLocale('en');
    }
}
