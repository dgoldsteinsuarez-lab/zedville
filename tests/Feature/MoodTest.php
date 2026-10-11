<?php

namespace Tests\Feature;

use App\Models\MoodLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for MoodController - written while extracting it
 * (saveMode/getMoodCoordinates/city_mood/getMoodData) out of
 * ProfileController (planning.md Phase 3 #4). Previously uncovered.
 * getMoodData() and city_mood() were byte-for-byte identical except for
 * the final return (a view vs. the raw array) - consolidated into one
 * method as part of the move.
 */
class MoodTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_mode_records_the_correct_energy_and_pleasantness(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 9701]);

        $response = $this->actingAs($user)->postJson('/profile/save-mood', ['mood' => 'happy']);

        $response->assertOk();
        $this->assertDatabaseHas('mood_logs', [
            'user_id' => $user->id,
            'mood' => 'happy',
            'energy' => 8,
            'pleasantness' => 9,
        ]);
    }

    public function test_save_mode_rejects_a_second_submission_the_same_day(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 9702]);
        MoodLog::create(['user_id' => $user->id, 'sid' => 9702, 'mood' => 'calm', 'energy' => 6, 'pleasantness' => 9, 'created_at' => now()]);

        $response = $this->actingAs($user)->postJson('/profile/save-mood', ['mood' => 'happy']);

        $response->assertOk();
        $response->assertJson(['message' => 'Mood already submitted today']);
        $this->assertEquals(1, MoodLog::where('user_id', $user->id)->count());
    }

    public function test_city_mood_page_renders_with_todays_log(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 9703, 'grade' => 1]);
        MoodLog::create(['user_id' => $user->id, 'sid' => 9703, 'mood' => 'happy', 'energy' => 8, 'pleasantness' => 9, 'created_at' => now()]);

        $response = $this->actingAs($user)->get('/city-mood');

        $response->assertOk();
        $response->assertViewHas('todayMyLog', fn ($log) => $log['mood'] === 'happy');
    }
}
