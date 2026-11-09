<?php

namespace Tests\Feature;

use App\Models\LoginOption;
use App\Models\LoginQuestion;
use App\Models\User;
use App\Support\ContentTranslations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-25: "el supermercado está en inglés... las preguntas del
 * día también... también todos los productos". Products, supermarket items
 * and the daily questions are stored in English and shown translated.
 */
class ContentTranslationsTest extends TestCase
{
    use RefreshDatabase;

    private const LOCALES = ['es', 'fr', 'de'];

    private function translations(string $locale, string $group): array
    {
        return require resource_path("content-translations/{$locale}/{$group}.php");
    }

    public function test_every_daily_question_and_answer_is_translated(): void
    {
        $content = json_decode(file_get_contents(database_path('seeders/data/rookie_learning_content.json')), true);
        $texts = collect($content['login_months'])->flatMap(fn ($month) => collect($month['questions'])
            ->flatMap(fn ($q) => array_merge([$q['question']], $q['options'] ?? ['True', 'False'])))
            ->map(fn ($t) => trim($t))->unique();

        foreach (self::LOCALES as $locale) {
            $map = $this->translations($locale, 'daily_questions');
            $missing = $texts->reject(fn ($t) => isset($map[$t]))->values()->all();
            $this->assertSame([], $missing, "daily questions missing in {$locale}");
        }
    }

    public function test_every_shop_and_supermarket_product_is_translated(): void
    {
        $names = DB::table('products')->pluck('product_name')
            ->merge(DB::table('products')->pluck('category'))
            ->merge(DB::table('supermarkets')->pluck('product_name'))
            ->map(fn ($t) => trim($t))->filter()->unique();
        $this->assertGreaterThan(100, $names->count());

        foreach (self::LOCALES as $locale) {
            $map = $this->translations($locale, 'products');
            $missing = $names->reject(fn ($t) => isset($map[$t]))->values()->all();
            $this->assertSame([], $missing, "products missing in {$locale}");
        }
    }

    /** Every group (Library, Academy, activities...) has the same texts in es, fr and de. */
    public function test_every_group_has_the_same_texts_in_every_language(): void
    {
        foreach (['products', 'daily_questions', 'library', 'curriculum', 'activities'] as $group) {
            $keys = array_keys($this->translations('es', $group));
            $this->assertNotEmpty($keys, $group);
            foreach (['fr', 'de'] as $locale) {
                $this->assertSame([], array_values(array_diff($keys, array_keys($this->translations($locale, $group)))), "{$group}: missing in {$locale}");
                $this->assertSame([], array_values(array_diff(array_keys($this->translations($locale, $group)), $keys)), "{$group}: extra in {$locale}");
            }
        }
    }

    public function test_amounts_are_written_the_local_way(): void
    {
        app()->setLocale('es');
        $this->assertSame('Ƶ 1.800,00', ContentTranslations::get('curriculum', 'Ƶ 1,800.00'));
        $this->assertSame('15 %', ContentTranslations::get('curriculum', '15%'));
        app()->setLocale('fr');
        $this->assertSame('Ƶ 1 800,00', ContentTranslations::get('curriculum', 'Ƶ 1,800.00'));
        app()->setLocale('en');
        $this->assertSame('Ƶ 1,800.00', ContentTranslations::get('curriculum', 'Ƶ 1,800.00'));
    }

    /** A city message is shown in the reader's language, with the right name. */
    public function test_a_city_message_is_shown_in_the_readers_language(): void
    {
        $student = User::factory()->create(['role' => 4, 'name' => 'Lucia Ferrer', 'nickname' => null, 'citizenId' => 'ZV-1']);
        $mail = \App\Models\Mailbox::create([
            'student_id' => $student->id, 'subject' => 'Welcome to Zedville, !', 'content' => '<p>English</p>',
            'type' => 'primary', 'template' => 'welcome', 'read' => 0,
        ]);

        app()->setLocale('es');
        $this->assertSame('¡Bienvenido/a a Zedville, Lucia Ferrer!', $mail->fresh()->subject);
        $this->assertStringContainsString('Tu ID ciudadano:', $mail->fresh()->content);

        // A message an admin wrote by hand keeps its own text.
        $own = \App\Models\Mailbox::create(['student_id' => $student->id, 'subject' => 'Trip', 'content' => 'Hello', 'type' => 'primary', 'read' => 0]);
        $this->assertSame('Trip', $own->fresh()->subject);
    }

    /** A picture with a translated copy is shown in the student's language; any other stays as it is. */
    public function test_a_picture_is_shown_in_the_students_language(): void
    {
        $dir = public_path('uploads/curriculum');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($dir);
        $made = [$dir . '/zz_test_chart.svg', $dir . '/zz_test_chart.es.svg'];
        foreach ($made as $file) {
            file_put_contents($file, '<svg xmlns="http://www.w3.org/2000/svg"/>');
        }

        try {
            app()->setLocale('es');
            $this->assertSame('zz_test_chart.es.svg', ContentTranslations::image('zz_test_chart.svg'));
            $this->assertStringEndsWith('/uploads/curriculum/zz_test_chart.es.svg', ct_image('zz_test_chart.svg'));
            $this->assertSame('zz_admin_upload.png', ContentTranslations::image('zz_admin_upload.png'));

            app()->setLocale('fr');
            $this->assertSame('zz_test_chart.svg', ContentTranslations::image('zz_test_chart.svg'));

            app()->setLocale('en');
            $this->assertSame('zz_test_chart.svg', ContentTranslations::image('zz_test_chart.svg'));
        } finally {
            array_map('unlink', $made);
        }
    }

    /** Every PISA picture has a copy in es, fr and de (copied by PisaPracticeContentSeeder with it). */
    public function test_every_pisa_picture_is_translated(): void
    {
        $pictures = array_filter(glob(database_path('seeders/data/pisa_graphics/pisa_*.svg')), fn ($f) => substr_count(basename($f), '.') === 1);
        $this->assertCount(22, $pictures);

        foreach ($pictures as $picture) {
            preg_match_all('/<text\b[^>]*>(.*?)<\/text>/s', file_get_contents($picture), $english);
            foreach (self::LOCALES as $locale) {
                $copy = substr($picture, 0, -4) . ".{$locale}.svg";
                $this->assertFileExists($copy);
                preg_match_all('/<text\b[^>]*\/>|<text\b[^>]*>.*?<\/text>/s', file_get_contents($copy), $translated);
                $this->assertCount(count($english[0]), $translated[0], basename($copy) . ': same number of texts');
            }
        }
    }

    public function test_untranslated_text_and_english_stay_as_they_are(): void
    {
        app()->setLocale('es');
        $this->assertSame('A product added later', ContentTranslations::get('products', 'A product added later'));
        $this->assertNotSame('Plant-based Milk', ContentTranslations::get('products', 'Plant-based Milk'));

        app()->setLocale('en');
        $this->assertSame('Plant-based Milk', ContentTranslations::get('products', 'Plant-based Milk'));
    }

    public function test_the_question_of_the_day_is_served_in_the_students_language(): void
    {
        $student = User::factory()->create(['role' => 4, 'preferred_language' => 'es']);
        $question = LoginQuestion::create(['question' => 'What is a budget?', 'type' => 'mcq']);
        LoginOption::create(['question_id' => $question->id, 'option_text' => 'A plan for how you will use your money', 'is_correct' => 1]);
        LoginOption::create(['question_id' => $question->id, 'option_text' => 'A type of bank account', 'is_correct' => 0]);

        $data = $this->actingAs($student)->getJson(route('login.quiz.today'))->assertOk()->json();

        $this->assertTrue($data['show']);
        $this->assertSame($this->translations('es', 'daily_questions')['What is a budget?'], $data['question']);
        $this->assertContains($this->translations('es', 'daily_questions')['A type of bank account'], array_column($data['options'], 'text'));
    }
}
