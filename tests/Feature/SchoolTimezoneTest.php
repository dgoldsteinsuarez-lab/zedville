<?php

namespace Tests\Feature;

use App\Models\SchoolDomain;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: "for each school, I need to add the timezone ... so
 * they get the right greeting."
 */
class SchoolTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function school(string $timezone): SchoolDomain
    {
        return SchoolDomain::create([
            'school_name' => 'School ' . uniqid(), 'school_code' => uniqid(), 'school_domain' => uniqid() . '.example',
            'timezone' => $timezone,
        ]);
    }

    public function test_the_greeting_follows_the_schools_time_zone(): void
    {
        // 20:00 UTC is evening in Berlin but already 09:00 the next morning in Auckland.
        Carbon::setTestNow(Carbon::parse('2026-10-22 20:00:00', 'UTC'));

        $auckland = $this->school('Pacific/Auckland');
        $berlin = $this->school('Europe/Berlin');

        $this->actingAs(User::factory()->create(['role' => 4, 'sid' => $auckland->id]))
            ->get(route('education.city'))
            ->assertOk()->assertSee('Good morning')->assertSee('Pacific\/Auckland', false);

        $this->actingAs(User::factory()->create(['role' => 4, 'sid' => $berlin->id]))
            ->get(route('education.city'))
            ->assertOk()->assertSee('Good evening');
    }

    public function test_a_school_without_a_valid_time_zone_falls_back_to_berlin(): void
    {
        $school = $this->school('Not/AZone');
        $this->assertSame('Europe/Berlin', \App\Support\SchoolTime::timezoneFor(User::factory()->make(['sid' => $school->id])));
    }

    public function test_the_super_admin_can_set_a_schools_time_zone(): void
    {
        $school = $this->school('Europe/Berlin');
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin)->put(url('admin/school-domain/edit/' . $school->id), [
            'school_name' => $school->school_name, 'school_code' => $school->school_code,
            'school_domain' => $school->school_domain, 'timezone' => 'America/Mexico_City',
        ])->assertRedirect();

        $this->assertSame('America/Mexico_City', $school->fresh()->timezone);

        $this->actingAs($admin)->put(url('admin/school-domain/edit/' . $school->id), [
            'school_name' => $school->school_name, 'school_code' => $school->school_code,
            'school_domain' => $school->school_domain, 'timezone' => 'Mars/Olympus',
        ])->assertSessionHasErrors('timezone');
    }
}
