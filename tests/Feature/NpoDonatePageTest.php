<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Npo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The donate page used to know only the first 7 NPOs by a hard-coded list:
 * any NPO an admin added later crashed it (undefined $accNO), every
 * donation thanked the student for helping "rescued animals", and a row of
 * demo buttons could switch the page to made-up organisations.
 */
class NpoDonatePageTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 1]);
        BankAccount::create([
            'student_id' => $user->id,
            'sid' => 1,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'card_pin' => '1234',
        ]);

        return $user;
    }

    private function npo(array $attributes = []): Npo
    {
        return Npo::create($attributes + [
            'category' => 'Food',
            'name' => 'Harbour Food Pantry',
            'slug' => 'harbour-food-pantry',
            'content' => 'Meals for families.',
            'account_number' => ' 9999-0000-1111',
            'status' => 1,
            'sort_order' => 1,
        ]);
    }

    public function test_an_npo_added_by_an_admin_opens_its_donate_page(): void
    {
        $this->npo();

        $this->actingAs($this->student())
            ->get(route('education.npos.donate', 'harbour-food-pantry'))
            ->assertOk()
            ->assertSee('Harbour Food Pantry')
            ->assertSee('9999-0000-1111');
    }

    public function test_an_unknown_or_hidden_npo_is_not_found(): void
    {
        $this->npo(['slug' => 'hidden-npo', 'status' => 0]);
        $student = $this->student();

        $this->actingAs($student)->get(route('education.npos.donate', 'no-such-npo'))->assertNotFound();
        $this->actingAs($student)->get(route('education.npos.donate', 'hidden-npo'))->assertNotFound();
    }

    public function test_the_thank_you_message_follows_the_npos_category(): void
    {
        $this->npo();

        $this->actingAs($this->student())
            ->get(route('education.npos.donate', 'harbour-food-pantry'))
            ->assertSee('No one should go hungry')
            ->assertDontSee('rescued animals')
            ->assertDontSee('Select an organization');
    }
}
