<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-11-07: "put the name of the account where it is visible, in the
 * Good morning and maybe somewhere else" - an account without a nickname (one an
 * admin created and the student never claimed) showed an empty greeting.
 */
class DisplayNameTest extends TestCase
{
    use RefreshDatabase;

    private function student(?string $nickname): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'name' => 'Leigh Poti', 'nickname' => $nickname]);
    }

    public function test_the_nickname_or_else_the_accounts_name(): void
    {
        $this->assertSame('Leigh Poti', $this->student(null)->display_name);
        $this->assertSame('Leigh', $this->student('Leigh')->display_name);
    }

    public function test_the_greeting_the_header_and_the_dashboard_show_it(): void
    {
        $html = $this->actingAs($this->student(null))->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('singlelineTxt">Leigh Poti</h4>', $html);       // under "Good morning,"
        $this->assertStringContainsString('font-semibold">Leigh Poti</span>', $html);     // the header, beside the avatar
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'Leigh Poti'));            // and its card, and the welcome
    }
}
