<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-26: "make all the activities available or repeatable for
 * every school and classes made, not just only one school". Every activity
 * screen can assign to one class, to every class of the school being
 * managed, or to every class of every school - each class getting the
 * assignment in its own school, so its students see it.
 */
class ActivityAssignmentScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<int, SchoolClass> */
    private array $classes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        foreach ([1, 1, 2] as $i => $sid) {
            $grade = Grade::create(['sid' => (string) $sid, 'name' => "Grade $i", 'grade_level_id' => $level->id]);
            $this->classes[] = SchoolClass::create(['sid' => (string) $sid, 'grade_id' => $grade->id, 'name' => "Class $i"]);
        }
    }

    private function positions(string $key)
    {
        return DB::table('mba_position')->where('activity_key', $key)->orderBy('cid')->get();
    }

    public function test_an_activity_can_be_given_to_every_class_of_every_school(): void
    {
        $this->actingAs($this->admin)->post('/admin/education/emergency-fund-position', [
            'cid' => 'all', 'position' => 'required', 'start_date' => '2026-11-02', 'end_date' => '2026-11-20',
        ])->assertSessionHas('success', 'Emergency Fund assigned to 3 classes.');

        $positions = $this->positions('emeaccount');
        $this->assertCount(3, $positions);
        foreach ($this->classes as $class) {
            $row = $positions->firstWhere('cid', $class->id);
            $this->assertSame((string) $class->sid, (string) $row->sid, 'each class in its own school');
            $this->assertTrue(DB::table('calendar_events')->where('classId', $class->id)->where('sid', $class->sid)->where('title', 'Emergency Fund')->exists());
        }

        // Again: the classes that have it are updated, not duplicated.
        $this->actingAs($this->admin)->post('/admin/education/emergency-fund-position', [
            'cid' => 'all', 'position' => 'optional',
        ]);
        $this->assertCount(3, $this->positions('emeaccount'));
        $this->assertSame([0, 0, 0], $this->positions('emeaccount')->pluck('required')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(3, DB::table('calendar_events')->where('title', 'Emergency Fund')->count());
    }

    public function test_all_classes_of_the_school_being_managed(): void
    {
        $this->actingAs($this->admin)->withSession(['selected_school' => 1])
            ->post('/admin/education/mba-position', ['cid' => 'school', 'position' => 'required'])
            ->assertSessionHas('success');

        $this->assertSame([$this->classes[0]->id, $this->classes[1]->id], $this->positions('mba')->pluck('cid')->map(fn ($v) => (int) $v)->all());
    }

    public function test_one_class_saved_while_managing_all_schools_keeps_its_school(): void
    {
        $class = $this->classes[2];
        $this->actingAs($this->admin)->post('/admin/education/emergency-fund-position', ['cid' => $class->id, 'position' => 'required']);

        $this->assertSame('2', (string) $this->positions('emeaccount')->first()->sid);
    }

    public function test_only_a_super_admin_can_assign_to_every_school(): void
    {
        $this->actingAs(User::factory()->create(['role' => 2, 'sid' => '1']));
        $this->assertTrue(\App\Support\AssignmentScope::classes('all', null)->isEmpty());
        $this->assertCount(2, \App\Support\AssignmentScope::classes('school', 1));
    }

    public function test_choosing_many_classes_while_editing_one_is_refused(): void
    {
        $class = $this->classes[0];
        $this->actingAs($this->admin)->post('/admin/education/emergency-fund-position', ['cid' => $class->id, 'position' => 'required']);
        $id = $this->positions('emeaccount')->first()->id;

        $this->actingAs($this->admin)->post('/admin/education/emergency-fund-position', ['id' => $id, 'cid' => 'all', 'position' => 'required'])
            ->assertSessionHas('error');
        $this->assertCount(1, $this->positions('emeaccount'));
    }

    public function test_substitute_goods_for_every_class(): void
    {
        $this->actingAs($this->admin)->post(route('admin.engagement.substitute-goods.store'), [
            'cid' => 'all', 'position' => 'required', 'start_date' => '2026-11-02', 'end_date' => '2026-11-09',
        ])->assertSessionHas('success', 'Substitute Goods assigned to 3 classes.');

        $this->assertCount(3, $this->positions('substitute_goods'));
    }

    public function test_a_finhero_activity_for_every_grade_of_every_school(): void
    {
        $post = fn () => $this->actingAs($this->admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_5', 'activity_name' => 'Introduction to Banking',
            'position' => 'required', 'type' => 'library', 'grade_id' => 'all',
        ]);

        $post()->assertSessionHas('success', 'Activity added to 3 grades.');
        $rows = DB::table('finhero_activity_registry')->where('activity_key', 'task_5')->get();
        $this->assertCount(3, $rows);
        $this->assertEqualsCanonicalizing(['1', '1', '2'], $rows->pluck('sid')->map(fn ($v) => (string) $v)->all());
        foreach ($this->classes as $class) {
            $this->assertTrue(DB::table('calendar_events')->where('classId', $class->id)->where('title', 'library-Introduction to Banking')->exists());
        }

        // A second time adds nothing: every grade has it already.
        $post()->assertSessionHas('success', 'Activity added to 0 grades (3 already had it).');
        $this->assertSame(3, DB::table('finhero_activity_registry')->where('activity_key', 'task_5')->count());
    }
}
