<?php

namespace Tests\Feature;

use App\Http\Controllers\EFDController;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Needs, Wants & Savings Match (Daniel, 2026-10-22): the student calendar
 * shows it, the page explains the activity, and a student without last
 * month's bank statement gets a simulated one.
 */
class NeedsWantsSavingsTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }

    public function test_the_student_calendar_reads_the_students_own_events(): void
    {
        $student = $this->student();
        DB::table('calendar_events')->insert([
            'sid' => '1', 'classId' => $student->grade, 'title' => 'Needs, Wants & Savings Match',
            'position' => 'required', 'repeatActivity' => '0',
            'start' => now()->startOfMonth(), 'end' => now()->endOfMonth(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // The page carries the student's own activities (option A, 2026-11-04),
        // never the admin-only endpoint (403 for students).
        $this->actingAs($student)->get(route('education.calendar'))
            ->assertOk()
            ->assertViewHas('calendar', fn ($calendar) => $calendar['activities'][0]['title'] === 'Needs, Wants & Savings Match')
            ->assertDontSee('/api/admin/calendar-events', false);

        $this->actingAs($student)->getJson(route('education.get-event-by-user'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Needs, Wants & Savings Match']);
    }

    public function test_a_student_without_last_months_statement_gets_a_simulated_one(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student)->get(route('education.index'));

        $response->assertOk()
            ->assertSee('How it works')
            ->assertSee('Needs:')
            ->assertSee('id="mbaSimulatedNote"', false)
            ->assertViewHas('simulated', true);

        $template = collect($response->viewData('template'));
        $this->assertSame(['income', 'needs', 'savings', 'wants'], $template->pluck('category')->unique()->sort()->values()->all());
        $salary = (float) config('zedville.monthly_salary');
        $this->assertEquals($salary, $template->firstWhere('id', 'sim-salary')['amount']);
        $this->assertEquals(-round($salary * 0.2, 2), $template->firstWhere('id', 'sim-emergency_fund')['amount']);
    }

    public function test_from_the_second_month_the_real_statement_is_used_and_sorted(): void
    {
        $student = $this->student();
        $lastMonth = now()->startOfMonth()->subMonth()->addDays(3);
        foreach ([
            ['Monthly Salary Credit', 'Income', 'credit', 3952.40],
            ['Rent', 'Needs', 'debit', 300],
            ['Purchase: Acoustic Guitar (Order #1)', 'Music Instruments', 'debit', 80],
            ['Purchase: Soap (Order #3)', 'Personal Care & Household', 'debit', 5],
            ['Auto Transfer to Savings Account', 'Savings', 'debit', 790.48],
        ] as [$description, $category, $type, $amount]) {
            $t = Transaction1::create([
                'user_id' => $student->id, 'sid' => '1', 'description' => $description,
                'category' => $category, 'type' => $type, 'amount' => $amount, 'balance' => 0,
                'transaction_date' => $lastMonth,
            ]);
            $t->forceFill(['created_at' => $lastMonth])->save();
        }

        $response = $this->actingAs($student)->get(route('education.index'));

        $response->assertOk()->assertViewHas('simulated', false)->assertDontSee('id="mbaSimulatedNote"', false);
        $byName = collect($response->viewData('template'))->keyBy('name');
        $this->assertSame('income', $byName['Monthly Salary Credit']['category']);
        $this->assertSame('needs', $byName['Rent']['category']);
        // Older per-item orders show as one line per order, with a clear name (2026-11-02).
        $this->assertSame('wants', $byName['City Mall purchase']['category']);
        $this->assertSame('needs', $byName['Supermarket purchase']['category']);
        $this->assertSame('savings', $byName['Auto Transfer to Savings Account']['category']);
    }

    public function test_every_category_lands_in_a_budget_column(): void
    {
        $this->assertSame('needs', EFDController::budgetCategory('Needs', 'Internet'));
        $this->assertSame('needs', EFDController::budgetCategory('Other', 'Utility - Water'));
        $this->assertSame('savings', EFDController::budgetCategory('Savings'));
        $this->assertSame('wants', EFDController::budgetCategory('Sports', 'Purchase: Competition Basketball'));
        $this->assertSame('wants', EFDController::budgetCategory('Donation'));
        $this->assertSame('wants', EFDController::budgetCategory('Fund Transfer', 'Transfer to Riley'));
    }
}
