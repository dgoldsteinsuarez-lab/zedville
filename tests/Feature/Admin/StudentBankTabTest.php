<?php

namespace Tests\Feature\Admin;

use App\Models\Avatar;
use App\Models\SalaryBonus;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bank Admin Functions doc, Screen 2: balance, Reset Balance, Reverse
 * Transaction, and Salary Management all live on the student's own detail
 * page, rather than three disconnected admin tools each with their own
 * student picker (the actual gap found when comparing the doc to the live
 * app). AdminStudentController::details() now loads the data; the existing
 * LedgerController/SalaryBonusController routes handle the actions
 * unchanged, just invoked from this page instead of their old standalone
 * index pages.
 */
class StudentBankTabTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pre-existing, unrelated bug: the details view reads
     * $student->avatarRelation->name unconditionally, which errors when a
     * factory-created student has no avatar row - see planning.md Phase 3 #4.
     * Not this feature's bug to fix; worked around by giving test students one.
     */
    private function studentWithAvatar(array $attrs = []): User
    {
        $avatar = Avatar::create(['name' => 'default.png']);
        return User::factory()->create(array_merge(['role' => 4, 'avatar' => $avatar->id], $attrs));
    }

    public function test_student_details_page_shows_balance_and_transaction_history(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = $this->studentWithAvatar();

        Transaction1::create([
            'user_id' => $student->id,
            'sid' => $student->sid,
            'bank_account_id' => null,
            'transaction_date' => now(),
            'description' => 'Allowance',
            'type' => 'credit',
            'category' => 'Salary',
            'amount' => 500,
            'balance' => 500,
            'is_penalty' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/admin/student/details/' . $student->id);

        $response->assertOk();
        $response->assertSee('500.00');
        $response->assertSee('Allowance');
    }

    public function test_reverse_transaction_from_student_details_redirects_back_there(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = $this->studentWithAvatar();

        $txn = Transaction1::create([
            'user_id' => $student->id,
            'sid' => $student->sid,
            'bank_account_id' => null,
            'transaction_date' => now(),
            'description' => 'Allowance',
            'type' => 'credit',
            'category' => 'Salary',
            'amount' => 500,
            'balance' => 500,
            'is_penalty' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/student/details/' . $student->id)
            ->post(route('admin.ledger.reverse', $txn->id), ['reason' => 'Wrong amount entered']);

        $response->assertRedirect('/admin/student/details/' . $student->id);
        $this->assertDatabaseHas('transactions1', ['user_id' => $student->id, 'category' => 'Reversal']);
    }

    public function test_reset_balance_from_student_details_redirects_back_there(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = $this->studentWithAvatar();

        Transaction1::create([
            'user_id' => $student->id, 'sid' => $student->sid, 'bank_account_id' => null,
            'transaction_date' => now(), 'description' => 'Allowance', 'type' => 'credit',
            'category' => 'Salary', 'amount' => 500, 'balance' => 500, 'is_penalty' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/student/details/' . $student->id)
            ->post(route('admin.ledger.reset'), [
                'student_id' => $student->id,
                'new_balance' => 1500,
                'reason' => 'Scenario reset',
            ]);

        $response->assertRedirect('/admin/student/details/' . $student->id);
        $this->assertDatabaseHas('transactions1', ['user_id' => $student->id, 'category' => 'Admin Adjustment', 'balance' => 1500]);
    }

    public function test_salary_entry_type_is_saved_and_displayed(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = $this->studentWithAvatar();

        $response = $this->actingAs($admin)
            ->from('/admin/student/details/' . $student->id)
            ->post(route('admin.salary-bonuses.store'), [
                'student_id' => $student->id,
                'amount' => 300,
                'type' => 'Allowance',
                'reason' => 'Weekly allowance',
                'start_date' => now()->toDateString(),
                'frequency' => 'weekly',
            ]);

        $response->assertRedirect('/admin/student/details/' . $student->id);
        $this->assertDatabaseHas('salary_bonuses', ['student_id' => $student->id, 'type' => 'Allowance']);

        $detailsResponse = $this->actingAs($admin)->get('/admin/student/details/' . $student->id);
        $detailsResponse->assertSee('Allowance');
    }

    public function test_salary_type_defaults_to_bonus_when_omitted(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($admin)->post(route('admin.salary-bonuses.store'), [
            'student_id' => $student->id,
            'amount' => 100,
            'reason' => 'One-time top-up',
            'start_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('salary_bonuses', ['student_id' => $student->id, 'type' => 'Bonus']);
    }

    public function test_non_super_admin_cannot_see_reset_or_reverse_controls(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);
        $student = $this->studentWithAvatar(['sid' => $schoolAdmin->sid]);

        $response = $this->actingAs($schoolAdmin)->get('/admin/student/details/' . $student->id);

        $response->assertOk();
        $response->assertDontSee('Reset balance');
        $response->assertDontSee(route('admin.ledger.reset'), false);
    }
}
