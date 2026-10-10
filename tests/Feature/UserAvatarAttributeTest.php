<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * User::avatar() was defined as a belongsTo relation with the SAME name
 * as the underlying `avatar` column it reads as its foreign key -
 * self-referential. Whenever a User instance's attributes array doesn't
 * already contain an 'avatar' key (e.g. a freshly created model that
 * didn't explicitly set it, as opposed to one loaded via a full `SELECT *`
 * like Auth::user() normally does), accessing $user->avatar falls through
 * to relation resolution instead of the plain column value, and that
 * resolution itself tries to read $user->avatar to find the FK value -
 * throwing "Undefined property: $avatar". avatarRelation() already
 * exists as the correctly-separated relation accessor and is what
 * AdminStudentController/details.blade.php actually use for object-style
 * access, so the colliding avatar() method served no purpose.
 */
class UserAvatarAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_accessing_avatar_on_a_freshly_created_user_does_not_throw(): void
    {
        $user = User::factory()->create(['role' => 4]);

        // Before the fix, this throws ErrorException: Undefined property: $avatar.
        $this->assertNull($user->avatar);
    }

    public function test_avatar_reads_back_as_the_plain_assigned_value(): void
    {
        $user = User::factory()->create(['role' => 4, 'avatar' => 3]);

        $this->assertSame(3, $user->avatar);
    }
}
