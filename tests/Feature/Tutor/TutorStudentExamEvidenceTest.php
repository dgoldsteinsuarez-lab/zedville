<?php

namespace Tests\Feature\Tutor;

use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "I want to create an Exam at the end of the year to
 * show or have evidence of learning." Surfaced on the existing Tutor
 * student-snapshot page (TutorStudentController::show()) rather than a new
 * page, per the plan's lean-reuse approach.
 */
class TutorStudentExamEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_completed_real_exam_shows_on_the_tutor_student_page(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade', 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class']);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create([
            'category_id' => $category->id, 'title' => 'End of Year Exam', 'type' => 'quiz',
            'is_real_exam' => true, 'sort_order' => 1,
        ]);
        CurriculumAttempt::create([
            'item_id' => $item->id, 'student_id' => $student->id,
            'score' => 8, 'total_questions' => 10, 'completed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tutor.my-students.show', $student->id));

        $response->assertOk();
        $response->assertSee('Exam evidence');
        $response->assertSee('End of Year Exam');
        $response->assertSee('8/10');
    }

    public function test_a_practice_item_does_not_count_as_exam_evidence(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade', 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class']);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create([
            'category_id' => $category->id, 'title' => 'Practice Quiz', 'type' => 'quiz',
            'is_real_exam' => false, 'sort_order' => 1,
        ]);
        CurriculumAttempt::create([
            'item_id' => $item->id, 'student_id' => $student->id,
            'score' => 5, 'total_questions' => 10, 'completed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tutor.my-students.show', $student->id));

        $response->assertOk();
        $response->assertSee('No real exams completed yet.');
    }
}
