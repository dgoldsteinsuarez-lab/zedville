<?php

namespace Tests\Feature\Finhero;

use App\Models\FinheroLibraryModule;
use App\Models\FinheroLibraryOption;
use App\Models\FinheroLibraryQuestion;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The student Library page: an assigned module's lesson and quiz, graded on
 * the server. Points follow the FinHero Point Values doc - 1 point per
 * correct answer on the first attempt only, stored under the module's id.
 */
class LibraryActivityTest extends TestCase
{
    use RefreshDatabase;

    private function studentWithLibraryActivity(): array
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        $module = FinheroLibraryModule::create([
            'id' => 1, 'module_key' => 'task_1', 'title' => 'Introduction to Budget', 'icon' => '🐷', 'sort_order' => 2,
            'lesson' => [['title' => "What's a Budget?", 'lines' => ['A budget is a plan for your money.', '• Know your money']]],
        ]);

        DB::table('finhero_activity_registry')->insert([
            'activity_key' => 'task_1', 'activity_name' => 'Introduction to Budget', 'type' => 'library',
            'sid' => 1, 'cid' => $class->id, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $question = FinheroLibraryQuestion::create(['module_id' => $module->id, 'question_text' => 'What is a budget?', 'sort_order' => 1]);
        $correct = FinheroLibraryOption::create(['question_id' => $question->id, 'option_text' => 'A plan for your money', 'is_correct' => true, 'sort_order' => 1]);
        $wrong = FinheroLibraryOption::create(['question_id' => $question->id, 'option_text' => 'A type of bank account', 'is_correct' => false, 'sort_order' => 2]);

        return [$student, $question, $correct, $wrong];
    }

    public function test_a_student_sees_the_lesson_and_the_quiz_of_their_assigned_module(): void
    {
        [$student] = $this->studentWithLibraryActivity();

        $response = $this->actingAs($student)->get(route('library.page', 'task-1'));

        $response->assertOk();
        $response->assertSee('Introduction to Budget');
        $response->assertSee('A budget is a plan for your money.');
        $response->assertSee('<li>Know your money</li>', false);
        $response->assertSee('What is a budget?');
    }

    public function test_a_perfect_first_attempt_completes_the_activity_and_earns_points_under_the_module_id(): void
    {
        [$student, $question, $correctOption] = $this->studentWithLibraryActivity();

        $response = $this->actingAs($student)->post(route('library.submit', 'task-1'), [
            'answers' => [$question->id => $correctOption->id],
        ]);

        // Daniel, 2026-10-26: an acknowledgement and the result after
        // submitting - on the module's page, reached by a redirect so a
        // reload never sends the answers again.
        $response->assertRedirect(route('library.page', 'task-1'));
        $this->actingAs($student)->get(route('library.page', 'task-1'))->assertOk()
            ->assertSee('Your answers have been submitted!')
            ->assertSee('1 of 1 correct')
            ->assertSee('Perfect! You got every question right.')
            ->assertSee('You earned 1 FinHero point.')
            ->assertSee('Correct · A plan for your money')
            ->assertDontSee('Start the quiz');

        // Opened again later: the lesson and the quiz, not the old result.
        $this->actingAs($student)->get(route('library.page', 'task-1'))->assertSee('Start the quiz')->assertDontSee('Your answers have been submitted!');
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'library_task_1']);
        $this->assertDatabaseHas('finhero_student_points', [
            'student_id' => $student->id, 'source_type' => 'library', 'source_key' => '1', 'points_earned' => 1,
        ]);
    }

    public function test_only_the_first_attempt_earns_points(): void
    {
        [$student, $question, $correctOption, $wrongOption] = $this->studentWithLibraryActivity();

        $this->actingAs($student)->post(route('library.submit', 'task-1'), ['answers' => [$question->id => $wrongOption->id]]);
        $this->travel(1)->days();
        $this->actingAs($student)->post(route('library.submit', 'task-1'), ['answers' => [$question->id => $correctOption->id]]);

        // Finishing the first attempt completes the activity; the retake earns nothing.
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'library_task_1']);
        $this->assertSame(0, (int) DB::table('finhero_student_points')
            ->where('student_id', $student->id)->where('source_type', 'library')->sum('points_earned'));
    }

    public function test_a_student_outside_the_assigned_class_cannot_open_it(): void
    {
        $this->studentWithLibraryActivity();
        $otherLevel = GradeLevel::create(['name' => 'Intermediate ' . uniqid(), 'sort_order' => 99]);
        $otherGrade = Grade::create(['sid' => 1, 'name' => 'Other Grade', 'grade_level_id' => $otherLevel->id]);
        $otherClass = SchoolClass::create(['sid' => 1, 'grade_id' => $otherGrade->id, 'name' => 'Other Class']);
        $outsider = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $otherClass->id]);

        $this->actingAs($outsider)->get(route('library.page', 'task-1'))->assertNotFound();
    }

    /**
     * Daniel, 2026-10-21: "they get points and this should also be credited
     * in the badge, in both."
     */
    public function test_library_points_count_in_the_finhero_badge_when_the_module_is_assigned(): void
    {
        [$student, $question, $correctOption] = $this->studentWithLibraryActivity();

        $this->actingAs($student)->post(route('library.submit', 'task-1'), ['answers' => [$question->id => $correctOption->id]]);

        $record = app(\App\Services\FinheroBadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);

        $this->assertSame(1, (int) $record->library_pts);
        // 10 Daily Quiz + 10 for the assigned Library module.
        $this->assertSame(20, (int) $record->total_available);
    }

    public function test_without_an_assigned_module_library_is_left_out_of_the_finhero_total(): void
    {
        $student = User::factory()->create(['role' => 4, 'sid' => 1]);

        $record = app(\App\Services\FinheroBadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);

        $this->assertSame(0, (int) $record->library_pts);
        $this->assertSame(10, (int) $record->total_available);
    }

    public function test_finishing_a_required_module_counts_as_a_completed_required_activity_for_the_engagement_badge(): void
    {
        [$student, $question, , $wrongOption] = $this->studentWithLibraryActivity();
        $eventId = DB::table('calendar_events')->insertGetId([
            'sid' => '1', 'classId' => $student->grade, 'title' => 'library-Introduction to Budget',
            'position' => 'required', 'start' => now(), 'end' => now()->addDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Completed even with a wrong answer: finishing is what counts here,
        // accuracy is what the FinHero points reward.
        $this->actingAs($student)->post(route('library.submit', 'task-1'), ['answers' => [$question->id => $wrongOption->id]]);

        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_id' => $eventId]);
        $record = app(\App\Services\BadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);
        $this->assertSame(1, (int) $record->required_assigned);
        $this->assertSame(1, (int) $record->required_completed);
    }

    public function test_the_library_popup_gets_the_result_as_json(): void
    {
        [$student, $question, $correctOption] = $this->studentWithLibraryActivity();

        $this->actingAs($student)
            ->postJson(route('library.submit', 'task-1'), ['answers' => [$question->id => $correctOption->id]])
            ->assertOk()
            ->assertJson(['correct' => 1, 'total' => 1, 'completed' => true, 'points' => 1, 'first_attempt' => true]);
    }

    /** Daniel, 2026-10-28: "move up and start the lesson box" - Back beside it, not above it. */
    public function test_the_lesson_starts_at_the_top_with_back_beside_it(): void
    {
        [$student] = $this->studentWithLibraryActivity();

        $this->actingAs($student)->get(route('library.page', 'task-1'))->assertOk()
            ->assertSeeInOrder(['class="zl-page"', 'class="zl-back"', 'zvBackBtn', 'class="zl-wrap"', 'id="zlCard"'], false)
            ->assertDontSee('zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-5', false); // not the layout's Back above it
    }

    /**
     * Daniel, 2026-10-29: "squeeze this introduction to budget box ... so I would not need to
     * scroll", Start at its bottom right: the lesson in two columns that end level.
     */
    public function test_the_lesson_is_in_two_columns_with_start_under_them(): void
    {
        [$student] = $this->studentWithLibraryActivity();

        $this->actingAs($student)->get(route('library.page', 'task-1'))->assertOk()
            ->assertSeeInOrder(['<div class="zl-lesson">', '<div class="zl-part">', "What&#039;s a Budget?", '<li>Know your money</li>', '</ul>', '</div>', 'class="zl-start"', 'data-go="1"'], false)
            ->assertSee('.zl-lesson { columns: 2; column-gap: 56px; column-rule: 1px solid #E6ECEA; }', false); // two columns ending level
    }

    /**
     * Daniel, 2026-10-29: "keep the context of 1st page on the left side ... make it all fit ...
     * Then each question" beside it - the lesson (step 0) stays shown while answering, its
     * name over it, its text made just small enough to fit. 2026-11-02: "Show also the notes
     * (1st page) when its minimized" - half and half in a smaller window, under the question
     * on a phone, never hidden.
     */
    public function test_the_lesson_stays_beside_the_questions(): void
    {
        [$student] = $this->studentWithLibraryActivity();

        $this->actingAs($student)->get(route('library.page', 'task-1'))->assertOk()
            ->assertSeeInOrder(['<section class="zl-step" data-step>', '<p class="zl-ref-title" aria-hidden="true">', 'Introduction to Budget</p>', '<div class="zl-lesson">'], false)
            ->assertSee('.zl-card.is-quiz #zlQuiz { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);', false)
            ->assertSee('.zl-card.is-quiz #zlQuiz { grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);', false)
            ->assertSee('.zl-card.is-quiz #zlQuiz > .zl-step:first-of-type { order: 1;', false)
            ->assertDontSee('.zl-card.is-quiz #zlQuiz > .zl-step:first-of-type { display: none; }', false)
            ->assertSee('step.hidden = i !== current && !(i === 0 && current > 0);', false)
            ->assertSee('for (var size = 15; size >= 12; size -= 0.5)', false); // never under 12px
    }

    /** "change the icon of a head of the pig to a piggy bank instead" - there is no piggy bank emoji, so it's drawn. */
    public function test_introduction_to_budget_shows_a_piggy_bank(): void
    {
        [$student] = $this->studentWithLibraryActivity();
        FinheroLibraryModule::where('module_key', 'task_1')->update(['icon' => 'piggy-bank']);

        $this->actingAs($student)->get(route('library.page', 'task-1'))->assertOk()
            ->assertSee('<span class="zl-icon" aria-hidden="true"><svg class="zv-drawn-icon"', false)
            ->assertDontSee('🐷');
        // the department's Library pop-up gets it too
        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSee('zv-drawn-icon', false);
    }

    public function test_module_icons_are_an_escaped_emoji_or_a_drawn_one(): void
    {
        $this->assertStringStartsWith('<svg class="zv-drawn-icon"', \App\Support\ModuleIcon::html('piggy-bank'));
        $this->assertSame('🏦', \App\Support\ModuleIcon::html('🏦'));
        $this->assertSame('&lt;b&gt;', \App\Support\ModuleIcon::html('<b>'));
        $this->assertSame('', \App\Support\ModuleIcon::html(null));
    }
}
