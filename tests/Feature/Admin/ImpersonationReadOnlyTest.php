<?php

namespace Tests\Feature\Admin;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "if i am in tutor mode, i should not be able to
 * change something." Pairs with ImpersonateController's audit log
 * (impersonation_logs) - a Super Admin already has full DB access
 * regardless of this feature, so the point isn't restricting what can be
 * seen, it's not letting a "view as" session casually mutate another
 * user's data. BlockWritesWhileImpersonating (registered in the `web`
 * middleware group, Kernel.php) blocks every non-GET/HEAD request while
 * session('impersonator_id') is set, except returning to your own
 * account.
 */
class ImpersonationReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_write_request_is_blocked_while_impersonating(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $tutor = User::factory()->create(['role' => 3]);

        $this->actingAs($superAdmin)->post("/admin/impersonate/{$tutor->id}")->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($tutor);

        $response = $this->post('/admin/email-templates', [
            'name' => 'Should not be created',
            'subject' => 'x',
            'content' => 'x',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('email_templates', ['name' => 'Should not be created']);
    }

    public function test_a_read_request_still_works_while_impersonating(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $tutor = User::factory()->create(['role' => 3]);

        $this->actingAs($superAdmin)->post("/admin/impersonate/{$tutor->id}");

        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_returning_to_your_own_account_still_works_while_impersonating(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $tutor = User::factory()->create(['role' => 3]);

        $this->actingAs($superAdmin)->post("/admin/impersonate/{$tutor->id}");
        $this->assertAuthenticatedAs($tutor);

        $response = $this->post('/impersonate/stop');

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_writes_work_normally_outside_impersonation(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($superAdmin)->post('/admin/email-templates', [
            'name' => 'Fine on its own',
            'subject' => 'x',
            'content' => 'x',
        ]);

        $response->assertRedirect('/admin/email-templates');
        $this->assertDatabaseHas('email_templates', ['name' => 'Fine on its own']);
    }
}
