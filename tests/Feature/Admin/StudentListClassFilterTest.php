<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bank Admin Functions doc, Screen 1: "Filter by Class". AdminStudentController::index
 * already supported school-level filtering and name/email search; class-level
 * filtering (via users.grade, which holds the student's Class id) was missing.
 */
class StudentListClassFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtering_by_class_only_shows_students_in_that_class(): void
    {
        $admin = User::factory()->create(['role' => 1]); // super admin
        $classA = SchoolClass::create(['name' => 'Grade 6 - A']);
        $classB = SchoolClass::create(['name' => 'Grade 6 - B']);

        $inA = User::factory()->create(['role' => 4, 'grade' => $classA->id]);
        $inB = User::factory()->create(['role' => 4, 'grade' => $classB->id]);

        $response = $this->actingAs($admin)->get('/admin/student?class_id=' . $classA->id);

        $response->assertOk();
        $response->assertSee($inA->name);
        $response->assertDontSee($inB->name);
    }
}
