<?php

namespace Tests\Feature\Finhero;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: "build the language for beginners activities" - the
 * four FinHero tasks and the Library page show in the class's language,
 * and a beginner class gets the simpler instructions.
 */
class FinheroTaskLanguageTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $language, string $level = 'standard'): User
    {
        $gradeLevel = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $gradeLevel->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => 'locked', 'default_language' => $language, 'language_level' => $level,
        ]);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    public function test_the_four_tasks_show_in_german(): void
    {
        $student = $this->student('de');

        $this->actingAs($student)->get('/finhero/task-1')->assertOk()
            ->assertSee('Ordne dein Gehalt ein!')
            ->assertSee('Niedriges Einkommen')   // income levels, in the page script
            ->assertDontSee('Classify Your Salary');
        $this->actingAs($student)->get('/finhero/task-2')->assertOk()
            ->assertSee('Aufgabe 2: Budget-Rechnungen')
            ->assertDontSee('Budget Calculations');
        $this->actingAs($student)->get('/finhero/task-3')->assertOk()
            ->assertSee('Die 50/30/20-Budgetregel')
            ->assertSee('Aufgabe 3');
        $this->actingAs($student)->get('/finhero/task-4')->assertOk()
            ->assertSee('Aufgabe 4: Budget-Realitätscheck')
            ->assertDontSee('Budget Reality Check');
    }

    public function test_the_tasks_are_unchanged_in_english(): void
    {
        $student = $this->student('en');

        $this->actingAs($student)->get('/finhero/task-1')->assertOk()
            ->assertSee('Classify Your Salary!')
            ->assertSee('Low Income');
        $this->actingAs($student)->get('/finhero/task-4')->assertOk()
            ->assertSee('Task 4: Budget Reality Check');
    }

    public function test_a_beginner_class_gets_the_simpler_instructions(): void
    {
        $student = $this->student('es', 'beginner');

        $this->actingAs($student)->get('/finhero/task-1')->assertOk()
            ->assertSee('Tienes tu extracto bancario de este mes.')                     // es_easy
            ->assertDontSee('Acabas de recibir tu extracto bancario mensual.')         // es (replaced)
            ->assertSee('Tarea 1: Clasifica tu sueldo');                               // not simplified -> es
    }
}
