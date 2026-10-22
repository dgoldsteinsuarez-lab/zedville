<?php

namespace Tests\Feature\Auth;

use App\Models\BankAccount;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Each role goes somewhere different after logging in
     * (AuthenticatedSessionController::store): a student to the next
     * Citizen Activation step they haven't done, or the dashboard once
     * they're set up; admins and tutors to the admin dashboard.
     */
    public function test_a_new_student_is_sent_to_the_first_citizen_activation_step(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->post('/login', ['email' => $student->email, 'password' => 'password']);

        $this->assertAuthenticated();
        $response->assertRedirect(route('CitizenActivation.bankAccount'));
    }

    public function test_a_student_who_finished_citizen_activation_lands_on_the_dashboard(): void
    {
        $student = User::factory()->create(['role' => 4]);
        BankAccount::create([
            'student_id' => $student->id, 'student_name' => $student->name, 'student_email' => $student->email,
            'primary_savings_account_amount' => 3952.40,
        ]);
        DB::table('auto_debit_requests')->insert(['user_id' => $student->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('consumer_surveys')->insert(['user_id' => $student->id, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->post('/login', ['email' => $student->email, 'password' => 'password']);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_an_admin_lands_on_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
