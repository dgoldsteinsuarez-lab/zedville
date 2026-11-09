<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use App\Services\AddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-25: "when I sign up and open the account, the address
 * doesn't appear". The student's Zedville address now fills the bank form
 * (read-only, like the Citizen ID), is saved on the account, and reaches the
 * bills' auto-debit - which used to read a field the user never has.
 */
class BankAccountAddressTest extends TestCase
{
    use RefreshDatabase;

    private function studentInAClass(): User
    {
        $school = SchoolDomain::create(['school_name' => 'Test School', 'school_code' => 'TS-' . uniqid(), 'school_domain' => uniqid() . '.test']);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 10']);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '10A', 'grade_id' => $grade->id]);

        return User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id, 'welcome_seen_at' => now()]);
    }

    public function test_the_bank_form_shows_the_students_zedville_address(): void
    {
        $student = $this->studentInAClass();

        $response = $this->actingAs($student)->get(route('CitizenActivation.bankAccount'))->assertOk();

        $label = AddressService::assignForStudent($student)->load('street')->label();
        $this->assertStringEndsWith(', Zedville', $label);
        $response->assertSee($label)->assertSee('Home Address (Auto-filled)');
    }

    public function test_opening_the_account_saves_the_city_address_whatever_the_form_sends(): void
    {
        $student = $this->studentInAClass();

        $this->actingAs($student)->post(route('CitizenActivation.store'), [
            'email' => 'student@example.test', 'homeAddress' => 'Somewhere else', 'rcvaccemail' => '1', 'tandc' => '1',
        ]);

        $label = AddressService::assignForStudent($student)->load('street')->label();
        $this->assertSame($label, BankAccount::where('student_id', $student->id)->value('student_address'));
        $this->assertSame($label, $student->fresh()->address);
    }

    public function test_the_bills_auto_debit_carries_the_address(): void
    {
        $student = $this->studentInAClass();
        BankAccount::create([
            'student_id' => $student->id, 'sid' => $student->sid, 'student_name' => $student->name, 'student_email' => $student->email,
            'student_address' => '4 Keynes Boulevard, Zedville', 'primary_savings_account_number' => '123000-000001',
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);

        $this->actingAs($student)->post('/bank-account/store-auto-debit-authorization-form', [
            'services' => ['rent' => 'R-1'],
            'services_meta' => ['rent' => ['name' => 'Rent', 'account' => 'R-1', 'amount' => 300]],
            'bankname' => 'Universal Bank',
            'bankaccountnumber' => '123000-000001',
        ]);

        $this->assertSame('4 Keynes Boulevard, Zedville', AutoDebitRequest::where('user_id', $student->id)->value('serviceaddress'));
    }
}
