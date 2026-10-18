<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: manually registered a student via admin but didn't
 * know the password - couldn't log in, and re-registering with the same
 * email failed as a duplicate. Root cause: the "Add Student" form's
 * password <input> had no `name` attribute at all, so it was never
 * actually submitted; add_student()'s password validation was also
 * commented out, so Hash::make($request->password) hashed null for
 * every student added this way.
 */
class StudentPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_student_requires_a_real_password_and_hashes_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => 'testschool.example',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.admin.student.add-student'), [
            'name' => 'New Student',
            'email' => 'newstudent@testschool.example',
            'password' => 'a-real-password',
            'school' => $school->id,
        ]);

        $response->assertRedirect();
        $student = User::where('email', 'newstudent@testschool.example')->first();
        $this->assertNotNull($student);
        $this->assertTrue(Hash::check('a-real-password', $student->password));
    }

    public function test_add_student_rejects_a_missing_password(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => 'testschool2.example',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.admin.student.add-student'), [
            'name' => 'New Student',
            'email' => 'nopassword@testschool2.example',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'nopassword@testschool2.example']);
    }

    /**
     * Daniel, 2026-10-19: found while building the self-service
     * account-claim flow - the "Add Student" form's Class dropdown
     * (name="grade") was collected but never saved to the User model,
     * so a staged account's class could never be verified at claim time.
     */
    public function test_add_student_saves_the_selected_class(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => 'testschool3.example',
        ]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A']);

        $this->actingAs($admin)->post(route('admin.admin.student.add-student'), [
            'name' => 'New Student',
            'email' => 'classed@testschool3.example',
            'password' => 'a-real-password',
            'school' => $school->id,
            'grade' => $class->id,
        ]);

        $student = User::where('email', 'classed@testschool3.example')->first();
        $this->assertNotNull($student);
        $this->assertSame($class->id, $student->grade);
    }

    public function test_admin_can_reset_an_existing_students_password(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($admin)->post(route('admin.admin.student.reset-password', $student->id), [
            'password' => 'brand-new-password',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('brand-new-password', $student->fresh()->password));
    }
}
