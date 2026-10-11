<?php

namespace Tests\Feature\Admin;

use App\Models\RoomPoster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\RoomPosterController - written while
 * extracting it out of AdminEducationController (planning.md Phase 3
 * #4). Previously uncovered.
 */
class RoomPosterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_posters(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        RoomPoster::create(['poster_name' => 'Poster A', 'poster_image' => 'a.png']);

        $response = $this->actingAs($admin)->get('/admin/education/poster');

        $response->assertOk();
        $response->assertViewHas('posters', fn ($posters) => $posters->count() === 1);
    }

    public function test_admin_can_update_a_poster(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $poster = RoomPoster::create(['poster_name' => 'Old Name', 'poster_image' => 'old.png']);

        $response = $this->actingAs($admin)->put("/admin/education/updatePoster/{$poster->id}", [
            'poster_name' => 'Updated Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('room_poster', ['id' => $poster->id, 'poster_name' => 'Updated Name']);
    }

    public function test_admin_can_delete_a_poster_with_matching_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $poster = RoomPoster::create(['poster_name' => 'Delete Me', 'poster_image' => 'nonexistent.png']);

        $response = $this->actingAs($admin)->delete("/admin/education/deletePoster/{$poster->id}", [
            'confirm_code' => 'ABC',
            'delete_code' => 'ABC',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('room_poster', ['id' => $poster->id]);
    }
}
