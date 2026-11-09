<?php

namespace Tests\Feature;

use App\Models\EasyActivity;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Services\MoneyFactory\BigMacIndex;
use App\Services\MoneyFactory\RoundTrip;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The rest of the Money Factory's activities (Daniel, 2026-11-02: "haz también
 * lo que falta"): real or fake banknote, the story of money, prices in other
 * currencies (Junior to Intermediate); back from the trip, the Big Mac index,
 * headlines, central bank for a day, print or cut (Advance). Built in, assigned
 * by level in Admin, done from the Education Finance Department board on their
 * own page; done when every answer is right. And bonds as a way money gets in.
 */
class MoneyFactoryActivitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-03 10:00:00');
    }

    private function student(array $class = [], string $level = 'Rookie'): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->firstOrFail()->id]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id] + $class);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }

    /** A built-in activity running for the student's class, as an admin assigns it. */
    private function assign(string $type, User $student): EasyActivity
    {
        $activity = EasyActivity::where('type', $type)->firstOrFail();
        DB::table('mba_position')->insert(['user_id' => 1, 'activity_key' => $activity->key(), 'sid' => '1', 'cid' => $student->grade,
            'required' => 1, 'optional' => 0, 'start_date' => '2026-11-01', 'end_date' => '2026-11-30', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('calendar_events')->insert(['classId' => $student->grade, 'sid' => '1', 'title' => $activity->calendarTitle(), 'position' => 'required',
            'start' => '2026-11-01 00:00:00', 'end' => '2026-12-01 00:00:00', 'created_at' => now(), 'updated_at' => now()]);

        return $activity;
    }

    private function answers(string $type, User $student): array
    {
        return array_column(Activities::questions($type, (int) $student->id), 'answer');
    }

    public function test_every_activity_has_its_page_with_the_students_own_questions(): void
    {
        $student = $this->student();
        foreach (Activities::ALL as $type => $activity) {
            // not on the class calendar, which has no Optional activity this month: done on their own, it counts towards Gold
            $page = $this->actingAs($student)->get(route('education.money-factory.activity', $activity['slug']))->assertOk()
                ->assertSee(EasyActivity::TYPES[$type]['title'])
                ->assertSee('This activity isn\'t on your class\'s calendar: done on your own, it counts this month towards Gold in your Engagement badge.');
            $questions = $page->viewData('questions');
            $this->assertNotEmpty($questions, $type);
            foreach ($questions as $question) {
                // the answers and the explanations stay on the server
                $this->assertArrayNotHasKey('answer', $question);
                $this->assertArrayNotHasKey('why', $question);
            }
            // the same student always gets the same questions
            $this->assertSame($questions, Activities::forPage(Activities::questions($type, (int) $student->id)), $type);
        }

        $this->actingAs($student)->get(route('education.money-factory.activity', 'nothing-here'))->assertNotFound();
    }

    public function test_all_answers_right_finish_an_assigned_activity(): void
    {
        $student = $this->student([], 'Advance');
        $task = $this->assign('mf_headlines', $student);

        $this->actingAs($student)->get(route('education.money-factory.activity', 'headlines'))->assertOk()
            ->assertSee('Read the news: does the Zed go up or down?')->assertDontSee('This activity isn\'t on your class\'s calendar');

        $right = $this->answers('mf_headlines', $student);
        $wrong = $right;
        $wrong[2] = 1 - $wrong[2];
        $tried = $this->actingAs($student)->postJson(route('education.money-factory.activity.answers', 'headlines'), ['answers' => $wrong])->assertOk()
            ->assertJson(['right' => [true, true, false, true, true, true], 'done' => false, 'message' => '1 answer is not right yet. Look again and try once more.']);
        $this->assertNotEmpty($tried->json('why.0')); // why, once it's right
        $this->assertNull($tried->json('why.2'));
        $this->assertDatabaseMissing('activity_completions', ['student_id' => $student->id]);

        $this->actingAs($student)->postJson(route('education.money-factory.activity.answers', 'headlines'), ['answers' => $right])->assertOk()
            ->assertJson(['done' => true, 'message' => 'All right! Activity done.']);
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => $task->key()]);
    }

    public function test_the_story_of_money_is_put_in_order(): void
    {
        $student = $this->student();
        $questions = Activities::questions('mf_history', (int) $student->id);
        $right = $this->answers('mf_history', $student);
        $this->assertSame('order', $questions[0]['kind']);
        $this->assertSame('About 2,600 years ago', collect($questions[0]['cards'])->firstWhere('name', 'The first coins')['when']);

        $wrong = $right;
        $wrong[0] = array_reverse($right[0]);
        $this->actingAs($student)->postJson(route('education.money-factory.activity.answers', 'story-of-money'), ['answers' => $wrong])
            ->assertJson(['right' => [false, true, true, true], 'done' => false]);

        // not assigned to the class: done on their own (Daniel, 2026-11-02: "Oro si hace otras actividades no
        // asignadas, pero si hay opcionales, no") - never instead of the class's own run of it
        $this->actingAs($student)->postJson(route('education.money-factory.activity.answers', 'story-of-money'), ['answers' => $right])
            ->assertJson(['right' => [true, true, true, true], 'done' => false, 'own' => true, 'message' => 'All right! It counts towards Gold as an activity on your own.']);
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_id' => 0, 'activity_type' => 'own:mf_history']);
        $this->assertSame(1, DB::table('activity_completions')->where('student_id', $student->id)->count());

        // with an Optional activity on the class calendar it doesn't count: just "All right!", and the old note
        DB::table('calendar_events')->insert(['classId' => $student->grade, 'sid' => '1', 'title' => 'Wants Plan', 'position' => 'optional',
            'start' => '2026-11-02 00:00:00', 'end' => '2026-11-30 00:00:00', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($student)->postJson(route('education.money-factory.activity.answers', 'story-of-money'), ['answers' => $right])
            ->assertJson(['done' => false, 'message' => 'All right!']);
        $this->actingAs($student)->get(route('education.money-factory.activity', 'story-of-money'))
            ->assertSee('This activity isn\'t on your class\'s calendar right now: you can try it, but it doesn\'t count yet.');
        $this->assertSame(1, DB::table('activity_completions')->where('student_id', $student->id)->count());
    }

    public function test_the_numbers_come_from_the_board_and_the_economist(): void
    {
        // The Economist, July 2026: a Big Mac costs 7.30 CHF, 6.22 USD, 500 JPY; 1 Ƶ = 0.85 € at the ECB's rates
        $this->assertSame(9.34, BigMacIndex::inZeds('CHF'));
        $this->assertSame(6.55, BigMacIndex::inZeds('USD'));
        $this->assertSame(3.35, BigMacIndex::inZeds('JPY'));

        // the exchange office sells 2 % above the board and buys 2 % below
        $this->assertSame([1.0531, 1.0741, 1.032], RoundTrip::prices('USD'));
    }

    public function test_a_hybrid_class_gets_the_task_not_the_why(): void
    {
        $student = $this->student(['default_language' => 'es', 'language_level' => 'hybrid'], 'Advance');
        $this->assign('mf_roundtrip', $student);

        $this->actingAs($student)->get(route('education.money-factory.activity', 'back-from-the-trip'))->assertOk()
            ->assertSee('Casa de cambio')
            ->assertDontSee('Muchas casas de cambio meten su comisión')->assertDontSee('Many exchange offices put their fee');

        $done = $this->actingAs($student)->postJson(route('education.money-factory.activity.answers', 'back-from-the-trip'), ['answers' => $this->answers('mf_roundtrip', $student)])
            ->assertJson(['done' => true]);
        $this->assertSame([], $done->json('why'));
    }

    public function test_the_spanish_pages_use_the_ecbs_words(): void
    {
        $student = $this->student(['default_language' => 'es']);
        $this->assign('mf_fake', $student);

        $page = $this->actingAs($student)->get(route('education.money-factory.activity', 'real-or-fake'))->assertOk()
            ->assertSee('¿Billete auténtico o falso?')->assertSee('«toque, mire y gire»')
            ->assertSee('Mire al trasluz')->assertSee('Gire')->assertSee('Grupo 1')->assertSee('Billete A');
        $this->assertSame('Grupo 1: ¿qué billete es falso?', $page->viewData('questions')[0]['text']);

        // the board of the Education Finance Department sends the student there
        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSee('¿Billete auténtico o falso?')->assertSee(route('education.money-factory.activity', 'real-or-fake'), false);
        // and the Money Factory lists it
        $this->actingAs($student)->get(route('education.money-factory'))->assertOk()
            ->assertSee('Tus actividades de la Fábrica de dinero')->assertSee('¿Billete auténtico o falso?');
    }

    public function test_an_admin_assigns_them_by_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $advance = GradeLevel::where('name', 'Advance')->firstOrFail();
        $junior = GradeLevel::where('name', 'Junior')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.money-factory', ['level' => $advance->id]))->assertOk()
            ->assertSee('Central bank for a day')->assertSee('The Big Mac index')->assertSee('Print money or cut spending?')
            ->assertSee('The student runs the Central Bank of Zedland for four years')
            ->assertDontSee('Real or fake banknote?');
        $this->actingAs($admin)->get(route('admin.money-factory', ['level' => $junior->id]))->assertOk()
            ->assertSee('Real or fake banknote?')->assertSee('The story of money')->assertSee('How much is it in other currencies?')
            ->assertDontSee('Central bank for a day');

        // built in: assigned, never deleted; its name stays (its translations hang on it), its levels change
        $bigMac = EasyActivity::where('type', 'mf_bigmac')->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.engagement.easy-activities.delete', $bigMac->id))->assertStatus(422);
        $this->actingAs($admin)->put(route('admin.engagement.easy-activities.update', $bigMac->id), ['title' => 'Burgers', 'levels' => [$junior->id]])->assertRedirect();
        $this->assertSame('The Big Mac index', $bigMac->fresh()->title);
        $this->assertEquals([$junior->id], $bigMac->fresh()->levels);
    }

    public function test_bonds_are_a_way_money_gets_into_the_economy(): void
    {
        // Daniel, 2026-11-02: "¿el dinero no entra al mercado a través de los bonos?"
        $this->actingAs($this->student([], 'Advance'))->get(route('education.money-factory.enter'))->assertOk()
            ->assertSee('Bonds')->assertSee('buys bonds')
            ->assertSee('People and funds pay with money that already exists')
            ->assertSee('Banks pay with new money they create')
            ->assertSee('quantitative easing')->assertSee('1,850 billion euros')
            ->assertSee('never straight from the government');
    }
}
