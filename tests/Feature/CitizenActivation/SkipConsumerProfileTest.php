<?php

namespace Tests\Feature\CitizenActivation;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\User;
use App\Services\CitizenActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * bugs.md: the onboarding wizard's consumer-profile step
 * (citizen-activation.layouts.surveys) is a purely client-side quiz that
 * computes and shows results in the browser but never POSTs anything to the
 * server, so ConsumerSurvey::exists() never becomes true and a student can
 * never pass Step 5 - the whole activation wizard, and the student account
 * behind it, is permanently stuck. This is a temporary unblock (a visible
 * "Skip for now" button creating a zero-cost placeholder record), not the
 * real fix - the real fix is wiring the quiz's own computed answers to an
 * actual submit, the same way the already-working monthly-redo flow
 * (ConsumerSurveyController::storesurvey) does.
 */
class SkipConsumerProfileTest extends TestCase
{
    use RefreshDatabase;

    private function studentReadyForConsumerProfile(): User
    {
        $student = User::factory()->create(['role' => 4]);

        BankAccount::create([
            'student_id' => $student->id,
            'sid' => $student->sid,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'primary_savings_account_amount' => 500,
        ]);

        AutoDebitRequest::create([
            'user_id' => $student->id,
            'sid' => $student->sid,
            'type' => 'school',
        ]);

        return $student;
    }

    public function test_activation_is_stuck_on_consumer_profile_before_skipping(): void
    {
        $student = $this->studentReadyForConsumerProfile();

        $nextRoute = app(CitizenActivationService::class)->getNextRoute($student);

        $this->assertSame(route('CitizenActivation.consumerProfile'), $nextRoute);
    }

    public function test_skipping_creates_a_zero_cost_record_and_unblocks_activation(): void
    {
        $student = $this->studentReadyForConsumerProfile();

        $response = $this->actingAs($student)->post(route('CitizenActivation.consumerProfile.skip'));

        $response->assertRedirect(route('CitizenActivation.activationComplete'));
        $this->assertDatabaseHas('consumer_surveys', [
            'user_id' => $student->id,
            'total_cost' => 0,
        ]);

        $nextRoute = app(CitizenActivationService::class)->getNextRoute($student->fresh());
        $this->assertNull($nextRoute);
    }

    public function test_skipping_does_not_touch_the_students_balance(): void
    {
        $student = $this->studentReadyForConsumerProfile();

        $this->actingAs($student)->post(route('CitizenActivation.consumerProfile.skip'));

        $this->assertDatabaseMissing('transactions1', ['user_id' => $student->id]);
    }

    public function test_skipping_twice_does_not_create_duplicate_records(): void
    {
        $student = $this->studentReadyForConsumerProfile();

        $this->actingAs($student)->post(route('CitizenActivation.consumerProfile.skip'));
        $this->actingAs($student)->post(route('CitizenActivation.consumerProfile.skip'));

        $this->assertSame(1, ConsumerSurvey::where('user_id', $student->id)->count());
    }
}
