<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\EmergencyFundPositionController - written
 * while extracting it out of AdminEducationController (planning.md
 * Phase 3 #4). Previously uncovered.
 */
class EmergencyFundPositionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_an_emergency_fund_position_and_it_creates_a_calendar_event(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/emergency-fund-position', [
            'position' => 'required',
            'cid' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', [
            'user_id' => $admin->id,
            'activity_key' => 'emeaccount',
            'cid' => 1,
            'required' => 1,
        ]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Emergency Fund', 'classId' => 1]);
    }

    public function test_cannot_set_a_duplicate_emergency_fund_position_for_the_same_class(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        DB::table('mba_position')->insert([
            'user_id' => $admin->id,
            'activity_key' => 'emeaccount',
            'sid' => 5001,
            'cid' => 1,
            'required' => 1,
            'optional' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/education/emergency-fund-position', [
            'position' => 'optional',
            'cid' => 1,
        ]);

        $response->assertRedirect();
        $this->assertSame('This class already has an MBA Position.', session('error'));
    }
}
