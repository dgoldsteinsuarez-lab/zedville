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

    private function studentInClass(string $languageMode = 'free', string $defaultLanguage = 'en', string $languageLevel = 'standard'): User
    {
        $level = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create([
            'sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => $languageMode, 'default_language' => $defaultLanguage,
            'language_level' => $languageLevel,
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

    /**
     * Daniel, 2026-10-22: "also adding french and german as languages".
     */
    public function test_a_student_can_switch_to_french_and_to_german(): void
    {
        $student = $this->studentInClass('free');

        $this->actingAs($student)->post(route('language.switch'), ['language' => 'fr'])->assertRedirect();
        $this->assertSame('fr', $student->fresh()->preferred_language);
        $this->actingAs($student)->get(route('dashboard'))->assertOk()->assertSee('Accueil');
        $this->actingAs($student)->get(route('academy.index'))->assertOk()->assertSee('Académie');
        $this->actingAs($student)->get(route('pisa.index'))->assertOk()->assertSee("Qu'est-ce que PISA ?");

        $this->actingAs($student)->post(route('language.switch'), ['language' => 'de'])->assertRedirect();
        $this->assertSame('de', $student->fresh()->preferred_language);
        $this->actingAs($student)->get(route('dashboard'))->assertOk()->assertSee('Startseite');
        $this->actingAs($student)->get(route('academy.index'))->assertOk()->assertSee('Akademie');
        $this->actingAs($student)->get(route('pisa.index'))->assertOk()->assertSee('Was ist PISA?');
    }

    public function test_a_class_can_be_locked_to_french(): void
    {
        $student = $this->studentInClass('locked', 'fr');

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Accueil');
        $response->assertSee('<html lang="fr" translate="no">', false);
    }

    public function test_a_language_that_is_not_offered_is_rejected(): void
    {
        $student = $this->studentInClass('free');

        $this->actingAs($student)->post(route('language.switch'), ['language' => 'it'])
            ->assertSessionHasErrors('language');

        // The beginner version is the teacher's choice, not something a
        // student can pick for themselves.
        $this->actingAs($student)->post(route('language.switch'), ['language' => 'en_easy'])
            ->assertSessionHasErrors('language');

        $this->assertNull($student->fresh()->preferred_language);
    }

    /**
     * Daniel, 2026-10-22: a beginner (A2/B1) version of every language, set
     * per class by the teacher. lang/<code>_easy only holds the texts that
     * are written more simply - every other text must still come from the
     * standard version of that same language, never from English.
     */
    public function test_a_beginner_class_sees_the_simpler_wording_and_the_standard_text_for_the_rest(): void
    {
        $student = $this->studentInClass('locked', 'es', 'beginner');

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Visita la ciudad y sus edificios.');            // lang/es_easy
        $response->assertDontSee('Explora la ciudad y visita sus edificios'); // the lang/es text it replaces
        $response->assertSee('Ir a la ciudad');                               // not in es_easy -> lang/es
        $response->assertSee('Inicio');
        $response->assertSee('<html lang="es" translate="no">', false);
    }

    public function test_a_standard_class_keeps_the_standard_wording(): void
    {
        $student = $this->studentInClass('locked', 'es');

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Explora la ciudad y visita sus edificios');
        $response->assertDontSee('Visita la ciudad y sus edificios.');
    }

    public function test_a_beginner_class_uses_the_beginner_version_of_whatever_language_the_student_picks(): void
    {
        $student = $this->studentInClass('free', 'en', 'beginner');

        $english = $this->actingAs($student)->get(route('dashboard'));
        $english->assertOk();
        $english->assertSee('Visit the city and its buildings.');
        $english->assertSee('Go to the city');

        $this->actingAs($student)->post(route('language.switch'), ['language' => 'de'])->assertRedirect();

        $german = $this->actingAs($student)->get(route('dashboard'));
        $german->assertOk();
        $german->assertSee('Besuche die Stadt und ihre Gebäude.');
        $german->assertSee('In die Stadt gehen');
        $german->assertSee('<html lang="de" translate="no">', false);
        $german->assertDontSee('DE_EASY');

        $pisa = $this->actingAs($student)->get(route('pisa.index'));
        $pisa->assertOk();
        $pisa->assertSee('PISA ist ein echter Test für Schüler in vielen Ländern.');
        $pisa->assertSee('Was ist PISA?');
    }

    public function test_a_beginner_class_still_gets_real_messages_for_texts_nobody_simplified(): void
    {
        $student = $this->studentInClass('free', 'es', 'beginner');

        // validation.php only exists in English - a beginner Spanish class
        // must get that message, not a raw "validation.in" key.
        $this->actingAs($student)->post(route('language.switch'), ['language' => 'it'])
            ->assertSessionHasErrors(['language' => 'The selected language is invalid.']);
    }

    public function test_beginner_texts_are_only_used_for_the_beginner_class(): void
    {
        $beginner = $this->studentInClass('locked', 'es', 'beginner');
        $standard = $this->studentInClass('locked', 'es');

        $this->actingAs($beginner)->get(route('dashboard'))->assertSee('Visita la ciudad y sus edificios.');
        $this->actingAs($standard)->get(route('dashboard'))->assertSee('Explora la ciudad y visita sus edificios');
        $this->actingAs($beginner)->get(route('dashboard'))->assertSee('Visita la ciudad y sus edificios.');
    }
}
