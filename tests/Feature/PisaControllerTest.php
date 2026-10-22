<?php

namespace Tests\Feature;

use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\PisaProficiencyLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "in PISA, you need to explain the student what PISA
 * is and when the exam is taken... use the PISA terminologie" + "divide in
 * the levels you search for." Dedicated student PISA experience,
 * replacing the old academy.pisa shortcut (see AcademyControllerTest for
 * the removed redirect test).
 *
 * Revised later the same day: "for PISA i don't need to divide in
 * Zedville levels, only the PISA levels... independent from if a student
 * is Junior, Rookie or else, they will have the same activities." PISA
 * Practice is one single global category now, not one per Grade Level -
 * every `pisaCategory()` lookup below reflects that.
 */
class PisaControllerTest extends TestCase
{
    use RefreshDatabase;

    private function studentAtLevel(string $levelName, int $sid = 1): User
    {
        $level = GradeLevel::where('name', $levelName)->first();
        $grade = Grade::create(['sid' => $sid, 'name' => 'Test Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => $sid, 'grade_id' => $grade->id, 'name' => 'Test Class ' . uniqid()]);

        return User::factory()->create(['role' => 4, 'sid' => $sid, 'grade' => $class->id]);
    }

    private function pisaCategory(): CurriculumCategory
    {
        return CurriculumCategory::where('is_pisa_practice', true)->first();
    }

    private function completeQuiz(User $student, CurriculumItem $item, int $score): void
    {
        CurriculumAttempt::create([
            'item_id' => $item->id, 'student_id' => $student->id,
            'score' => $score, 'total_questions' => 1, 'completed_at' => now(),
        ]);
    }

    public function test_the_explainer_page_uses_real_pisa_terminology(): void
    {
        $student = $this->studentAtLevel('Rookie');

        $response = $this->actingAs($student)->get(route('pisa.index'));

        $response->assertOk();
        $response->assertSee('Programme for International Student Assessment');
        $response->assertSee('Financial Literacy Assessment');
        $response->assertSee('Grade 10');
    }

    public function test_the_index_page_lists_the_seeded_oecd_levels_with_activity_counts(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $level = PisaProficiencyLevel::where('name', 'Level 2')->first();
        CurriculumItem::create([
            'category_id' => $this->pisaCategory()->id, 'title' => 'Needs vs wants', 'type' => 'quiz',
            'pisa_proficiency_level_id' => $level->id, 'sort_order' => 1, 'is_active' => true,
        ]);

        $response = $this->actingAs($student)->get(route('pisa.index'));

        $response->assertOk();
        $response->assertSee('Below Level 1');
        $response->assertSee('Level 2');
        $response->assertSee('1 activity');
    }

    /**
     * Daniel, 2026-10-21: "independent from if a student is Junior,
     * Rookie or else, they will have the same activities because its for
     * PISA and they just have these." Students at two different Grade
     * Levels both see the exact same PISA item.
     */
    public function test_students_at_different_grade_levels_see_the_same_pisa_activity(): void
    {
        $rookie = $this->studentAtLevel('Rookie');
        $intermediate = $this->studentAtLevel('Intermediate');
        $pisaLevel = PisaProficiencyLevel::where('name', 'Level 1')->first();

        $item = CurriculumItem::create([
            'category_id' => $this->pisaCategory()->id, 'title' => 'Shared PISA activity', 'type' => 'quiz',
            'pisa_proficiency_level_id' => $pisaLevel->id, 'sort_order' => 1, 'is_active' => true,
        ]);

        $rookieResponse = $this->actingAs($rookie)->get(route('pisa.level', $pisaLevel));
        $intermediateResponse = $this->actingAs($intermediate)->get(route('pisa.level', $pisaLevel));

        $rookieResponse->assertOk();
        $rookieResponse->assertSee('Shared PISA activity');
        $intermediateResponse->assertOk();
        $intermediateResponse->assertSee('Shared PISA activity');
        // Both reach the exact same item/grading flow, not two copies.
        $rookieResponse->assertSee(route('academy.item', $item), false);
        $intermediateResponse->assertSee(route('academy.item', $item), false);
    }

    public function test_an_item_opened_from_pisa_links_back_to_pisa_not_the_generic_academy_category(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $pisaLevel = PisaProficiencyLevel::where('name', 'Level 1')->first();
        $category = $this->pisaCategory();
        $item = CurriculumItem::create([
            'category_id' => $category->id, 'title' => 'PISA item', 'type' => 'content',
            'pisa_proficiency_level_id' => $pisaLevel->id, 'sort_order' => 1, 'is_active' => true,
        ]);

        $response = $this->actingAs($student)->get(route('academy.item', $item));

        $response->assertOk();
        $response->assertSee(route('pisa.level', $pisaLevel), false);
        $response->assertDontSee(route('academy.category', $category), false);
    }

    /**
     * Daniel, 2026-10-21: "a Game Option, where they play alone or with
     * other students of the same grade" - confirmed as an async
     * leaderboard. Scoped to the viewer's own school only now - Grade
     * Level no longer matters since every student does the same PISA
     * content (see PisaController::leaderboard() for why).
     */
    public function test_the_leaderboard_ranks_students_in_the_same_school_regardless_of_grade_level(): void
    {
        $pisaLevel = PisaProficiencyLevel::where('name', 'Level 1')->first();
        $item = CurriculumItem::create([
            'category_id' => $this->pisaCategory()->id, 'title' => 'Scam check', 'type' => 'quiz',
            'pisa_proficiency_level_id' => $pisaLevel->id, 'sort_order' => 1,
        ]);

        $viewer = $this->studentAtLevel('Rookie', sid: 1);
        $schoolmateAtAnotherLevel = $this->studentAtLevel('Intermediate', sid: 1);
        $otherSchoolStudent = $this->studentAtLevel('Rookie', sid: 2);

        $this->completeQuiz($viewer, $item, 1);
        $this->completeQuiz($schoolmateAtAnotherLevel, $item, 1);
        $this->completeQuiz($otherSchoolStudent, $item, 1);

        $response = $this->actingAs($viewer)->get(route('pisa.leaderboard'));

        $response->assertOk();
        $response->assertSee($viewer->nickname);
        $response->assertSee($schoolmateAtAnotherLevel->nickname);
        $response->assertDontSee($otherSchoolStudent->nickname);
    }

    public function test_the_leaderboard_sums_each_students_best_score_per_item(): void
    {
        $pisaLevel = PisaProficiencyLevel::where('name', 'Level 1')->first();
        $item = CurriculumItem::create([
            'category_id' => $this->pisaCategory()->id, 'title' => 'Scam check', 'type' => 'quiz',
            'pisa_proficiency_level_id' => $pisaLevel->id, 'sort_order' => 1,
        ]);
        $viewer = $this->studentAtLevel('Rookie');

        // Two attempts at the same item - only the better score should count.
        $this->completeQuiz($viewer, $item, 0);
        $this->completeQuiz($viewer, $item, 1);

        $response = $this->actingAs($viewer)->get(route('pisa.leaderboard'));

        $response->assertOk();
        $response->assertSee('ranked #1');
    }

    public function test_a_pisa_level_page_offers_a_timed_play_link_and_the_item_page_shows_the_timer(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $pisaLevel = PisaProficiencyLevel::where('name', 'Level 1')->first();
        $item = CurriculumItem::create([
            'category_id' => $this->pisaCategory()->id, 'title' => 'Timed item', 'type' => 'quiz',
            'pisa_proficiency_level_id' => $pisaLevel->id, 'sort_order' => 1, 'is_active' => true,
        ]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Q', 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true, 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => false, 'sort_order' => 2]);

        $levelResponse = $this->actingAs($student)->get(route('pisa.level', $pisaLevel));
        $levelResponse->assertOk();
        $levelResponse->assertSee('Play timed');

        $timedResponse = $this->actingAs($student)->get(route('academy.item', $item) . '?timed=1');
        $timedResponse->assertOk();
        $timedResponse->assertSee('timerDisplay', false);
    }
}
