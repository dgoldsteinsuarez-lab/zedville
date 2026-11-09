<?php

namespace Tests\Feature\Finhero;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-28, on Task 3's results: "Fix the glitch" (the salary bar
 * rode along over the results card) "then put a finished button down below
 * the activity".
 */
class FinheroTaskFinishedTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $language): User
    {
        $gradeLevel = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $gradeLevel->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => 'locked', 'default_language' => $language,
        ]);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    /** "Finish", not "Finished" (Daniel, 2026-10-28: "what is more logical") - a button says what it does. */
    public function test_every_task_ends_with_a_finish_button_back_to_the_department(): void
    {
        $student = $this->student('en');
        $department = route('education.educational_finance_department');

        foreach ([1, 2, 3, 4] as $n) {
            $this->actingAs($student)->get('/finhero/task-' . $n)->assertOk()
                // inside the results, which only show once every question is done
                ->assertSeeInOrder(['id="completion"', 'class="zv-finish"', 'href="' . $department . '" class="themeBtn zv-finish-btn">Finish</a>'], false)
                // the salary / statement bar stops riding along once finished
                ->assertSee('#game-screen:has(#completion:not(.hidden)) :is(.reference, .bank-sticky) { position: static; }', false);
        }
    }

    /**
     * Daniel, 2026-10-29, on Task 3's first screen: "Fit in the black rectangle the infos to be able
     * to remove the scrolling up and down ... put the box more up. Adjust the 'back' button" - the
     * card at the top, Back and the task's name beside it (finhero/_intro_fit), in every task.
     */
    public function test_every_task_starts_with_its_card_at_the_top(): void
    {
        $student = $this->student('en');

        foreach ([1, 2, 3, 4] as $n) {
            $this->actingAs($student)->get('/finhero/task-' . $n)->assertOk()
                ->assertSee('#intro-screen.screen { min-height: 0; align-items: flex-start; padding: 0; }', false)
                ->assertSee("main:has(#intro-screen:not(.hidden)) > .zv-page-back { position: absolute; top: 16px; left: 32px; }", false)
                ->assertSeeInOrder(['<div class="zv-task-head flex items-center justify-between pb-6">', 'Task ' . $n, 'id="intro-screen"', 'id="start-button"'], false);
        }
    }

    public function test_the_button_speaks_the_class_language(): void
    {
        foreach (['es' => 'Terminar', 'fr' => 'Terminer', 'de' => 'Fertig'] as $language => $label) {
            $this->actingAs($this->student($language))->get('/finhero/task-3')->assertOk()
                ->assertSee('class="themeBtn zv-finish-btn">' . $label . '</a>', false);
        }
    }

    public function test_task_3_heading_is_in_the_page_not_above_it(): void
    {
        // it used to sit in the styles stack, so the browser drew it above the whole layout
        $this->actingAs($this->student('en'))->get('/finhero/task-3')->assertOk()
            ->assertSeeInOrder(['</head>', '<main', 'Task 3', 'id="loading-screen"'], false);
    }
}
