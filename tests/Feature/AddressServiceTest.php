<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\User;
use App\Services\AddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * City Map, Phase 1 (2026-10-19): every student gets one street+number in
 * their own Grade's simulated city. Addresses are scoped per Grade (not
 * per school) so two Grades can validly reuse the same street+number -
 * each Grade is its own city, same model CityProposal already uses for
 * the per-Grade city name/quote.
 */
class AddressServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentInGrade(?int $maxAddresses = null): array
    {
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8', 'max_addresses' => $maxAddresses]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'grade_id' => $grade->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id]);

        return [$student, $grade, $class];
    }

    public function test_a_student_gets_assigned_a_real_street_and_number(): void
    {
        [$student] = $this->makeStudentInGrade();

        $address = AddressService::assignForStudent($student);

        $this->assertNotNull($address);
        $this->assertNotNull($address->street);
        $this->assertGreaterThan(0, $address->house_number);
    }

    public function test_assignment_is_idempotent(): void
    {
        [$student] = $this->makeStudentInGrade();

        $first = AddressService::assignForStudent($student);
        $second = AddressService::assignForStudent($student);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Address::where('student_id', $student->id)->count());
    }

    public function test_two_grades_can_validly_share_the_same_street_and_number(): void
    {
        [$studentA, $gradeA] = $this->makeStudentInGrade(1);
        [$studentB, $gradeB] = $this->makeStudentInGrade(1);

        $addressA = AddressService::assignForStudent($studentA);
        $addressB = AddressService::assignForStudent($studentB);

        // Both are the very first address generated in a 1-address pool -
        // same street, same house number - but different grades, so no
        // actual collision.
        $this->assertSame($addressA->street_id, $addressB->street_id);
        $this->assertSame($addressA->house_number, $addressB->house_number);
        $this->assertNotSame($addressA->grade_id, $addressB->grade_id);
    }

    public function test_pool_exhaustion_returns_null_instead_of_erroring(): void
    {
        [$studentA] = $this->makeStudentInGrade(1);
        AddressService::assignForStudent($studentA);

        $school = SchoolDomain::where('id', $studentA->sid)->first();
        $class = SchoolClass::find($studentA->grade);
        $studentB = User::factory()->create(['role' => 4, 'sid' => $studentA->sid, 'grade' => $class->id]);

        $address = AddressService::assignForStudent($studentB);

        $this->assertNull($address);
    }

    public function test_a_student_with_no_class_assigned_gets_no_address(): void
    {
        $student = User::factory()->create(['role' => 4, 'grade' => null]);

        $address = AddressService::assignForStudent($student);

        $this->assertNull($address);
    }
}
