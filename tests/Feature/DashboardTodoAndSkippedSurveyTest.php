<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-23: "why do you write overdue if it's completed. Also, I
 * can still not do the Survey with Esperanza."
 */
class DashboardTodoAndSkippedSurveyTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        Transaction1::create([
            'user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => 1000, 'balance' => 1000,
        ]);

        return $user;
    }

    private function event(User $student, string $title): int
    {
        // Like the admin screens: started a few days ago, last day in a week
        // (`end` is stored one day later, the calendar treats it as exclusive).
        return DB::table('calendar_events')->insertGetId([
            'sid' => '1', 'classId' => $student->grade, 'title' => $title, 'position' => 'required', 'repeatActivity' => '0',
            'start' => now()->subDays(3)->startOfDay(), 'end' => now()->addDays(8)->startOfDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_the_to_do_list_shows_the_last_day_and_hides_finished_activities(): void
    {
        $student = $this->student();
        $finished = $this->event($student, 'Needs, Wants & Savings Match');
        $this->event($student, 'Emergency Fund');
        DB::table('activity_completions')->insert([
            'student_id' => $student->id, 'activity_id' => $finished, 'activity_type' => 'mba',
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $activities = $this->actingAs($student)->get(route('dashboard'))->assertOk()->viewData('activities');

        $this->assertSame(['Emergency Fund'], array_column($activities, 'title'));
        $this->assertSame(now()->addDays(7)->toDateString(), $activities[0]['date']);   // not "Overdue"
    }

    /** Daniel, 2026-10-25: "no puede clicar sobre actividades e ir a la actividad". */
    public function test_each_activity_links_to_its_page_and_shows_once(): void
    {
        $student = $this->student();
        $this->event($student, 'Emergency Fund');
        $this->event($student, 'Emergency Fund');   // put on the calendar twice
        $this->event($student, 'Needs, Wants & Savings Match');
        $this->event($student, 'task-Budget Calculations');
        $this->event($student, 'library-Introduction to Banking');
        foreach ([['task', 'task_2', 'Budget Calculations'], ['library', 'task_5', 'Introduction to Banking']] as [$type, $key, $name]) {
            DB::table('finhero_activity_registry')->insert([
                'sid' => null, 'cid' => $student->grade, 'type' => $type, 'activity_key' => $key, 'activity_name' => $name,
                'position' => 'required', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($student)->get(route('dashboard'))->assertOk();
        $links = array_column($response->viewData('activities'), 'url', 'title');

        $this->assertCount(4, $links);
        $this->assertSame(route('education.educational_finance_department', ['open' => 'emergency-fund']), $links['Emergency Fund']);
        $this->assertSame(route('education.index'), $links['Needs, Wants & Savings Match']);
        $this->assertSame(route('task.page', ['task' => 'task-2']), $links['Budget Calculations']);
        $this->assertSame(route('library.page', ['key' => 'task-5']), $links['Introduction to Banking']);
        $response->assertSee('href="' . route('task.page', ['task' => 'task-2']) . '"', false);
    }

    public function test_a_survey_skipped_in_activation_can_still_be_taken_for_that_month(): void
    {
        $student = $this->student();
        ConsumerSurvey::create([
            'user_id' => $student->id, 'sid' => '1', 'diet' => null, 'groceries' => [], 'basket_cost' => 0,
            'lunch' => 0, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'total_cost' => 0,
            'survey_month' => now()->format('M-Y'),
        ]);

        // The profile offers the survey, not locked zero results.
        $this->actingAs($student)->get(route('profile.edit'))->assertOk()->assertViewHas('ConsumersurveyExists', false);

        $this->actingAs($student)->postJson('/profile/storesurvey', [
            'diet' => 'omnivore',
            'groceries' => [['id' => 1, 'name' => 'Rice', 'unit_price' => 2, 'quantity' => 5, 'total_price' => 10]],
            'basketCost' => 10, 'lunch' => 20, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'totalCost' => 30,
        ])->assertOk()->assertJson(['status' => true]);

        $surveys = ConsumerSurvey::where('user_id', $student->id)->get();
        $this->assertCount(1, $surveys);                                   // replaced, not a second month
        $this->assertSame(now()->format('M-Y'), $surveys[0]->survey_month);
        $this->assertSame('omnivore', $surveys[0]->diet);
        $this->assertSame(970.0, (float) Transaction1::where('user_id', $student->id)->latest('id')->value('balance'));

        $this->actingAs($student)->get(route('profile.edit'))->assertViewHas('ConsumersurveyExists', true);
    }
}
