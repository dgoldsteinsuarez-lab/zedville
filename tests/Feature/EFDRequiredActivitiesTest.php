<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Esperanza bug, Daniel 2026-10-21: "I have added Discretional Activity,
 * Finhero and Finhero Library activity, but it doesn't show in
 * Esperanza's Required Activities." A FinHero Task assigned to the whole
 * Grade (grade_id set, cid null - see FinheroActivityFormTest) must reach
 * every student in every class under that grade, not just one exact
 * class-id match.
 */
class EFDRequiredActivitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_grade_wide_task_activity_shows_for_a_student_in_any_class_under_that_grade(): void
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        DB::table('finhero_activity_registry')->insert([
            'activity_key' => 'task_9', 'activity_name' => 'Grade-wide Task', 'type' => 'task',
            'sid' => 1, 'cid' => null, 'grade_id' => $grade->id,
            'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('education.educational_finance_department'));

        $response->assertOk();
        $response->assertSee('Grade-wide Task');
    }

    /**
     * Daniel, 2026-10-21: "I have added Discretional Activity, Finhero
     * and Finhero Library activity, but it doesn't show in Esperanza's
     * Required Activities" - the page never read type='library' rows at
     * all before this fix.
     */
    public function test_a_library_activity_shows_in_required_activities(): void
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        DB::table('finhero_activity_registry')->insert([
            'activity_key' => 'task_1', 'activity_name' => 'Introduction to Budget', 'type' => 'library',
            'sid' => 1, 'cid' => $class->id, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('education.educational_finance_department'));

        $response->assertOk();
        $response->assertSee('Introduction to Budget');
    }

    /**
     * The Library pop-up used to load a hardcoded modules.js; it now gets
     * the same modules, lessons and questions admins edit. It writes them
     * into innerHTML, so text must arrive escaped.
     */
    public function test_the_library_popup_gets_its_modules_from_the_database_with_text_escaped(): void
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        $module = \App\Models\FinheroLibraryModule::create([
            'id' => 4, 'module_key' => 'task_4', 'title' => 'Emergency Fund', 'icon' => '🛟', 'sort_order' => 5,
            'lesson' => [['title' => 'What is an Emergency Fund?', 'lines' => ['Money for surprises.']]],
        ]);
        \App\Models\FinheroLibraryQuestion::create(['module_id' => $module->id, 'question_text' => 'Is <b>this</b> escaped?', 'sort_order' => 1]);

        $response = $this->actingAs($student)->get(route('education.educational_finance_department'));

        $response->assertOk();
        $response->assertSee('const modules =', false);
        $response->assertSee('Emergency Fund', false);
        $response->assertDontSee('Is <b>this</b> escaped?', false);
        $response->assertDontSee('asset/front/js/modules.js', false);
    }
}
