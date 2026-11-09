<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-31: "where can I add a grade with a language for A2/B1
 * level or hybrid here?" - the Add a Grade form has the language level, and
 * the Grade's Classes start with it.
 */
class GradeLanguageLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_grade_is_added_with_its_language_level_and_its_classes_get_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin)->withSession(['selected_school' => 1])->get(route('admin.grades.index'))->assertOk()
            ->assertSee('Language level')->assertSee('Beginner (A2/B1 - simpler wording)')->assertSee('Hybrid (English + this language)');

        $this->actingAs($admin)->withSession(['selected_school' => 1])->post(route('admin.grades.store'), [
            'name' => 'Grade 9 Spanish', 'default_language' => 'es', 'language_level' => 'hybrid',
        ])->assertRedirect();

        $grade = Grade::where('name', 'Grade 9 Spanish')->first();
        $this->assertSame(['es', 'hybrid'], [$grade->default_language, $grade->language_level]);

        $this->actingAs($admin)->withSession(['selected_school' => 1])->post(url('admin/grade/add'), ['name' => '9S', 'grade_id' => $grade->id]);
        $class = SchoolClass::where('grade_id', $grade->id)->first();
        $this->assertSame(['es', 'hybrid'], [$class->default_language, $class->language_level]);

        $this->actingAs($admin)->withSession(['selected_school' => 1])->get(route('admin.grades.index'))->assertSee('Hybrid + English');
    }
}
