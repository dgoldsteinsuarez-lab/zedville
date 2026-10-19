<?php

namespace Tests\Feature;

use App\Models\Npo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: "I know the IT guys did it, could I find this in
 * hostinger?" - the `npos` table was empty locally; the real 7 NGOs only
 * ever existed in the production database on Hostinger, never committed
 * as a seeder here. Pulled the production data via phpMyAdmin export and
 * replayed it in a new migration (2026_10_19_000002_seed_npos_from_
 * production). Image files substituted with the already-committed
 * plain-named versions (production's were upload-timestamped filenames
 * that only exist on the live server).
 */
class NpoCatalogSeededTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_seven_ngos_are_seeded_and_active(): void
    {
        $this->assertSame(7, Npo::where('status', 1)->count());

        foreach (['animal-shelter', 'senior-care-home', 'childrens-orphanage', 'food-bank', 'local-library', 'community-center', 'ecofun'] as $slug) {
            $this->assertDatabaseHas('npos', ['slug' => $slug, 'status' => 1]);
        }
    }

    public function test_every_ngos_image_file_actually_exists(): void
    {
        foreach (Npo::all() as $npo) {
            $this->assertFileExists(
                public_path('uploads/npos/' . $npo->image),
                "NPO '{$npo->name}' points at a missing image: {$npo->image}"
            );
        }
    }

    public function test_the_ngo_directory_page_lists_them_all(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.npos'));

        $response->assertOk();
        $response->assertSee('Animal Shelter');
        $response->assertSee('EcoFun');
    }
}
