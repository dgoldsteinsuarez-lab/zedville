<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-25: "I choose 3c for log in page ... Put 'Hello Citizen,
 * Welcome back to Zedville'" - and on 2026-10-26: "Hello Citizen, Welcome
 * to Zedville!". The log-in page is design 3c, in the visitor's language:
 * the one they pick on the page, else their browser's.
 */
class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_log_in_page_is_design_3c_with_the_welcome(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Hello Citizen,')
            ->assertSee('Welcome to Zedville!')
            ->assertSee('skyline.webp', false)
            ->assertSee('What is Zedville?');
    }

    public function test_the_visitor_can_pick_a_language_and_it_is_remembered(): void
    {
        $this->get('/login?lang=es')->assertOk()
            ->assertSee('¡Bienvenido/a a Zedville!')
            ->assertSee('lang="es"', false);

        $this->get('/login')->assertSee('¡Bienvenido/a a Zedville!');
    }

    public function test_the_browser_language_is_used_when_none_was_picked(): void
    {
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.5')->get('/login')
            ->assertOk()->assertSee('Bienvenue à Zedville !');

        $this->withHeader('Accept-Language', 'it-IT')->get('/login')
            ->assertOk()->assertSee('Welcome to Zedville!');
    }

    public function test_a_wrong_password_is_explained_in_the_visitors_language(): void
    {
        $user = User::factory()->create();

        $this->withSession(['guest_locale' => 'de'])
            ->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'E-Mail-Adresse oder Passwort ist falsch.']);
    }

    /** Daniel, 2026-10-26: the home page is the log-in page. */
    public function test_the_home_page_is_the_log_in_page(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Hello Citizen,')
            ->assertSee('Welcome to Zedville!')
            ->assertSee('What is Zedville?')
            ->assertSee('action="' . route('login') . '"', false);

        $this->withHeader('Accept-Language', 'de-DE')->get('/')->assertSee('Willkommen in Zedville!');

        // A citizen who is already signed in goes to their own home.
        $this->actingAs(User::factory()->create(['role' => 4]))->get('/')->assertRedirect();
    }

    public function test_the_design_previews_are_gone(): void
    {
        $this->get('/login-preview/3c')->assertNotFound();
    }
}
