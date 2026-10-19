<?php

namespace Tests\Feature;

use App\Models\Avatar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: every avatar image was 404ing in the browser console
 * ("GET .../avatar6.png 404"). The `avatars` table said "avatar1.png" ..
 * "avatar9.png", but the real committed files are "avtar1.svg" ..
 * "avtar9.svg" - a typo'd filename and wrong extension on all 9 rows, from
 * the original base-template upload. Fixed via a data-fix migration
 * (2026_10_19_000001); this guards against the two drifting apart again.
 */
class AvatarAssetsExistTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_active_avatars_image_file_actually_exists(): void
    {
        $avatars = Avatar::where('status', 1)->get();
        $this->assertNotEmpty($avatars, 'No active avatars seeded - nothing to check.');

        foreach ($avatars as $avatar) {
            $path = public_path('asset/front/images/' . $avatar->name);
            $this->assertFileExists($path, "Avatar #{$avatar->id} points at a missing file: {$avatar->name}");
        }
    }
}
