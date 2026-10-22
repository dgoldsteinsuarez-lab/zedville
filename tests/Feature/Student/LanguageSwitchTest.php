<?php

namespace Tests\Feature\Student;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "I want that the school admin, tutor, can decide if
 * the student can switch languages... the tutor might decide to only
 * have this in that specific language for beginners, to force the
 * students to understand it." A Class is 'free' (student's own choice,
 * falling back to the Class default) or 'locked' (always the Class's
 * default_language, no override).
 */
class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function studentInClass(string $languageMode = 'free', string $defaultLanguage = 'en'): User
    {
        $level = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => $languageMode, 'default_language' => $defaultLanguage,
        ]);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    public function test_a_new_class_defaults_to_free_english_unchanged_behaviour(): void
    {
        $student = $this->studentInClass();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Home');
    }

    public function test_a_student_in_a_free_class_can_switch_to_spanish(): void
    {
        $student = $this->studentInClass('free');

        $this->actingAs($student)->post(route('language.switch'), ['language' => 'es'])->assertRedirect();

        $this->assertSame('es', $student->fresh()->preferred_language);

        $response = $this->actingAs($student)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Inicio');
    }

    public function test_a_student_in_a_locked_class_cannot_switch(): void
    {
        $student = $this->studentInClass('locked', 'es');

        $response = $this->actingAs($student)->post(route('language.switch'), ['language' => 'en']);

        $response->assertForbidden();
        $this->assertNull($student->fresh()->preferred_language);
    }

    public function test_a_locked_class_always_shows_its_language_even_if_the_student_has_a_different_preference(): void
    {
        $student = $this->studentInClass('locked', 'es');
        $student->update(['preferred_language' => 'en']);

        $response = $this->actingAs($student)->get(route('dashboard'));

        // Not asserting dontSee('Home') - that plain English word can
        // legitimately appear elsewhere on the page outside the nav
        // (same false-positive trap documented earlier this week for
        // generic substrings). Seeing the Spanish label is the real proof.
        $response->assertOk();
        $response->assertSee('Inicio');
    }

    public function test_academy_and_pisa_render_in_spanish_once_switched(): void
    {
        $student = $this->studentInClass('free');
        $this->actingAs($student)->post(route('language.switch'), ['language' => 'es']);

        $academy = $this->actingAs($student)->get(route('academy.index'));
        $academy->assertOk();
        $academy->assertSee('Academia');

        $pisa = $this->actingAs($student)->get(route('pisa.index'));
        $pisa->assertOk();
        $pisa->assertSee('¿Qué es PISA?', false);
    }
}
