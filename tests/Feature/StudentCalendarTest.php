<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\StudentCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The student calendar, option A (Daniel, 2026-11-04: "Make option A for the
 * calendar"): a month, a week and a day without hours or scrolling.
 */
class StudentCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-03 10:00');
    }

    private function student(array $class = []): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()] + $class);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }

    /** Like the admin screens: the calendar keeps the day after the last one. */
    private function event(User $student, string $title, string $start, string $end, string $position = 'required'): int
    {
        return DB::table('calendar_events')->insertGetId([
            'sid' => '1', 'classId' => $student->grade, 'title' => $title, 'position' => $position, 'repeatActivity' => '0',
            'start' => $start, 'end' => $end, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function completed(User $student, int $eventId, string $when): void
    {
        DB::table('activity_completions')->insert(['student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'mba',
            'completed_at' => $when, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_each_activity_once_with_its_last_day_and_whether_it_is_done(): void
    {
        $student = $this->student();
        $match = $this->event($student, 'Needs, Wants & Savings Match', '2026-11-03', '2026-11-11', 'optional');
        $this->event($student, 'Emergency Fund', '2026-11-01', '2026-11-08');
        $this->event($student, 'Emergency Fund', '2026-11-01', '2026-11-08');   // put on the calendar twice
        $task = $this->event($student, 'task-Budget Rule', '2026-11-02', '2026-11-22');
        $this->completed($student, $match, '2026-11-03 09:00');
        $this->completed($student, $task, '2026-10-20 09:00');                 // before this run: not done
        BankAccount::create(['student_id' => $student->id, 'sid' => '1', 'student_name' => $student->name, 'student_email' => $student->email,
            'card_pin' => '1234'])->forceFill(['is_open_emergency_account' => 1])->save();

        $items = collect(StudentCalendar::activities($student))->keyBy('title');

        $this->assertCount(3, $items);
        $this->assertSame(['title' => 'Needs, Wants & Savings Match', 'required' => false, 'start' => '2026-11-03', 'last' => '2026-11-10',
            'done' => true, 'done_on' => '2026-11-03', 'url' => route('education.index')], $items['Needs, Wants & Savings Match']);
        $this->assertSame('2026-11-07', $items['Emergency Fund']['last']);
        $this->assertTrue($items['Emergency Fund']['done']);   // the fund is open
        $this->assertNull($items['Emergency Fund']['done_on']);
        $this->assertTrue($items['Budget Rule']['required']);
        $this->assertFalse($items['Budget Rule']['done']);
    }

    public function test_only_the_students_own_class_and_nothing_without_a_class(): void
    {
        $student = $this->student();
        $other = $this->student();
        $this->event($other, 'Emergency Fund', '2026-11-01', '2026-11-08');

        $this->assertSame([], StudentCalendar::activities($student));
        $this->assertSame([], StudentCalendar::activities(User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => null])));
    }

    public function test_the_page_has_the_three_views_without_hours(): void
    {
        $student = $this->student();
        $this->event($student, 'Needs, Wants & Savings Match', '2026-11-03', '2026-11-11');

        $response = $this->actingAs($student)->get(route('education.calendar'))->assertOk();

        $response->assertSee('x-data="zvCalendar(window.zvCalendarConfig)"', false)
            ->assertSeeInOrder(['>Month<', '>Week<', '>Day<'], false)
            ->assertSee('Each activity shows on its last day')
            ->assertDontSee('fullcalendar', false)
            ->assertDontSee('timeGrid', false);
        $config = $response->viewData('calendar');
        $this->assertSame(['locale' => 'en', 'today' => '2026-11-03'], array_slice($config, 0, 2));
        $this->assertSame('Needs, Wants & Savings Match', $config['activities'][0]['title']);
        $this->assertEquals(50, $response->viewData('fineAmount'));
    }

    public function test_in_spanish_and_in_a_hybrid_class(): void
    {
        $student = $this->student(['default_language' => 'es']);

        $this->actingAs($student)->get(route('education.calendar'))->assertOk()
            ->assertSeeInOrder(['>Mes<', '>Semana<', '>Día<'], false)
            ->assertSee('Cada actividad sale en su último día')
            ->assertSee('Si no las haces a tiempo: multa de');

        // inform, don't explain: the hint goes, the facts stay
        $hybrid = $this->student(['default_language' => 'es', 'language_level' => 'hybrid']);
        $this->actingAs($hybrid)->get(route('education.calendar'))->assertOk()
            ->assertDontSee('class="zvcal-hint"', false)
            ->assertSee('Si no las haces a tiempo: multa de');
    }
}
