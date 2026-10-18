<?php

namespace Tests\Feature\Auth;

use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: wants a two-step flow - admin pre-creates a student
 * with their real name/email and a shared temporary password
 * (AdminStudentController::add_student()), then the student finishes
 * setting up the account at /register with their nickname, class code,
 * and a new password of their own. Since the class code and the starting
 * password are both shared by every student in the class, only the email
 * is unique per student - so the claim also requires the original
 * (admin-set) password as proof, otherwise any classmate could claim
 * someone else's account first.
 */
class ClaimStagedAccountTest extends TestCase
{
    use RefreshDatabase;

    private function stageStudent(string $password = 'shared-temp-pass'): array
    {
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => 'testschool.example',
        ]);
        $class = SchoolClass::create([
            'sid' => (string) $school->id,
            'name' => '8A',
            'classCode' => 'ABC123',
        ]);
        $student = User::create([
            'sid' => $school->id,
            'name' => 'Real Student Name',
            'email' => 'student@testschool.example',
            'password' => Hash::make($password),
            'citizenId' => 'ZV-2026-0001',
            'role' => 4,
            'avatar' => 1,
        ]);

        return [$school, $class, $student];
    }

    public function test_student_can_claim_a_staged_account_with_the_original_password(): void
    {
        [, $class, $student] = $this->stageStudent();

        $response = $this->post('/register', [
            'nickname' => 'Robin',
            'email' => 'student@testschool.example',
            'classCode' => $class->classCode,
            'current_password' => 'shared-temp-pass',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertRedirect(route('login'));
        $student->refresh();
        $this->assertSame('Robin', $student->nickname);
        $this->assertSame('Real Student Name', $student->name); // untouched
        $this->assertSame($class->id, $student->grade);
        $this->assertTrue(Hash::check('brand-new-password', $student->password));
    }

    public function test_claim_is_rejected_with_the_wrong_current_password(): void
    {
        [, $class, $student] = $this->stageStudent();

        $response = $this->post('/register', [
            'nickname' => 'Robin',
            'email' => 'student@testschool.example',
            'classCode' => $class->classCode,
            'current_password' => 'guessed-wrong',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
        $student->refresh();
        $this->assertNull($student->nickname);
        $this->assertTrue(Hash::check('shared-temp-pass', $student->password));
    }

    public function test_claim_is_rejected_with_an_invalid_class_code(): void
    {
        [, , $student] = $this->stageStudent();

        $response = $this->post('/register', [
            'nickname' => 'Robin',
            'email' => 'student@testschool.example',
            'classCode' => 'ZZZZZZ',
            'current_password' => 'shared-temp-pass',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertSessionHasErrors('classCode');
        $this->assertNull($student->fresh()->nickname);
    }

    public function test_claim_is_rejected_when_class_code_conflicts_with_an_already_assigned_class(): void
    {
        [$school, $class, $student] = $this->stageStudent();
        $otherClass = SchoolClass::create([
            'sid' => (string) $school->id,
            'name' => '8B',
            'classCode' => 'ZYX987',
        ]);
        $student->update(['grade' => $class->id]);

        $response = $this->post('/register', [
            'nickname' => 'Robin',
            'email' => 'student@testschool.example',
            'classCode' => $otherClass->classCode,
            'current_password' => 'shared-temp-pass',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertSessionHasErrors('classCode');
    }

    public function test_brand_new_registration_still_works_for_a_non_staged_email(): void
    {
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => 'newschool.example',
        ]);
        $class = SchoolClass::create([
            'sid' => (string) $school->id,
            'name' => '7A',
            'classCode' => 'NEW111',
        ]);

        $response = $this->post('/register', [
            'name' => 'Brand New Student',
            'nickname' => 'Newbie',
            'email' => 'brandnew@newschool.example',
            'classCode' => $class->classCode,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'brandnew@newschool.example',
            'nickname' => 'Newbie',
        ]);
    }
}
