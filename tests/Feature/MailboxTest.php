<?php

namespace Tests\Feature;

use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for MailboxController - written while extracting it
 * out of ProfileController (planning.md Phase 3 #4). Previously
 * uncovered.
 */
class MailboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailbox_index_shows_only_the_logged_in_students_mail(): void
    {
        $user = User::factory()->create(['role' => 4]);
        $other = User::factory()->create(['role' => 4]);

        Mailbox::create(['student_id' => $user->id, 'subject' => 'Mine', 'content' => 'hi', 'type' => 'primary', 'read' => 0]);
        Mailbox::create(['student_id' => $other->id, 'subject' => 'Not mine', 'content' => 'hi', 'type' => 'primary', 'read' => 0]);

        $response = $this->actingAs($user)->get('/profile/mailbox');

        $response->assertOk();
        $response->assertViewHas('primaryMailbox', fn ($mail) => $mail->count() === 1 && $mail->first()->subject === 'Mine');
    }

    public function test_showing_a_mail_marks_it_read_and_scopes_to_the_owner(): void
    {
        $user = User::factory()->create(['role' => 4]);
        $mail = Mailbox::create(['student_id' => $user->id, 'subject' => 'Hello', 'content' => 'body', 'type' => 'primary', 'read' => 0]);

        $response = $this->actingAs($user)->get('/profile/mailbox/show/' . base64_encode($mail->id));

        $response->assertOk();
        $this->assertEquals(1, $mail->fresh()->read);
    }

    public function test_cannot_show_another_students_mail(): void
    {
        $user = User::factory()->create(['role' => 4]);
        $other = User::factory()->create(['role' => 4]);
        $mail = Mailbox::create(['student_id' => $other->id, 'subject' => 'Hello', 'content' => 'body', 'type' => 'primary', 'read' => 0]);

        $response = $this->actingAs($user)->get('/profile/mailbox/show/' . base64_encode($mail->id));

        $response->assertStatus(400);
    }

    public function test_update_mail_status_moves_mail_between_folders(): void
    {
        $user = User::factory()->create(['role' => 4]);
        $mail = Mailbox::create(['student_id' => $user->id, 'subject' => 'Hello', 'content' => 'body', 'type' => 'primary', 'read' => 0]);

        $response = $this->actingAs($user)->postJson('/profile/mailbox/update-status', [
            'mail_id' => $mail->id,
            'status_type' => 'starred',
        ]);

        $response->assertOk();
        $this->assertEquals('starred', $mail->fresh()->type);
    }
}
