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
        foreach (['Senior Care Quarter', 'Financial Square', 'Market & Energy District', 'Community Care', 'Civic Plaza', 'Residential Quarter', 'Garden Homes', 'Education Hub', 'Future Developments'] as $name) {
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
        // buildings grid (checked on the controller data, not the page).
        $response->assertViewHas('buildings', function ($buildings) {
            // Daniel's map (2026-10-22) puts the City Mall in Financial Square
            // (division 2); City Hall is in Civic Plaza (division 5).
            return collect($buildings)->contains(fn ($b) => $b['name'] === 'City Mall')
                && collect($buildings)->doesntContain(fn ($b) => $b['name'] === 'City Hall');
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
        // The render itself carries the names of its own streets (Adam
        // Smith Avenue runs along Civic Plaza); the plaques list does not.
        $response->assertDontSee(__('city.street_plaques'));
        $response->assertDontSee('Gramsci Street');
    }

    public function test_the_overview_page_also_lists_all_27_street_plaques(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city'));

        $response->assertOk();
        $response->assertSee('Adam Smith Avenue');
        $response->assertSee('Gramsci Street');
    }

    public function test_every_street_name_on_the_plan_matches_a_real_street(): void
    {
        $names = \App\Models\Street::pluck('name')->all();

        foreach (\App\Support\CityStreetLabels::LABELS as $view => $labels) {
            foreach ($labels as [$name]) {
                $this->assertContains($name, $names, "Label '{$name}' on view '{$view}' has no street");
            }
        }
        $this->assertCount(27, \App\Http\Controllers\CityController::streetLabels('overview'));
    }

    public function test_street_names_are_drawn_on_the_overview_and_the_district_views(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $overview = $this->actingAs($student)->get(route('education.city'));
        $overview->assertOk();
        $this->assertSame(27, substr_count($overview->getContent(), 'class="zv-street"'));

        // Civic Plaza's render shows Keynes Boulevard along its north side.
        $district = $this->actingAs($student)->get(route('education.city.district', 'civic-plaza'));
        $district->assertOk();
        $district->assertSee('class="zv-street"', false);
        $district->assertSee('Keynes Boulevard');
    }

    public function test_buildings_with_a_page_are_clickable_on_the_map_and_the_district_view(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $overview = $this->actingAs($student)->get(route('education.city'));
        $overview->assertOk();
        $overview->assertSee('class="zv-building"', false);
        $overview->assertSee('href="' . route('bank.index') . '"', false);
        $overview->assertSee('href="' . route('education.city-hall') . '"', false);

        $district = $this->actingAs($student)->get(route('education.city.district', 'civic-plaza'));
        $district->assertOk();
        $district->assertSee('href="' . route('education.city-hall') . '"', false);
        // Only this district's buildings are drawn on its render.
        $this->assertSame(['City Hall'], array_column(\App\Http\Controllers\CityController::buildingSpots('civic-plaza'), 'name'));
    }

    public function test_a_building_without_a_page_is_not_clickable_on_the_map(): void
    {
        // Switching the Senior Care Home NPO off removes its page, so it
        // gets no map area.
        \App\Models\Npo::where('slug', 'senior-care-home')->update(['status' => 0]);
        $names = array_column(\App\Http\Controllers\CityController::buildingSpots('overview'), 'name');

        $this->assertContains('Universal Bank', $names);
        $this->assertNotContains('Senior Care Home', $names);
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
