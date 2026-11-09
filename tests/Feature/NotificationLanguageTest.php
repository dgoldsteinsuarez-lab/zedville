<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\BadgeCalculatorService;
use App\Support\Hybrid;
use App\Support\NotificationText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-30: "mood and badges can be in the language to learn, so,
 * you can translate them for hybrid".
 */
class NotificationLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Hybrid::enable(false);
        app()->setLocale('en');
        parent::tearDown();
    }

    public function test_the_mood_notice_reads_in_the_students_language(): void
    {
        $student = User::factory()->create(['role' => 4]);
        MoodLog::create(['user_id' => $student->id, 'mood' => 'ecstatic', 'sid' => 1, 'energy' => 8, 'pleasantness' => 9]);
        $notice = AppNotification::where('user_id', $student->id)->where('type', 'mood_logged')->sole();

        app()->setLocale('es');
        $this->assertSame(['Ánimo guardado', 'Hemos guardado tu ánimo de hoy: Eufórico/a.'], NotificationText::for($notice));
        app()->setLocale('de');
        $this->assertSame('Stimmung gespeichert', NotificationText::for($notice)[0]);
    }

    public function test_a_badge_notice_comes_with_the_first_calculation_and_reads_in_the_students_language(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $record = \App\Models\StudentBadgeRecord::create([
            'student_id' => $student->id, 'month' => now()->month, 'year' => now()->year, 'academic_year' => '2026-2027',
            'required_assigned' => 1, 'required_completed' => 1, 'optional_completed' => 3, 'participation_points' => 10,
            'monthly_badge' => 'GOLD', 'monthly_badge_points' => 3, 'accumulated_points' => 3, 'accumulated_badge' => 'NONE',
        ]);
        $notice = AppNotification::where('user_id', $student->id)->where('type', 'badge_earned')->sole();

        // recalculated later: the notice keeps the level it was earned with
        $record->update(['monthly_badge' => 'SILVER']);
        app()->setLocale('fr');
        $this->assertSame(['Badge Participation obtenu', 'Ce mois-ci, tu as obtenu le badge Participation de niveau Or !'], NotificationText::for($notice));
        app()->setLocale('es');
        $this->assertSame('¡Este mes has conseguido la insignia de Participación de nivel Oro!', NotificationText::for($notice)[1]);
    }

    public function test_a_hybrid_class_gets_them_mixed_with_english(): void
    {
        $student = User::factory()->create(['role' => 4]);
        MoodLog::create(['user_id' => $student->id, 'mood' => 'happy', 'sid' => 1, 'energy' => 8, 'pleasantness' => 9]);
        $notice = AppNotification::where('user_id', $student->id)->sole();

        app()->setLocale('es');
        Hybrid::enable();
        [$title, $body] = array_map([Hybrid::class, 'plain'], NotificationText::for($notice));

        $this->assertSame('Ánimo guardado', $title); // 2 words: the language to learn
        $this->assertStringStartsWith('Hemos guardado tu ánimo de hoy: Feliz. (Your mood for today has been saved:', $body);
    }

    public function test_every_language_has_the_notice_texts(): void
    {
        foreach (['es', 'fr', 'de'] as $locale) {
            foreach (array_keys(trans('notifications', [], 'en')) as $key) {
                $this->assertNotSame(trans('notifications.' . $key, [], 'en'), trans('notifications.' . $key, [], $locale), "$locale: $key");
            }
        }
    }
}
