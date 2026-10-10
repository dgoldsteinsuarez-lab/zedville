<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * routes/web.php had five student-facing routes with no 'auth' middleware
 * at all - unlike every comparable route in the file (the very next route
 * after these, /award-poster-room, explicitly has ->middleware('auth')).
 * An unauthenticated GET to /login-quiz/today returned 200 with
 * {"show":false} instead of a redirect to login. Guards against the gap
 * reopening.
 */
class AuthGapTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_away_from_login_quiz_today(): void
    {
        $this->get('/login-quiz/today')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_away_from_login_quiz_submit(): void
    {
        $this->post('/login-quiz/submit')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_away_from_city_mood(): void
    {
        $this->get('/city-mood')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_away_from_consumer_profile_survey(): void
    {
        $this->get('/profile/consumer-profile-survey')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_away_from_storesurvey(): void
    {
        $this->post('/profile/storesurvey')->assertRedirect('/login');
    }

    public function test_authenticated_student_can_still_reach_login_quiz_today(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 5001]);

        $this->actingAs($user)->get('/login-quiz/today')->assertOk();
    }
}
