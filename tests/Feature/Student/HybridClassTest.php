<?php

namespace Tests\Feature\Student;

use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\Hybrid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-29: "create an hibrid class between english and the other
 * languages ... for students that speak little Spanish, French or German ...
 * complicated explanation in English, some double, the language to learn
 * bigger, and some in Spanish, like supermarket products".
 */
class HybridClassTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Hybrid::enable(false);
        app()->setLocale('en');
        parent::tearDown();
    }

    private function studentInClass(string $language = 'es', string $level = 'hybrid'): User
    {
        $gradeLevel = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $gradeLevel->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid(),
            'language_mode' => 'locked', 'default_language' => $language, 'language_level' => $level]);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    public function test_words_stay_in_the_language_to_learn_short_texts_come_in_both_and_explanations_in_english(): void
    {
        app()->setLocale('es');
        Hybrid::enable();

        $this->assertSame('Enviar', __('bank_ui.send'));                                  // a word
        $this->assertSame('Pollo', ct('products', 'Chicken'));                           // a product
        $both = Hybrid::plain(__('bank_ui.choice_friend'));
        $this->assertSame('Enviar a un amigo o amiga (Send to a friend)', $both);        // a short text
        $this->assertStringStartsWith('An <b>emergency</b> fund is your safety net', __('efd.ef_explainer')); // an explanation

        // asked for in one language on purpose: as it is
        $this->assertSame('Send to a friend', trans('bank_ui.choice_friend', [], 'en'));
        // a format isn't mixed
        $this->assertSame(trans('transactions.month_year', [], 'es'), __('transactions.month_year'));
    }

    public function test_a_page_shows_both_languages_with_the_english_smaller_and_no_marks_leak(): void
    {
        $student = $this->studentInClass('es');
        BankAccount::create(['student_id' => $student->id, 'sid' => 1, 'student_name' => $student->name, 'student_email' => $student->email]);

        $html = $this->actingAs($student)->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="es"', $html);
        $this->assertStringContainsString('<span class="zv-hy-e" lang="en">', $html);
        $this->assertStringContainsString('id="zv-hybrid"', $html); // the style that makes English smaller
        // and orange: burnt on a light background, strong on a dark one (Daniel, 2026-10-31)
        $this->assertStringContainsString('color:#C2410C', $html);
        $this->assertStringContainsString('.zv-hy-e.is-on-dark{color:#FF922B}', $html);
        $this->assertStringContainsString('id="zv-hybrid-colour"', $html);
        $this->assertStringNotContainsStringIgnoringCase('zvhy', $html);
        $this->assertStringNotContainsStringIgnoringCase('zven', $html);
        $this->assertStringContainsString('Inicio', $html);
    }

    public function test_attributes_scripts_and_options_get_the_plain_form(): void
    {
        app()->setLocale('es');
        Hybrid::enable();
        $text = __('bank_ui.choice_friend');
        $html = '<html><head></head><body><a title="' . e($text) . '" x-on:click="items.map(i => i)">' . e($text) . '</a>'
            . '<script>var t = ' . json_encode($text) . ';</script><select><option>' . e($text) . '</option></select></body></html>';

        $out = Hybrid::html($html);

        $this->assertStringContainsString('title="Enviar a un amigo o amiga (Send to a friend)"', $out);
        $this->assertStringContainsString('<option>Enviar a un amigo o amiga (Send to a friend)</option>', $out);
        $this->assertStringContainsString('var t = "Enviar a un amigo o amiga (Send to a friend)";', $out);
        $this->assertStringContainsString('<span class="zv-hy-t">Enviar a un amigo o amiga</span>', $out);
    }

    public function test_an_english_class_and_other_classes_are_not_mixed(): void
    {
        $english = $this->studentInClass('en');
        $this->actingAs($english)->get(route('dashboard'))->assertOk()->assertDontSee('zv-hy-e', false);

        $standard = $this->studentInClass('es', 'standard');
        $this->actingAs($standard)->get(route('dashboard'))->assertOk()->assertDontSee('zv-hy-e', false);

        $admin = User::factory()->create(['role' => 1]);
        $this->actingAs($admin)->get('/admin/dashboard')->assertDontSee('zv-hy-e', false);
    }

    public function test_a_tutor_and_an_admin_can_make_a_class_hybrid(): void
    {
        $student = $this->studentInClass('fr', 'standard');
        $tutor = User::factory()->create(['role' => 3, 'sid' => 1, 'grade' => $student->grade]);
        SchoolClass::find($student->grade)->tutors()->attach($tutor->id);

        $this->actingAs($tutor)->put(route('admin.tutor.class-language', $student->grade), [
            'language_mode' => 'locked', 'default_language' => 'fr', 'language_level' => 'hybrid',
        ])->assertSessionHasNoErrors();

        $this->assertSame('hybrid', SchoolClass::find($student->grade)->language_level);
        $this->actingAs($student)->get(route('dashboard'))->assertSee('<span class="zv-hy-e" lang="en">', false);
    }
}
