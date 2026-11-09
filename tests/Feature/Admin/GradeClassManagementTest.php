<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel: "it's easier to make a grade and than add the classes? ... If I
 * am in Classes, I should be able to handle them easier inside each
 * grade." The flat Classes screen (admin/grade, grade_id picked from a
 * dropdown) was the only place to add/edit/delete a Class - the per-Grade
 * "General Information" page only showed them read-only. Added Add/Edit/
 * Delete directly to that page, posting to the exact same existing
 * AdminDashboardController endpoints the flat Classes screen already
 * used (no new business logic, no grade_id to pick wrong - it's fixed to
 * the Grade you're already on). Also moved "Grade levels" management off
 * the Classes screen onto the Grades screen, since a Level belongs to a
 * Grade, not a Class (bugs.md, 2026-10-20).
 */
class GradeClassManagementTest extends TestCase
{
    use RefreshDatabase;

    private function schoolAndGrade(): array
    {
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => $school->id, 'name' => 'Grade 8']);

        return [$school, $grade];
    }

    /**
     * Daniel, 2026-10-21: "the school admin, tutor, can decide if the
     * student can switch languages."
     */
    public function test_an_admin_can_lock_a_new_class_to_a_language(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);

        $this->actingAs($admin)->post('/admin/grade/add', [
            'name' => '8A', 'grade_id' => $grade->id,
            'default_language' => 'es', 'language_mode' => 'locked',
        ])->assertRedirect();

        $this->assertDatabaseHas('classes', [
            'name' => '8A', 'language_mode' => 'locked', 'default_language' => 'es',
        ]);
    }

    /**
     * Daniel, 2026-10-22: French and German, and a beginner (A2/B1) version
     * of every language that the teacher sets per class.
     */
    public function test_an_admin_can_set_a_class_to_french_or_german_at_beginner_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);

        $this->actingAs($admin)->post('/admin/grade/add', [
            'name' => '8A', 'grade_id' => $grade->id,
            'default_language' => 'fr', 'language_level' => 'beginner',
        ])->assertRedirect();

        $this->assertDatabaseHas('classes', [
            'name' => '8A', 'default_language' => 'fr', 'language_level' => 'beginner',
        ]);

        $class = SchoolClass::where('name', '8A')->firstOrFail();

        $this->actingAs($admin)->put('/admin/grade/edit/' . $class->id, [
            'name' => '8A', 'default_language' => 'de', 'language_level' => 'standard',
        ])->assertRedirect();

        $class->refresh();
        $this->assertSame('de', $class->default_language);
        $this->assertSame('standard', $class->language_level);
    }

    public function test_a_new_class_is_standard_level_unless_the_admin_picks_beginner(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);

        $this->actingAs($admin)->post('/admin/grade/add', [
            'name' => '8B', 'grade_id' => $grade->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('classes', ['name' => '8B', 'language_level' => 'standard']);
    }

    public function test_a_language_or_level_that_is_not_offered_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);

        $this->actingAs($admin)->post('/admin/grade/add', [
            'name' => '8C', 'grade_id' => $grade->id, 'default_language' => 'it',
        ])->assertSessionHasErrors('default_language');

        $this->actingAs($admin)->post('/admin/grade/add', [
            'name' => '8D', 'grade_id' => $grade->id, 'language_level' => 'expert',
        ])->assertSessionHasErrors('language_level');

        $this->assertDatabaseMissing('classes', ['name' => '8C']);
        $this->assertDatabaseMissing('classes', ['name' => '8D']);
    }

    public function test_the_grade_page_offers_all_four_languages_and_the_beginner_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        SchoolClass::create([
            'sid' => $school->id, 'name' => '8A', 'grade_id' => $grade->id,
            'default_language' => 'de', 'language_level' => 'beginner',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.grades.general-information', $grade->id));

        $response->assertOk();
        foreach (['English', 'Español', 'Français', 'Deutsch'] as $language) {
            $response->assertSee($language);
        }
        $response->assertSee('name="language_level"', false);
        $response->assertSee('data-language-level="beginner"', false);
    }

    public function test_an_unchecked_lock_on_update_leaves_the_class_free(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        $class = SchoolClass::create([
            'sid' => $school->id, 'name' => '8A', 'grade_id' => $grade->id,
            'language_mode' => 'locked', 'default_language' => 'es',
        ]);

        $this->actingAs($admin)->put('/admin/grade/edit/' . $class->id, [
            'name' => '8A', 'default_language' => 'en',
            // language_mode omitted - an unchecked "Lock" checkbox.
        ])->assertRedirect();

        $this->assertSame('free', $class->fresh()->language_mode);
    }

    public function test_the_grade_page_shows_an_add_class_button_scoped_to_this_grade(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();

        $response = $this->actingAs($admin)->get(route('admin.grades.general-information', $grade->id));

        $response->assertOk();
        $response->assertSee('Add Class to Grade 8');
    }

    public function test_adding_a_class_from_the_grade_page_creates_it_under_that_grade(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);

        $response = $this->actingAs($admin)->post('/admin/grade/add', [
            'name' => '8A',
            'grade_id' => $grade->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['name' => '8A', 'grade_id' => $grade->id, 'sid' => $school->id]);
    }

    public function test_editing_a_class_from_the_grade_page_persists_changes(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);
        $class = SchoolClass::create(['sid' => $school->id, 'name' => '8A', 'grade_id' => $grade->id, 'classCode' => '123456']);

        $response = $this->actingAs($admin)->put('/admin/grade/edit/' . $class->id, [
            'name' => '8A Renamed',
            'classCode' => '123456',
            'grade_id' => $grade->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['id' => $class->id, 'name' => '8A Renamed']);
    }

    public function test_deleting_an_empty_class_from_the_grade_page_removes_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        session(['selected_school' => $school->id]);
        $class = SchoolClass::create(['sid' => $school->id, 'name' => '8A', 'grade_id' => $grade->id, 'classCode' => '123456']);

        $response = $this->actingAs($admin)->delete('/admin/grade/delete/' . $class->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_grade_page_lists_its_classes_with_edit_and_delete_actions(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        [$school, $grade] = $this->schoolAndGrade();
        SchoolClass::create(['sid' => $school->id, 'name' => '8A', 'grade_id' => $grade->id, 'classCode' => '111111']);
        SchoolClass::create(['sid' => $school->id, 'name' => '8B', 'grade_id' => $grade->id, 'classCode' => '222222']);

        $response = $this->actingAs($admin)->get(route('admin.grades.general-information', $grade->id));

        $response->assertOk();
        $response->assertSee('8A');
        $response->assertSee('8B');
    }

    public function test_grade_levels_management_moved_to_the_grades_page(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => SchoolDomain::create(['school_name' => 'Test School', 'school_code' => 'TS-' . uniqid(), 'school_domain' => uniqid() . '.test'])->id]);

        $response = $this->actingAs($admin)->get('/admin/grades');

        $response->assertOk();
        $response->assertSee('Grade levels');
        $response->assertSee('Rookie');
    }

    public function test_grade_levels_card_is_hidden_from_a_school_admin(): void
    {
        $school = SchoolDomain::create(['school_name' => 'Test School', 'school_code' => 'TS-' . uniqid(), 'school_domain' => uniqid() . '.test']);
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => $school->id]);

        $response = $this->actingAs($schoolAdmin)->get('/admin/grades');

        $response->assertOk();
        $response->assertDontSee('Grade levels');
    }

    public function test_classes_screen_no_longer_shows_grade_levels_management(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => SchoolDomain::create(['school_name' => 'Test School', 'school_code' => 'TS-' . uniqid(), 'school_domain' => uniqid() . '.test'])->id]);

        $response = $this->actingAs($admin)->get('/admin/grade');

        $response->assertOk();
        $response->assertDontSee('Grade levels');
    }
}
