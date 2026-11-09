<?php

namespace Tests\Feature;

use App\Models\AutopilotPeriod;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: only Tutors, School Admins and Admins turn Autopilot on
 * (never students), it can be scheduled for a whole Grade (long holidays),
 * and the language is chosen per Grade - a Tutor can change their own Class.
 */
class AutopilotAndGradeLanguageTest extends TestCase
{
    use RefreshDatabase;

    private function grade(int $sid = 1): Grade
    {
        return Grade::create(['sid' => (string) $sid, 'name' => 'Grade ' . uniqid()]);
    }

    private function classIn(Grade $grade): SchoolClass
    {
        return SchoolClass::create(['sid' => $grade->sid, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
    }

    private function student(SchoolClass $class): User
    {
        return User::factory()->create(['role' => 4, 'sid' => $class->sid, 'grade' => $class->id]);
    }

    private function tutorOf(SchoolClass $class): User
    {
        $tutor = User::factory()->create(['role' => 3, 'sid' => $class->sid]);
        DB::table('class_tutor')->insert(['class_id' => $class->id, 'user_id' => $tutor->id]);

        return $tutor;
    }

    public function test_a_student_can_no_longer_turn_autopilot_on(): void
    {
        $student = $this->student($this->classIn($this->grade()));

        $this->actingAs($student)
            ->post('/profile/autopilot', ['end_date' => now()->addWeek()->toDateString()])
            ->assertNotFound();
        $this->actingAs($student)->get(route('admin.autopilot.index'))->assertForbidden();
        $this->assertSame(0, AutopilotPeriod::count());

        // The profile tab still tells them, read-only.
        $this->actingAs($student)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Only your tutor or your school can turn it on.')
            ->assertDontSee('Turn on Autopilot');
    }

    public function test_a_school_admin_schedules_autopilot_for_a_whole_grade_over_the_holidays(): void
    {
        $grade = $this->grade();
        $students = collect([$this->classIn($grade), $this->classIn($grade)])
            ->flatMap(fn ($class) => [$this->student($class), $this->student($class)]);
        $otherGradeStudent = $this->student($this->classIn($this->grade()));
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);
        \Carbon\Carbon::setTestNow('2026-06-20 10:00'); // scheduled before the holidays

        $this->actingAs($schoolAdmin)->post(route('admin.autopilot.store'), [
            'target' => 'grade',
            'grade_id' => $grade->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-10',   // ten weeks: long holidays are fine
            'reason' => 'Summer holidays',
        ])->assertRedirect(route('admin.autopilot.index'));

        $periods = AutopilotPeriod::all();
        $this->assertEqualsCanonicalizing($students->pluck('id')->all(), $periods->pluck('student_id')->all());
        $this->assertCount(1, $periods->pluck('batch_id')->unique());
        $this->assertNotContains($otherGradeStudent->id, $periods->pluck('student_id'));

        // Month-end sees every student in the Grade as on Autopilot.
        $this->assertCount(4, AutopilotPeriod::overlappingMonth(2026, 8)->get());

        // Listed as one entry, and cancelled as one.
        $this->actingAs($schoolAdmin)->get(route('admin.autopilot.index'))
            ->assertOk()->assertSee($grade->name)->assertSee('4 students');

        $this->actingAs($schoolAdmin)
            ->post(route('admin.autopilot.cancel-batch', $periods->first()->batch_id))
            ->assertRedirect(route('admin.autopilot.index'));
        $this->assertSame(0, AutopilotPeriod::where('status', 'active')->count());
    }

    public function test_a_tutor_puts_only_their_own_students_in_the_grade_on_autopilot(): void
    {
        $grade = $this->grade();
        $myClass = $this->classIn($grade);
        $mine = $this->student($myClass);
        $notMine = $this->student($this->classIn($grade));
        $tutor = $this->tutorOf($myClass);

        $this->actingAs($tutor)->post(route('admin.autopilot.store'), [
            'target' => 'grade', 'grade_id' => $grade->id,
            'start_date' => '2026-12-20', 'end_date' => '2027-01-08', 'reason' => 'Winter holidays',
        ])->assertRedirect(route('admin.autopilot.index'));

        $this->assertSame([$mine->id], AutopilotPeriod::pluck('student_id')->all());
        $this->assertNotContains($notMine->id, AutopilotPeriod::pluck('student_id'));

        // A Grade the Tutor has no Class in isn't reachable.
        $otherGrade = $this->grade();
        $this->student($this->classIn($otherGrade));
        $this->actingAs($tutor)->post(route('admin.autopilot.store'), [
            'target' => 'grade', 'grade_id' => $otherGrade->id,
            'start_date' => '2026-12-20', 'end_date' => '2027-01-08', 'reason' => 'Winter holidays',
        ])->assertNotFound();
    }

    public function test_the_grade_language_applies_to_every_class_and_a_tutor_can_change_their_own(): void
    {
        $grade = $this->grade();
        $myClass = $this->classIn($grade);
        $otherClass = $this->classIn($grade);
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);

        $this->actingAs($schoolAdmin)->get(route('admin.grades.general-information', $grade->id))
            ->assertOk()->assertSee('Language for this Grade');

        $this->actingAs($schoolAdmin)->put(route('admin.grades.language', $grade->id), [
            'default_language' => 'es', 'language_mode' => 'locked', 'language_level' => 'beginner',
        ])->assertRedirect();

        foreach ([$grade->fresh(), $myClass->fresh(), $otherClass->fresh()] as $row) {
            $this->assertSame(['locked', 'es', 'beginner'], [$row->language_mode, $row->default_language, $row->language_level]);
        }

        // The Tutor changes their own Class only.
        $tutor = $this->tutorOf($myClass);
        $this->actingAs($tutor)->get(route('admin.tutor.my-students'))->assertOk()->assertSee('Español');
        $this->actingAs($tutor)->put(route('admin.tutor.class-language', $myClass->id), [
            'default_language' => 'fr', 'language_level' => 'standard',
        ])->assertRedirect();

        $this->assertSame(['free', 'fr', 'standard'], [$myClass->fresh()->language_mode, $myClass->fresh()->default_language, $myClass->fresh()->language_level]);
        $this->assertSame('es', $otherClass->fresh()->default_language);

        $this->actingAs($tutor)->put(route('admin.tutor.class-language', $otherClass->id), [
            'default_language' => 'de', 'language_level' => 'standard',
        ])->assertNotFound();
        $this->assertSame('es', $otherClass->fresh()->default_language);

        // A student in the Tutor's Class now sees French.
        $student = $this->student($myClass);
        $this->actingAs($student)->get(route('profile.edit'))->assertOk()->assertSee('Pilote automatique');
    }

    public function test_a_new_class_starts_with_its_grade_language_and_the_classes_screen_keeps_it(): void
    {
        $grade = $this->grade();
        $grade->update(['default_language' => 'de', 'language_mode' => 'locked', 'language_level' => 'standard']);
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);

        // The Classes screen's Add form sends no language fields.
        $this->actingAs($schoolAdmin)->withSession(['selected_school' => '1'])
            ->post(url('admin/grade/add'), ['name' => 'New ' . uniqid(), 'grade_id' => $grade->id])
            ->assertRedirect();
        $class = SchoolClass::where('grade_id', $grade->id)->latest('id')->first();
        $this->assertSame(['locked', 'de'], [$class->language_mode, $class->default_language]);

        // ...and its Edit form neither - it must not reset the language.
        $this->actingAs($schoolAdmin)->withSession(['selected_school' => '1'])
            ->put(url('admin/grade/edit/' . $class->id), ['name' => 'Renamed ' . uniqid(), 'grade_id' => $grade->id])
            ->assertRedirect();
        $this->assertSame(['locked', 'de'], [$class->fresh()->language_mode, $class->fresh()->default_language]);
    }
}
