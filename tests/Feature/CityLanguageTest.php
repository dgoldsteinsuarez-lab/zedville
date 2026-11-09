<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * City screens in the class language: district and building names come
 * from lang/<language>/city.php by key, City Hall from cityhall.php.
 */
class CityLanguageTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $language): User
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => 'locked', 'default_language' => $language,
        ]);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    public function test_district_names_follow_the_class_language(): void
    {
        $this->actingAs($this->student('fr'))
            ->get(route('education.city'))
            ->assertOk()
            ->assertSee('Plan de la ville')
            ->assertSee('Quartier résidentiel')
            ->assertDontSee('Residential Quarter');
    }

    public function test_building_names_follow_the_class_language(): void
    {
        $this->actingAs($this->student('de'))
            ->get(route('education.city.district', 'civic-plaza'))
            ->assertOk()
            ->assertSee('Bürgerplatz')
            ->assertSee('Rathaus')
            ->assertDontSee('City Hall');
    }

    public function test_street_bios_follow_the_class_language(): void
    {
        // Marshall Street's bio, shown on the overview's street plaques and labels.
        $this->actingAs($this->student('es'))
            ->get(route('education.city'))
            ->assertOk()
            ->assertSee('curvas de oferta y demanda')
            ->assertDontSee('supply and demand curves');

        $this->actingAs($this->student('en'))
            ->get(route('education.city'))
            ->assertOk()
            ->assertSee('supply and demand curves');
    }

    public function test_the_civic_chamber_follows_the_class_language(): void
    {
        $this->actingAs($this->student('es'))
            ->get(route('education.civic-chamber'))
            ->assertOk()
            ->assertSee('Cámara Cívica')
            ->assertSee('Iniciar una nueva petición')
            ->assertDontSee('Start a new petition');
    }
}
