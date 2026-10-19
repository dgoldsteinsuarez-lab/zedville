<?php

namespace Tests\Feature;

use App\Models\Street;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * City Map, Phase 1 (2026-10-19): the 27 street names from the "City and
 * Map" doc are fixed, global reference data - same category as GradeLevel.
 */
class StreetsSeededTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_27_streets_are_seeded(): void
    {
        $this->assertSame(27, Street::count());
    }

    public function test_a_couple_of_streets_have_the_right_bio(): void
    {
        $this->assertDatabaseHas('streets', [
            'name' => 'Adam Smith Avenue',
            'tier' => '1',
        ]);
        $this->assertDatabaseHas('streets', [
            'name' => 'Gramsci Street',
            'tier' => 'marxist',
        ]);

        $smith = Street::where('name', 'Adam Smith Avenue')->first();
        $this->assertStringContainsString('invisible hand', $smith->bio);
    }
}
