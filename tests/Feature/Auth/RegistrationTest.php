<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
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

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
