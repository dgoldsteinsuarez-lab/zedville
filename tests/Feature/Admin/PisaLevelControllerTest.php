<?php

namespace Tests\Feature\Admin;

use App\Models\PisaProficiencyLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "Add in admin a section for PISA with the different
 * difficult levels that PISA has in their exams, later i want to build
 * assignments based on this." Confirmed via clarifying questions: PISA's
 * own official proficiency levels (seeded with the real OECD Financial
 * Literacy scale), a global reference catalog kept separate from Grade
 * Levels ("PISA is the same for all levels... my levels are rookie,
 * intermediate and so on" - not renamed, not touched).
 */
class PisaLevelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_six_official_financial_literacy_levels_are_seeded(): void
    {
        $names = PisaProficiencyLevel::where('domain', 'Financial Literacy')->orderBy('sort_order')->pluck('name')->all();

        $this->assertSame(
            ['Below Level 1', 'Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5'],
            $names
        );
    }

    public function test_grade_levels_are_untouched_by_the_pisa_migration(): void
    {
        $names = \App\Models\GradeLevel::orderBy('sort_order')->pluck('name')->all();

        $this->assertSame(['Rookie', 'Intermediate', 'Advance', 'Advance Economist'], $names);
    }

    public function test_a_super_admin_can_view_the_pisa_levels_page(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/pisa');

        $response->assertOk();
        $response->assertSee('Level 3');
    }

    public function test_a_school_admin_cannot_reach_the_pisa_levels_page(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);

        $this->actingAs($schoolAdmin)->get('/admin/pisa')->assertForbidden();
    }

    public function test_a_super_admin_can_add_a_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/pisa', [
            'domain' => 'Mathematics',
            'name' => 'Level 1',
            'sort_order' => 1,
            'min_score' => 358.3,
            'max_score' => 420.1,
            'description' => 'Test description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pisa_proficiency_levels', ['domain' => 'Mathematics', 'name' => 'Level 1']);
    }

    public function test_a_super_admin_can_edit_a_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = PisaProficiencyLevel::where('name', 'Level 2')->first();

        $response = $this->actingAs($admin)->put('/admin/pisa/' . $level->id, [
            'domain' => $level->domain,
            'name' => $level->name,
            'sort_order' => $level->sort_order,
            'min_score' => $level->min_score,
            'max_score' => $level->max_score,
            'description' => 'Edited description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pisa_proficiency_levels', ['id' => $level->id, 'description' => 'Edited description']);
    }

    public function test_a_super_admin_can_delete_a_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = PisaProficiencyLevel::create([
            'domain' => 'Test Domain',
            'name' => 'Temp',
            'sort_order' => 99,
        ]);

        $response = $this->actingAs($admin)->delete('/admin/pisa/' . $level->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('pisa_proficiency_levels', ['id' => $level->id]);
    }
}
