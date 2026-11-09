<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\CalendarTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Calendar titles carry internal prefixes ("task-", "library-", "exam-")
 * that the completion matching relies on. Students saw them as-is on the
 * dashboard ("task-Classify Your Salary") and in their calendar.
 */
class CalendarTitleTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_prefixes_are_hidden_from_students(): void
    {
        $this->assertSame('Classify Your Salary', CalendarTitle::forStudent('task-Classify Your Salary'));
        $this->assertSame('Introduction to Budget', CalendarTitle::forStudent('library-Introduction to Budget'));
        $this->assertSame('Final Exam', CalendarTitle::forStudent('exam-Final Exam'));
        $this->assertSame('Field trip', CalendarTitle::forStudent('Field trip'));
        $this->assertSame('', CalendarTitle::forStudent(null));
    }

    public function test_built_in_activities_show_in_the_students_language(): void
    {
        $this->assertSame('Needs, Wants & Savings Match', CalendarTitle::forStudent('Needs, Wants & Savings Match'));

        app()->setLocale('es');

        $this->assertSame('Necesidades, deseos y ahorro', CalendarTitle::forStudent('Needs, Wants & Savings Match'));
        $this->assertSame('Fondo de emergencia', CalendarTitle::forStudent('Emergency Fund'));
        $this->assertSame('Actividad de gasto alto', CalendarTitle::forStudent('Spending budget activity'));
        $this->assertSame('Actividad de gasto bajo', CalendarTitle::forStudent('Low spending activity'));
    }

    /** 2026-10-30: two to-dos on the German home page were still English - a Library module and FinHero task 3. */
    public function test_library_modules_and_the_budget_rule_task_are_translated(): void
    {
        app()->setLocale('de');
        $this->assertSame('Sparen – warum eigentlich?', CalendarTitle::forStudent('library-Savings — Why Save?'));
        $this->assertSame('Einführung ins Budget', CalendarTitle::forStudent('library-Introduction to Budget'));
        $this->assertSame('Die 50/30/20-Budgetregel', CalendarTitle::forStudent('task-Budget Rule'));

        app()->setLocale('es');
        $this->assertSame('La regla del presupuesto 50/30/20', CalendarTitle::forStudent('task-Budget Rule'));
        app()->setLocale('fr');
        $this->assertSame('La règle budgétaire 50/30/20', CalendarTitle::forStudent('task-Budget Rule'));
    }

    public function test_the_dashboard_and_the_calendar_feed_show_the_clean_title(): void
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        DB::table('calendar_events')->insert([
            'sid' => 1, 'classId' => $class->id, 'title' => 'task-Classify Your Salary', 'position' => 'required',
            'start' => now()->toDateString(), 'end' => now()->addWeek()->toDateString(),
            'repeatActivity' => '0', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Classify Your Salary')
            ->assertDontSee('task-Classify Your Salary');

        $this->actingAs($student)->getJson('/education/get-event-by-user')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Classify Your Salary']);

        // The stored title is unchanged - completions still match on it.
        $this->assertDatabaseHas('calendar_events', ['title' => 'task-Classify Your Salary']);
    }
}
