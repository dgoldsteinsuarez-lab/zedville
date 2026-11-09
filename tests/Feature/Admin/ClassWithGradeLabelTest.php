<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\ClassLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-28, on the Emergency Fund Account screen: "put the grade
 * together with the class, like Grade 9, 9A or something like that on the
 * data and also when selecting the class" - the same on every assignment
 * screen with a Class picker.
 */
class ClassWithGradeLabelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private SchoolClass $nineA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade9 = Grade::create(['sid' => '1', 'name' => 'Grade 9', 'grade_level_id' => $level->id]);
        $grade10 = Grade::create(['sid' => '1', 'name' => 'Grade 10', 'grade_level_id' => $level->id]);
        $this->nineA = SchoolClass::create(['sid' => '1', 'grade_id' => $grade9->id, 'name' => '9A']);
        SchoolClass::create(['sid' => '1', 'grade_id' => $grade10->id, 'name' => '10B']);
    }

    public function test_the_label_puts_the_grade_before_the_class(): void
    {
        $this->assertSame('Grade 9 · 9A', ClassLabel::make('Grade 9', '9A'));
        $this->assertSame('9A', ClassLabel::make(null, '9A'));     // a class without a grade
        $this->assertSame('', ClassLabel::make('Grade 9', null));  // no class at all
    }

    public function test_the_class_picker_and_the_list_show_the_grade_with_the_class(): void
    {
        DB::table('mba_position')->insert([
            'user_id' => $this->admin->id, 'activity_key' => 'emeaccount', 'sid' => 1, 'cid' => $this->nineA->id,
            'required' => 1, 'optional' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->admin)->get('/admin/education/emergency-fund-account')->assertOk()
            // picker: grade then class, Grade 9 before Grade 10
            ->assertSeeInOrder(['Select Class', 'Grade 9 · 9A', 'Grade 10 · 10B', 'Select Position'])
            // the list of assignments
            ->assertSee('<td>Grade 9 · 9A</td>', false);
    }

    public function test_every_assignment_screen_picks_classes_by_grade_and_class(): void
    {
        foreach (['/admin/education/monthly-budget-activity', '/admin/education/spending-activity', '/admin/education/high-budget-activity',
                  '/admin/engagement/substitute-goods', '/admin/finhero/activities', '/admin/finhero/task-activities'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk()->assertSee('Grade 9 · 9A');
        }
    }
}
