<?php

namespace Tests\Feature\Admin;

use App\Models\FinheroActivityDefault;
use App\Models\User;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel's requirement (2026-10-18): one global default point value per
 * FinHero activity, admin-editable, applying to every school. A
 * finhero_activity_registry row's own max_points only overrides the default
 * when an admin has explicitly set it on that specific row; otherwise every
 * school inherits the shared default, and editing the default changes the
 * effective value everywhere it hasn't been overridden - without touching
 * the override rows themselves.
 */
class FinheroPointDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_reach_the_point_defaults_screen(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);
        $this->actingAs($schoolAdmin)->get('/admin/finhero/point-defaults')->assertForbidden();

        $superAdmin = User::factory()->create(['role' => 1]);
        $this->actingAs($superAdmin)->get('/admin/finhero/point-defaults')->assertOk();
    }

    public function test_editing_a_default_changes_effective_points_for_rows_with_no_override(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $student = User::factory()->create(['role' => 4]);

        FinheroActivityDefault::create(['activity_key' => 'task_1', 'type' => 'task', 'max_points' => 1]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => $student->sid, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_1', 'activity_name' => 'Task 1',
            'max_points' => null, // no override - inherits the default
            'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $default = FinheroActivityDefault::where('activity_key', 'task_1')->first();

        $this->actingAs($superAdmin)->put(route('finhero.point-defaults.update', $default->id), [
            'max_points' => 3,
        ])->assertRedirect();

        $calculator = app(FinheroBadgeCalculatorService::class);
        $total = $this->invokePrivate($calculator, 'getTotalAvailable', [now()->month, now()->year, null]);

        // 10 (quiz; no settings row so library adds 0) + 3 (updated task_1 default)
        $this->assertSame(13, $total);
    }

    public function test_a_row_with_its_own_max_points_ignores_default_changes(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $student = User::factory()->create(['role' => 4]);

        FinheroActivityDefault::create(['activity_key' => 'task_1', 'type' => 'task', 'max_points' => 1]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => $student->sid, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_1', 'activity_name' => 'Task 1',
            'max_points' => 5, // explicit override
            'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $default = FinheroActivityDefault::where('activity_key', 'task_1')->first();
        $this->actingAs($superAdmin)->put(route('finhero.point-defaults.update', $default->id), ['max_points' => 50]);

        $calculator = app(FinheroBadgeCalculatorService::class);
        $total = $this->invokePrivate($calculator, 'getTotalAvailable', [now()->month, now()->year, null]);

        // 10 (quiz) + row's own override (5), not the new default (50)
        $this->assertSame(15, $total);
    }

    public function test_award_points_falls_back_to_default_when_row_has_no_override(): void
    {
        $student = User::factory()->create(['role' => 4]);

        FinheroActivityDefault::create(['activity_key' => 'task_custom', 'type' => 'task', 'max_points' => 7]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => $student->sid, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_custom', 'activity_name' => 'Custom',
            'max_points' => null, 'position' => 'optional', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->post('/finhero/award-points', ['activity_key' => 'task_custom']);

        $response->assertOk();
        $response->assertJson(['success' => true, 'points_awarded' => 7]);
    }

    private function invokePrivate(object $object, string $method, array $args)
    {
        $m = (new \ReflectionClass($object))->getMethod($method);
        $m->setAccessible(true);
        return $m->invoke($object, ...$args);
    }
}
