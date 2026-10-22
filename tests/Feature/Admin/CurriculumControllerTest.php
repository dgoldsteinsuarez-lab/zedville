<?php

namespace Tests\Feature\Admin;

use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "for PISA, i should be able to add tasks for each
 * level, for students to practice and also practice exams like in the real
 * pisa exams" + "i dont see the levels in admin" (his own planning doc's
 * Activities/Exams/Graphics-per-Level structure). Confirmed via
 * AskUserQuestion: real scored quizzes, kept separate from the existing
 * per-Grade Education settings.
 */
class CurriculumControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_seeded_per_level_matching_the_planning_doc(): void
    {
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $intermediate = GradeLevel::where('name', 'Intermediate')->first();
        $advance = GradeLevel::where('name', 'Advance')->first();
        $advanceEconomist = GradeLevel::where('name', 'Advance Economist')->first();

        $names = fn ($level) => CurriculumCategory::where('grade_level_id', $level->id)->orderBy('sort_order')->pluck('name')->all();

        // PISA Practice no longer appears per-level - consolidated into
        // one global category (grade_level_id null) the same day ("for
        // PISA i don't need to divide in Zedville levels"), so it drops
        // out of every per-level listing, Rookie's included (its own
        // original PISA Practice row is the one that became the global
        // one, hence the null, not a survivor of this query).
        $this->assertSame(['Activities', 'Exams', 'Graphics'], $names($rookie));
        $this->assertSame(['Activities', 'Exams', 'Graphics', 'Insurance', 'Emergency Costs', 'Credit'], $names($intermediate));
        $this->assertSame(['Activities', 'Exams', 'Graphics', 'Competition', 'Inflation'], $names($advance));
        $this->assertSame(['Activities', 'Exams', 'Graphics', 'Voting'], $names($advanceEconomist));
    }

    /**
     * Daniel, 2026-10-21: "i think I should create a Junior Level, for
     * grade 7 and 8, with more easy to digest content and activities" ->
     * "build a Junior." The fifth Level, ahead of Rookie (sort_order 1,
     * the easiest tier), seeded with the same base categories every Level
     * gets - no new code path, same pattern Intermediate/Advance/Advance
     * Economist already go through.
     */
    public function test_junior_level_exists_ahead_of_rookie_with_the_base_categories(): void
    {
        $names = GradeLevel::orderBy('sort_order')->pluck('name')->all();
        $this->assertSame('Junior', $names[0]);
        $this->assertSame('Rookie', $names[1]);

        // No PISA Practice category of its own - PISA Practice was
        // consolidated into one global category the same day ("for PISA
        // i don't need to divide in Zedville levels"), after Junior was
        // seeded with one, so Junior's own copy was folded into it.
        $junior = GradeLevel::where('name', 'Junior')->first();
        $categoryNames = CurriculumCategory::where('grade_level_id', $junior->id)->orderBy('sort_order')->pluck('name')->all();
        $this->assertSame(['Activities', 'Exams', 'Graphics'], $categoryNames);
    }

    public function test_a_grade_can_be_assigned_to_junior_and_have_multiple_classes(): void
    {
        $junior = GradeLevel::where('name', 'Junior')->first();
        $grade = \App\Models\Grade::create(['sid' => 1, 'name' => 'Grade 7', 'grade_level_id' => $junior->id]);

        // Multiple classes per Grade already works independently of which
        // Level it's assigned to - not new behavior, just confirming it
        // extends to Junior too.
        \App\Models\SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => '7A']);
        \App\Models\SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => '7B']);

        $this->assertSame($junior->id, $grade->fresh()->grade_level_id);
        $this->assertSame(2, \App\Models\SchoolClass::where('grade_id', $grade->id)->count());
    }

    /**
     * Daniel, 2026-10-21: "for PISA i don't need to divide in Zedville
     * levels, only the PISA levels... independent from if a student is
     * Junior, Rookie or else, they will have the same activities." PISA
     * Practice is consolidated into exactly one global category now
     * (grade_level_id null), not one per Grade Level.
     */
    public function test_exactly_one_global_pisa_practice_category_exists(): void
    {
        $this->assertSame(1, CurriculumCategory::where('is_pisa_practice', true)->count());
        $this->assertNull(CurriculumCategory::where('is_pisa_practice', true)->first()->grade_level_id);
    }

    public function test_a_super_admin_can_view_curriculum_pages(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();
        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('name', 'Activities')->first();

        $this->actingAs($admin)->get('/admin/curriculum')->assertOk();
        $this->actingAs($admin)->get('/admin/curriculum/levels/' . $level->id . '/categories')->assertOk();
        $this->actingAs($admin)->get('/admin/curriculum/categories/' . $category->id . '/items')->assertOk();
    }

    public function test_a_school_admin_cannot_reach_curriculum_pages(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);

        $this->actingAs($schoolAdmin)->get('/admin/curriculum')->assertForbidden();
    }

    public function test_a_super_admin_can_add_a_category(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();

        $response = $this->actingAs($admin)->post('/admin/curriculum/levels/' . $level->id . '/categories', [
            'name' => 'Savings',
            'sort_order' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('curriculum_categories', ['grade_level_id' => $level->id, 'name' => 'Savings']);
    }

    public function test_a_super_admin_can_add_and_edit_an_item(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();

        $response = $this->actingAs($admin)->post('/admin/curriculum/categories/' . $category->id . '/items', [
            'title' => 'Budgeting basics',
            'description' => 'Intro quiz',
            'type' => 'quiz',
            'sort_order' => 1,
            'is_active' => '1',
        ]);
        $response->assertRedirect();
        $item = CurriculumItem::where('category_id', $category->id)->where('title', 'Budgeting basics')->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->is_active);

        $this->actingAs($admin)->put('/admin/curriculum/items/' . $item->id, [
            'title' => 'Budgeting basics (updated)',
            'description' => 'Intro quiz',
            'type' => 'quiz',
            'sort_order' => 1,
        ])->assertRedirect();

        $item->refresh();
        $this->assertSame('Budgeting basics (updated)', $item->title);
        $this->assertFalse($item->is_active, 'omitting is_active on update should turn it off, matching the unchecked checkbox');
    }

    public function test_a_super_admin_can_add_a_question_with_options(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'question_text' => 'What is a budget?',
            'sort_order' => 1,
            'options' => ['A plan for money', 'A type of snack', '', ''],
            'correct' => 0,
        ]);

        $response->assertRedirect();
        $question = CurriculumQuestion::where('item_id', $item->id)->first();
        $this->assertNotNull($question);
        $this->assertSame(2, $question->options()->count());
        $this->assertTrue($question->options()->where('is_correct', true)->first()->option_text === 'A plan for money');
    }

    public function test_saving_a_question_with_no_correct_option_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'question_text' => 'Bad question',
            'sort_order' => 1,
            'options' => ['Only one option', '', '', ''],
            'correct' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('curriculum_questions', ['question_text' => 'Bad question']);
    }

    public function test_a_super_admin_can_edit_a_question_and_its_options(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Original', 'sort_order' => 1]);
        $optionA = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true, 'sort_order' => 1]);
        $optionB = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => false, 'sort_order' => 2]);

        $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'id' => $question->id,
            'question_text' => 'Edited',
            'sort_order' => 1,
            'options' => ['A', 'B'],
            'option_ids' => [0 => $optionA->id, 1 => $optionB->id],
            'correct' => 1,
        ])->assertRedirect();

        $question->refresh();
        $this->assertSame('Edited', $question->question_text);
        $this->assertFalse($optionA->fresh()->is_correct);
        $this->assertTrue($optionB->fresh()->is_correct);
        $this->assertSame(2, $question->options()->count(), 'editing should not create duplicate options');
    }

    /**
     * Daniel, 2026-10-21: "I should be able to add pictures, graphics and
     * more to them" + "add a section of practice exams and real exams in
     * Admin." Regression guard for the mass-assignment trap this would
     * otherwise hit: validateItem()'s rules include 'image', so storeItem()/
     * updateItem() must pull the uploaded file out before spreading the
     * rest into create()/update(), or this throws instead of saving.
     */
    public function test_a_super_admin_can_add_an_item_with_an_image_and_exam_fields(): void
    {
        $this->usePublicPathInTemp();
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();

        $response = $this->actingAs($admin)->post('/admin/curriculum/categories/' . $category->id . '/items', [
            'title' => 'End of year exam',
            'description' => 'Covers the full syllabus',
            // ->image() needs the GD extension to render real pixel data;
            // this XAMPP PHP build doesn't have it. Laravel's 'image' rule
            // only checks MIME type (see validateImage() -> validateMimes()
            // in vendor/.../ValidatesAttributes.php), so a GD-free fake
            // file with an explicit image mime type satisfies it.
            'image' => UploadedFile::fake()->create('stimulus.jpg', 100, 'image/jpeg'),
            'type' => 'quiz',
            'sort_order' => 1,
            'is_active' => '1',
            'is_real_exam' => '1',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
        ]);

        $response->assertRedirect();
        $item = CurriculumItem::where('category_id', $category->id)->where('title', 'End of year exam')->first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->image);
        $this->assertTrue($item->is_real_exam);
        $this->assertSame('2026-12-01', $item->start_date->format('Y-m-d'));
        $this->assertSame('2026-12-05', $item->end_date->format('Y-m-d'));
        $this->assertFileExists(public_path('uploads/curriculum/' . $item->image));
    }

    public function test_editing_an_item_without_a_new_file_keeps_its_existing_image(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create([
            'category_id' => $category->id, 'title' => 'Has an image', 'type' => 'quiz',
            'sort_order' => 1, 'image' => 'existing.jpg',
        ]);

        $this->actingAs($admin)->put('/admin/curriculum/items/' . $item->id, [
            'title' => 'Has an image (renamed)',
            'description' => '',
            'type' => 'quiz',
            'sort_order' => 1,
        ])->assertRedirect();

        $this->assertSame('existing.jpg', $item->fresh()->image);
    }

    public function test_an_exam_end_date_before_its_start_date_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();

        $response = $this->actingAs($admin)->post('/admin/curriculum/categories/' . $category->id . '/items', [
            'title' => 'Bad dates',
            'type' => 'quiz',
            'sort_order' => 1,
            'start_date' => '2026-12-05',
            'end_date' => '2026-12-01',
        ]);

        $response->assertSessionHasErrors('end_date');
        $this->assertDatabaseMissing('curriculum_items', ['title' => 'Bad dates']);
    }

    public function test_a_super_admin_can_add_a_question_with_an_image(): void
    {
        $this->usePublicPathInTemp();
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Graphics quiz', 'type' => 'quiz', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'question_text' => 'What is the value at x=3 on this chart?',
            'image' => UploadedFile::fake()->create('chart.png', 100, 'image/png'),
            'sort_order' => 1,
            'options' => ['10', '20', '', ''],
            'correct' => 1,
        ]);

        $response->assertRedirect();
        $question = CurriculumQuestion::where('item_id', $item->id)->first();
        $this->assertNotNull($question->image);
        $this->assertFileExists(public_path('uploads/curriculum/' . $question->image));
    }

    public function test_editing_a_question_without_a_new_file_keeps_its_existing_image(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Q', 'image' => 'existing-q.jpg', 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true, 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => false, 'sort_order' => 2]);

        $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'id' => $question->id,
            'question_text' => 'Q (edited)',
            'sort_order' => 1,
            'options' => ['A', 'B'],
            'correct' => 0,
        ])->assertRedirect();

        $this->assertSame('existing-q.jpg', $question->fresh()->image);
    }

    /**
     * Daniel, 2026-10-21: "add a section of practice exams and real exams
     * in Admin." A CurriculumItem is scoped only by the global
     * grade_level_id on its category, which can span several schools'
     * classes - unlike mba_position/finhero_activity_registry (already
     * per-class), a real exam's calendar event has to fan out to every
     * class in that Grade Level, not just one.
     */
    public function test_marking_an_item_as_a_real_exam_with_dates_syncs_a_calendar_event_per_class_in_that_grade_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();
        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('is_pisa_practice', false)->first();

        $gradeA = Grade::create(['sid' => 1, 'name' => 'Grade A', 'grade_level_id' => $level->id]);
        $classA1 = SchoolClass::create(['sid' => 1, 'grade_id' => $gradeA->id, 'name' => 'A1']);
        $classA2 = SchoolClass::create(['sid' => 1, 'grade_id' => $gradeA->id, 'name' => 'A2']);
        $gradeB = Grade::create(['sid' => 2, 'name' => 'Grade B', 'grade_level_id' => $level->id]);
        $classB1 = SchoolClass::create(['sid' => 2, 'grade_id' => $gradeB->id, 'name' => 'B1']);

        $response = $this->actingAs($admin)->post('/admin/curriculum/categories/' . $category->id . '/items', [
            'title' => 'End of year exam',
            'type' => 'quiz',
            'sort_order' => 1,
            'is_real_exam' => '1',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('calendar_events', ['sid' => 1, 'classId' => $classA1->id, 'title' => 'exam-End of year exam']);
        $this->assertDatabaseHas('calendar_events', ['sid' => 1, 'classId' => $classA2->id, 'title' => 'exam-End of year exam']);
        $this->assertDatabaseHas('calendar_events', ['sid' => 2, 'classId' => $classB1->id, 'title' => 'exam-End of year exam']);
        $this->assertSame(3, DB::table('calendar_events')->where('title', 'exam-End of year exam')->count());
    }

    public function test_turning_off_real_exam_removes_its_calendar_events(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();
        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('is_pisa_practice', false)->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade', 'grade_level_id' => $level->id]);
        SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class']);

        $this->actingAs($admin)->post('/admin/curriculum/categories/' . $category->id . '/items', [
            'title' => 'Maybe exam', 'type' => 'quiz', 'sort_order' => 1,
            'is_real_exam' => '1', 'start_date' => '2026-12-01',
        ]);
        $item = CurriculumItem::where('title', 'Maybe exam')->firstOrFail();
        $this->assertDatabaseHas('calendar_events', ['title' => 'exam-Maybe exam']);

        $this->actingAs($admin)->put('/admin/curriculum/items/' . $item->id, [
            'title' => 'Maybe exam', 'type' => 'quiz', 'sort_order' => 1,
            // is_real_exam omitted - an unchecked checkbox.
        ]);

        $this->assertDatabaseMissing('calendar_events', ['title' => 'exam-Maybe exam']);
    }

    public function test_a_super_admin_can_delete_a_category_item_and_question(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::create(['grade_level_id' => GradeLevel::first()->id, 'name' => 'Temp', 'sort_order' => 99]);
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Temp item', 'type' => 'quiz', 'sort_order' => 1]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Temp q', 'sort_order' => 1]);

        $this->actingAs($admin)->delete('/admin/curriculum/questions/' . $question->id)->assertRedirect();
        $this->assertDatabaseMissing('curriculum_questions', ['id' => $question->id]);

        $this->actingAs($admin)->delete('/admin/curriculum/items/' . $item->id)->assertRedirect();
        $this->assertDatabaseMissing('curriculum_items', ['id' => $item->id]);

        $this->actingAs($admin)->delete('/admin/curriculum/categories/' . $category->id)->assertRedirect();
        $this->assertDatabaseMissing('curriculum_categories', ['id' => $category->id]);
    }
}
