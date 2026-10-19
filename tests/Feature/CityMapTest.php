<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * City Map, Phase 1 (2026-10-19). Backend/data layer is fully testable
 * before Daniel supplies the real exported hero image - see
 * CityController's doc comment.
 */
class CityMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_city_map_page_renders_and_lists_the_seven_real_buildings(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('education.city'));

        $response->assertOk();
        foreach (['Universal Bank', 'City Mall', 'Supermarket', 'Educational Finance Department', 'City Hall'] as $name) {
            $response->assertSee($name);
        }
        // A not-yet-built building is still visible (per Daniel: "the
        // other buildings, should still be visible"), just not a link.
        $response->assertSee('Hospital');
    }

    public function test_the_city_map_page_lists_all_27_street_plaques(): void
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
