<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * City Map, Phase 1/1b (2026-10-19). Two levels: an overview page listing
 * the 9 districts the doc itself groups buildings into, then one page per
 * district listing just its own buildings - instead of ~25 hotspots
 * crammed onto a single overview image. Street plaques originally lived
 * only on the Residential Quarter district page (streets are residential
 * by definition) - 2026-10-20, Daniel asked for them reachable directly
 * from the overview too, so they're now shown in both places.
 */
class CityMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_overview_page_lists_all_nine_districts(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city'));

        $response->assertOk();
        foreach (['Residential Quarter', 'Civic Plaza', 'Education Hub', 'Main City Facilities', 'Financial Square', 'Community Care', 'Public Services', 'Industrial and Utilities Zone', 'Recreation and Development Spaces'] as $name) {
            $response->assertSee($name);
        }
    }

    public function test_a_district_page_lists_only_its_own_buildings(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city.district', 'financial-square'));

        $response->assertOk();
        $response->assertSee('Universal Bank');
        $response->assertSee('Money Factory');
        // A building from a different district shouldn't show in the
        // buildings grid (the shared sidebar links to City Mall on every
        // page regardless, so check the controller data directly instead
        // of scraping the rendered page for this one).
        $response->assertViewHas('buildings', function ($buildings) {
            return collect($buildings)->doesntContain(fn ($b) => $b['name'] === 'City Mall');
        });
    }

    public function test_an_unknown_district_404s(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city.district', 'not-a-real-district'));

        $response->assertStatus(404);
    }

    public function test_the_residential_quarter_lists_all_27_street_plaques(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city.district', 'residential-quarter'));

        $response->assertOk();
        $response->assertSee('Adam Smith Avenue');
        $response->assertSee('Gramsci Street');
    }

    public function test_other_districts_do_not_show_street_plaques(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city.district', 'civic-plaza'));

        $response->assertOk();
        $response->assertDontSee('Adam Smith Avenue');
    }

    public function test_the_overview_page_also_lists_all_27_street_plaques(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city'));

        $response->assertOk();
        $response->assertSee('Adam Smith Avenue');
        $response->assertSee('Gramsci Street');
    }

    public function test_a_student_sees_their_own_assigned_address(): void
    {
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8']);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'grade_id' => $grade->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id]);

        $response = $this->actingAs($student)->get(route('education.city'));

        $response->assertOk();
        $response->assertSee('You live at');
    }
}
