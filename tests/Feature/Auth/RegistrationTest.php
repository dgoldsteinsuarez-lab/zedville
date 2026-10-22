<?php

namespace Tests\Feature\Auth;

use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Daniel, 2026-10-19: the Name field's HTML maxlength was 8 - a
     * student couldn't even finish typing "Esperanza". Server-side
     * validation has always allowed up to 255 (RegisteredUserController);
     * the HTML attribute just never matched it.
     */
    public function test_name_field_allows_a_full_name_not_just_eight_characters(): void
    {
        $response = $this->get('/register');

        $response->assertSee('name="name"', false);
        $response->assertDontSee('name="name" value="{{old(\'name\')}}" required autofocus minlength="3" maxlength="8"', false);

        preg_match('/<input[^>]*name="name"[^>]*maxlength="(\d+)"/', $response->getContent(), $matches);
        $this->assertNotEmpty($matches, 'Could not find a maxlength on the name field');
        $this->assertGreaterThanOrEqual(100, (int) $matches[1]);
    }

    /**
     * Zedville's sign-up (RegisteredUserController::store): a one-word
     * nickname, an email from an approved school domain and that school's
     * class code. The student then logs in themselves - they aren't signed
     * in automatically. Claiming an admin-created account is covered by
     * ClaimStagedAccountTest.
     */
    public function test_a_student_can_sign_up_with_a_school_email_and_class_code(): void
    {
        [$school, $class] = $this->schoolWithClass();

        $response = $this->post('/register', [
            'name' => 'Esperanza Gold',
            'nickname' => 'Esperanza',
            'email' => 'esperanza@zedville-school.test',
            'classCode' => 'ZV8A01',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'esperanza@zedville-school.test', 'nickname' => 'Esperanza',
            'role' => '4', 'sid' => (string) $school->id, 'grade' => $class->id,
        ]);
    }

    public function test_an_email_from_an_unapproved_school_domain_cannot_sign_up(): void
    {
        $this->schoolWithClass();

        $response = $this->from('/register')->post('/register', [
            'name' => 'Someone Else',
            'nickname' => 'Someone',
            'email' => 'someone@gmail.com',
            'classCode' => 'ZV8A01',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'someone@gmail.com']);
    }

    private function schoolWithClass(): array
    {
        $school = SchoolDomain::create([
            'school_name' => 'Zedville School', 'school_code' => 'ZS-' . uniqid(), 'school_domain' => 'zedville-school.test',
        ]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'classCode' => 'ZV8A01']);

        return [$school, $class];
    }
}
