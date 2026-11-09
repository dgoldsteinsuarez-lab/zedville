<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-28: the market list's picture (and every picture you had to
 * scroll to see whole - the City Map's districts) fits the screen like City
 * Hall; no "Home › ..." trails; a "Task" button on the Spending Activity's
 * shops with the cart's item count; and "Finish" at the bottom of every
 * activity.
 */
class FitPicturesTaskAndFinishTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade 9', 'grade_level_id' => GradeLevel::first()->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => '9A']);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    private function surveyDone(User $student): void
    {
        // the shops read the student's card (every activated citizen has one)
        BankAccount::create(['student_id' => $student->id, 'sid' => $student->sid, 'student_name' => $student->name,
            'student_email' => $student->email, 'primary_savings_account_amount' => 100, 'card_pin' => '1234']);
        ConsumerSurvey::create(['user_id' => $student->id, 'sid' => 1, 'diet' => 'omnivore', 'groceries' => [],
            'basket_cost' => 0, 'lunch' => 0, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'total_cost' => 0]);
    }

    public function test_both_market_lists_are_a_picture_that_fits_the_screen(): void
    {
        $student = $this->student();
        $this->surveyDone($student);

        foreach ([route('supermarket.market-list'), route('spendingactivities.market-list', ['activity_id' => 1])] as $url) {
            $this->actingAs($student)->get($url)->assertOk()
                ->assertSeeInOrder(['class="zs" data-zs', 'class="zvBackBtn zs-back"', 'aspect-ratio: 4608 / 3456', 'class="store s1"', 'class="store s2"', 'class="store s3"', 'class="store s4"',
                    '<h1 class="zs-title">Supermarket Market List</h1>', '<p class="zs-hint">Click a shelf to start shopping</p>'], false);
        }
        // each shelf goes into its own shop, in the activity too
        $this->actingAs($student)->get(route('spendingactivities.market-list', ['activity_id' => 1]))
            ->assertSee('href="' . route('spendingactivities.supermarket', ['type' => 'vegan', 'activity_id' => 1]) . '"', false);
    }

    public function test_before_the_survey_the_market_list_asks_for_it_with_the_usual_back(): void
    {
        $this->actingAs($this->student())->get(route('supermarket.market-list'))->assertOk()
            ->assertSee('id="openSurveyBtn"', false)
            ->assertDontSee('class="zs" data-zs', false)
            ->assertSee('zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-5', false); // the layout's Back
    }

    public function test_every_district_is_a_picture_that_fits_the_screen(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('education.city.district', 'financial-square'))->assertOk()
            ->assertSeeInOrder(['class="zs zs--outside zs--map zs--flat zv-street-map" data-zs', '<h1 class="zs-head-title">Financial Square</h1>',
                '<p class="zs-head-sub">District 2', 'aspect-ratio: 2400 / 1441', 'class="zv-street"',
                '<p class="zs-foot">Click a pin or a street name to find out more</p>'], false);
        foreach (['senior-quarter', 'market-energy', 'community-care', 'civic-plaza', 'residential-quarter', 'garden-homes', 'education-hub', 'future-developments'] as $district) {
            $this->actingAs($student)->get(route('education.city.district', $district))->assertOk()->assertSee('data-zs', false);
        }
    }

    public function test_no_breadcrumb_trails_are_left(): void
    {
        $student = $this->student();

        foreach ([route('education.civic-chamber'), route('education.well-being-room'), route('city-mood')] as $url) {
            $this->actingAs($student)->get($url)->assertOk()
                ->assertDontSee('class="cc-breadcrumb"', false)
                ->assertDontSee('<div class="breadcrumb">', false);
        }
    }

    public function test_the_shops_of_the_spending_activity_have_the_task_the_item_count_and_finish(): void
    {
        $student = $this->student();
        $this->surveyDone($student);
        $activity = Activity::where('title', 'Birthday Party Budget')->first();
        $department = route('education.educational_finance_department');

        foreach (['/spending-activities/omnivore?activity_id=' . $activity->id, '/spending-activities/spending-tracker/tech-hub?activity_id=' . $activity->id] as $url) {
            $this->actingAs($student)->get($url)->assertOk()
                ->assertSee('onclick="zvOpenTask()">📋 Task</button>', false)
                ->assertSeeInOrder(['Items in cart', 'id="cartItemCount"', 'id="checkoutTotal"'], false)
                ->assertSeeInOrder(['class="zv-finish"', 'href="' . $department . '" class="themeBtn zv-finish-btn">Finish</a>',
                    'id="zvTask"', 'Low Spending · Activity 1 of 12', 'Birthday Party Budget', 'Your shopping list', '3 proteins', 'Goes to a Friend'], false)
                ->assertDontSee('class="zv-task-go"', false); // no "Start shopping" while already shopping
        }
    }

    public function test_an_activity_without_an_item_limit_counts_against_its_minimum_only(): void
    {
        $student = $this->student();
        $this->surveyDone($student);
        $open = Activity::whereNull('max_items')->first();

        $this->actingAs($student)->get('/spending-activities/spending-tracker/tech-hub?activity_id=' . $open->id)->assertOk()
            ->assertSee('const TASK_ITEMS = { min: ' . $open->min_items . ', max: null };', false);
    }

    public function test_the_activity_list_opens_the_same_task_card_with_the_way_into_the_shop(): void
    {
        $student = $this->student();
        $activity = Activity::where('title', 'Birthday Party Budget')->first();
        \Illuminate\Support\Facades\DB::table('mba_position')->insert([
            'user_id' => 1, 'activity_key' => $activity->slot()[0], 'activity_id' => $activity->id, 'sid' => 1, 'cid' => $student->grade,
            'required' => 1, 'optional' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->get('/education/high-spending-activities')->assertOk();
        $response->assertSee("onclick=\"zvOpenTask('zvTask{$activity->id}')\"", false);
        $response->assertSeeInOrder(['id="zvTask' . $activity->id . '"', 'Birthday Party Budget', 'class="zv-task-go"'], false);
        $response->assertSee(url('/spending-activities/market-list') . '?activity_id=' . $activity->id, false);
    }

    public function test_needs_wants_and_savings_ends_with_finish(): void
    {
        $this->actingAs($this->student())->get(route('education.index'))->assertOk()
            ->assertSee('href="' . route('education.educational_finance_department') . '" class="themeBtn zv-finish-btn">Finish</a>', false);
    }
}
