<?php

namespace Tests\Feature\Admin;

use App\Models\Npo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\NpoController - written while extracting
 * it out of AdminEducationController (planning.md Phase 3 #4).
 * Previously uncovered.
 */
class NpoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_an_npo(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/npo/add-npo', [
            'name' => 'Test Charity',
            'category' => 'Education',
            'content' => 'Helping kids learn.',
            'bank_name' => 'Universal Bank',
            'account_number' => '12345',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('npos', [
            'name' => 'Test Charity',
            'slug' => 'test-charity',
            'category' => 'Education',
        ]);
    }

    public function test_admin_can_update_an_npo(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $npo = Npo::create([
            'name' => 'Old Name',
            'category' => 'Education',
            'slug' => 'old-name',
            'status' => 1,
        ]);

        $response = $this->actingAs($admin)->post("/admin/npo/update/{$npo->id}", [
            'name' => 'New Name',
            'category' => 'Health',
            'status' => 1,
            'sort_order' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('npos', ['id' => $npo->id, 'name' => 'New Name', 'category' => 'Health']);
    }

    public function test_admin_can_delete_an_npo_with_matching_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $npo = Npo::create(['name' => 'Delete Me', 'category' => 'Education', 'slug' => 'delete-me', 'status' => 1]);

        $response = $this->actingAs($admin)->get("/admin/npo/delete/{$npo->id}?confirm_code=ABC&delete_code=ABC");

        $response->assertRedirect();
        $this->assertDatabaseMissing('npos', ['id' => $npo->id]);
    }

    public function test_delete_rejects_mismatched_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $npo = Npo::create(['name' => 'Keep Me', 'category' => 'Education', 'slug' => 'keep-me', 'status' => 1]);

        $response = $this->actingAs($admin)->get("/admin/npo/delete/{$npo->id}?confirm_code=ABC&delete_code=XYZ");

        $response->assertRedirect();
        $this->assertDatabaseHas('npos', ['id' => $npo->id]);
    }
}
