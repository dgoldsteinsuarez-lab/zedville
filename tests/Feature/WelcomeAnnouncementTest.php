<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The welcome announcement that opens activation (Daniel, 2026-10-23): the
 * Mayor's broadcast, a first decision, the citizenship letter and the
 * mission briefing - before the bank account is opened.
 */
class WelcomeAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withMiddleware(\App\Http\Middleware\EnsureCitizenActivation::class);
    }

    private function newStudent(): User
    {
        return User::factory()->create(['role' => 4, 'sid' => '1', 'name' => 'Lucia Ferrer']);
    }

    public function test_a_new_student_starts_with_the_welcome(): void
    {
        $student = $this->newStudent();

        $this->actingAs($student)->get(route('dashboard'))
            ->assertRedirect(route('CitizenActivation.index'));
        $this->actingAs($student)->get(route('CitizenActivation.bankAccount'))
            ->assertRedirect(route('CitizenActivation.index'));

        $this->actingAs($student)->get(route('CitizenActivation.index'))
            ->assertOk()
            ->assertSee('Zedville TV')
            ->assertSee('The Mayor of Zedville')
            ->assertSee('Lucia Ferrer')
            ->assertSee('Your rights')
            ->assertSee('START MISSION')
            ->assertSee(route('CitizenActivation.welcome.accept'), false);
    }

    public function test_starting_the_mission_moves_on_to_the_bank_account(): void
    {
        $student = $this->newStudent();

        $this->actingAs($student)->post(route('CitizenActivation.welcome.accept'))
            ->assertRedirect(route('CitizenActivation.bankAccount'));

        $this->assertNotNull($student->fresh()->welcome_seen_at);
        $this->actingAs($student)->get(route('CitizenActivation.bankAccount'))->assertOk();

        // "Back" from the bank account step can still replay it.
        $this->actingAs($student)->get(route('CitizenActivation.index'))->assertOk()->assertSee('Zedville TV');
    }

    public function test_mid_activation_the_welcome_sends_you_to_your_current_step(): void
    {
        $student = $this->newStudent();
        $student->forceFill(['welcome_seen_at' => now()])->save();
        BankAccount::create([
            'student_id' => $student->id, 'sid' => '1', 'student_name' => $student->name, 'student_email' => $student->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);

        $this->actingAs($student)->get(route('CitizenActivation.index'))
            ->assertRedirect(route('CitizenActivation.salaryAuthorization'));
    }

    /**
     * Daniel, 2026-10-25: a German class's welcome showed in English - the
     * page was German, but his browser machine-translated it. Student pages
     * now tell the browser not to translate them.
     */
    public function test_a_german_class_reads_the_welcome_in_german_untranslated(): void
    {
        $class = \App\Models\SchoolClass::create(['sid' => '1', 'name' => '8C', 'default_language' => 'de', 'language_mode' => 'free', 'language_level' => 'beginner']);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'name' => 'Maxene', 'grade' => $class->id]);

        $this->actingAs($student)->get(route('CitizenActivation.index'))->assertOk()
            ->assertSee('<html lang="de" translate="no">', false)
            ->assertSee('<meta name="google" content="notranslate">', false)
            ->assertSee('Deine Schulturnschuhe sind kaputt.');
    }

    public function test_the_welcome_is_translated(): void
    {
        $student = $this->newStudent();
        app()->setLocale('es');

        $this->assertSame('Atención, Lucia. Zedville te necesita.', __('welcome.tv.headline', ['name' => 'Lucia']));
        $this->assertSame('INICIAR MISIÓN ▶', __('welcome.mission.start'));
        $this->assertCount(5, __('welcome.mission.stages'));
        $this->assertCount(6, __('welcome.mission.skills'));
    }
}
