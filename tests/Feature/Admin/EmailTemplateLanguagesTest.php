<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendMailboxEmail;
use App\Models\EmailTemplate;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-25: "para los mensajes, debería haber templates para cada
 * idioma". An admin's template has a main (English) version plus optional
 * versions in the other languages; each student reads the one in their
 * language, or the English one when theirs was left empty.
 */
class EmailTemplateLanguagesTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    private function templateWithSpanish(array $extra = []): EmailTemplate
    {
        return EmailTemplate::create($extra + [
            'name' => 'Trip',
            'subject' => 'School trip',
            'content' => 'Hello {{name}}, pack your bag!',
            'translations' => ['es' => ['subject' => 'Excursión del colegio', 'content' => 'Hola, {{name}}: ¡prepara la mochila!']],
        ]);
    }

    public function test_an_admin_can_write_a_template_in_each_language(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/email-templates', [
            'name' => 'Trip',
            'subject' => 'School trip',
            'content' => 'Pack your bag!',
            'translations' => [
                'es' => ['subject' => 'Excursión del colegio', 'content' => '¡Prepara la mochila!'],
                'fr' => ['subject' => '', 'content' => ''],
                'de' => ['subject' => 'Klassenfahrt', 'content' => 'Pack deine Tasche!'],
            ],
        ])->assertRedirect('/admin/email-templates');

        $template = EmailTemplate::where('name', 'Trip')->firstOrFail();
        $this->assertSame(['es', 'de'], array_keys($template->versions()));
        $this->assertSame('Klassenfahrt', $template->translations['de']['subject']);

        $this->actingAs($this->superAdmin())->get("/admin/email-templates/{$template->id}/edit")
            ->assertOk()->assertSee('Español')->assertSee('Excursión del colegio');
    }

    public function test_a_language_needs_both_its_subject_and_its_content(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/email-templates', [
            'name' => 'Trip',
            'subject' => 'School trip',
            'content' => 'Pack your bag!',
            'translations' => ['fr' => ['subject' => 'Sortie scolaire', 'content' => '']],
        ])->assertSessionHasErrors('translations.fr.content');

        $this->assertDatabaseMissing('email_templates', ['name' => 'Trip']);
    }

    public function test_a_campaign_message_is_read_in_the_students_language(): void
    {
        $template = $this->templateWithSpanish();
        $spanish = User::factory()->create(['role' => 4, 'nickname' => 'Lucía', 'preferred_language' => 'es']);
        $french = User::factory()->create(['role' => 4, 'nickname' => 'Hugo', 'preferred_language' => 'fr']);

        $this->actingAs($this->superAdmin())->post('/admin/email-campaigns', [
            'template_id' => $template->id,
            'name' => 'Trip blast',
            'scope_type' => 'selected',
            'user_ids' => [$spanish->id, $french->id],
            'trigger_type' => 'immediate',
        ]);

        $toLucia = Mailbox::where('student_id', $spanish->id)->firstOrFail();
        app()->setLocale('es');
        $this->assertSame('Excursión del colegio', $toLucia->subject);
        $this->assertSame('Hola, Lucía: ¡prepara la mochila!', $toLucia->content);

        // No French version: the English one.
        $toHugo = Mailbox::where('student_id', $french->id)->firstOrFail();
        app()->setLocale('fr');
        $this->assertSame('School trip', $toHugo->subject);
        $this->assertSame('Hello Hugo, pack your bag!', $toHugo->content);

        // And the student's own mailbox page shows it.
        $this->actingAs($spanish)->get(route('profile.mailbox'))->assertOk()->assertSee('Excursión del colegio');
    }

    public function test_an_automatic_message_an_admin_rewrote_is_read_in_the_students_language(): void
    {
        $this->templateWithSpanish(['trigger_event' => 'welcome']);
        $student = User::factory()->create(['role' => 4, 'nickname' => 'Lucía', 'citizenId' => 'ZV-9']);

        (new SendMailboxEmail($student->id, 'welcome', 'Welcome, {{name}}'))->handle();

        $mail = Mailbox::where('student_id', $student->id)->firstOrFail();
        $this->assertNull($mail->getAttributes()['template']);

        app()->setLocale('es');
        $this->assertSame('Excursión del colegio', $mail->subject);
        $this->assertSame('Hola, Lucía: ¡prepara la mochila!', $mail->content);

        app()->setLocale('en');
        $this->assertSame('Welcome, Lucía', $mail->subject);
        $this->assertSame('Hello Lucía, pack your bag!', $mail->content);
    }
}
