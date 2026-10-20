<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel: "when I select the school in Admin, it should show clearly at
 * the top the school that I am in." The header badge alone
 * (components/school-badge.blade.php) was too easy to miss - this full-
 * width banner (layouts/admin.blade.php) is the same information shown
 * unmistakably on every admin page (bugs.md, 2026-10-20).
 */
class ManagingSchoolBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_a_no_school_selected_banner_by_default(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('NO SCHOOL SELECTED');
    }

    public function test_super_admin_sees_the_managing_banner_once_a_school_is_selected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create(['school_name' => 'Zedville Academy', 'school_code' => 'ZEDB', 'school_domain' => 'zedb.test']);
        session(['selected_school' => $school->id]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('MANAGING');
        $response->assertSee('Zedville Academy');
        $response->assertDontSee('NO SCHOOL SELECTED');
    }

    public function test_a_school_admin_pinned_to_their_own_school_does_not_see_the_banner(): void
    {
        $school = SchoolDomain::create(['school_name' => 'Zedville Academy', 'school_code' => 'ZEDC', 'school_domain' => 'zedc.test']);
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => $school->id]);

        $response = $this->actingAs($schoolAdmin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertDontSee('MANAGING');
        $response->assertDontSee('NO SCHOOL SELECTED');
    }
}
