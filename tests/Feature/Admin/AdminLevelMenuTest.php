<?php

namespace Tests\Feature\Admin;

use App\Models\Activity;
use App\Models\EasyActivity;
use App\Models\FinheroLibraryModule;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\ClassLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Admin menu by level (Daniel, 2026-11-02: "hay que dividir ya las
 * actividades que son para cada nivel rookie, intermedio, etc. tendrás que
 * reorganizar el Admin" - he chose a menu by level): one group per level, each
 * activity screen showing that level's activities and assigning to that
 * level's classes.
 */
class AdminLevelMenuTest extends TestCase
{
    use RefreshDatabase;

    private function level(string $name): GradeLevel
    {
        return GradeLevel::where('name', $name)->firstOrFail();
    }

    private function classOf(string $level, string $name): SchoolClass
    {
        $grade = Grade::create(['sid' => '1', 'name' => $level . ' grade', 'grade_level_id' => $this->level($level)->id]);

        return SchoolClass::create(['sid' => '1', 'name' => $name, 'grade_id' => $grade->id]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    public function test_the_menu_has_a_group_per_level_with_its_activity_screens(): void
    {
        $rookie = $this->level('Rookie');

        $page = $this->actingAs($this->admin())->get(route('admin.money-factory', ['level' => $rookie->id]))->assertOk()->assertSee('Level: Rookie');
        foreach (['Junior', 'Rookie', 'Intermediate', 'Advance', 'Advance Economist'] as $name) {
            $page->assertSee('<span class="navTxt">' . $name . '</span>', false);
        }
        $page->assertSee('admin/education/spending-activity?level=' . $rookie->id, false)
            ->assertSee('admin/money-factory?level=' . $rookie->id, false)
            ->assertSee('admin/finhero/task-activities?level=' . $rookie->id, false)
            ->assertSee('<span class="navTxt">Every level</span>', false);
    }

    public function test_a_level_screen_offers_and_assigns_only_that_levels_classes(): void
    {
        $admin = $this->admin();
        $rookieClass = $this->classOf('Rookie', 'Class 8R');
        $advanceClass = $this->classOf('Advance', 'Class 11A');
        $rookie = $this->level('Rookie');
        $journey = EasyActivity::where('type', 'mf_journey')->firstOrFail();

        $this->actingAs($admin)->withSession(['selected_school' => 1])->get(route('admin.money-factory', ['level' => $rookie->id]))->assertOk()
            ->assertSee('Class 8R')->assertDontSee('Class 11A')->assertSee('All Rookie classes in this school');

        // "every class of this school" on the Rookie screen: the Rookie classes only
        $this->actingAs($admin)->withSession(['selected_school' => 1, 'admin_level' => $rookie->id])
            ->post(route('admin.engagement.easy-activities.assign', $journey->id), ['cid' => 'school', 'position' => 'required', 'start_date' => '2026-11-01', 'end_date' => '2026-11-30'])
            ->assertRedirect();
        $this->assertDatabaseHas('mba_position', ['activity_key' => $journey->key(), 'cid' => $rookieClass->id]);
        $this->assertDatabaseMissing('mba_position', ['activity_key' => $journey->key(), 'cid' => $advanceClass->id]);

        // every level: every class
        $this->actingAs($admin)->withSession(['selected_school' => 1])->get(route('admin.money-factory', ['level' => 0]))->assertOk()
            ->assertSee('Class 8R')->assertSee('Class 11A')->assertSee('All levels');
    }

    public function test_catalogs_show_the_levels_activities_and_keep_their_levels(): void
    {
        $admin = $this->admin();
        $rookie = $this->level('Rookie');
        $advance = $this->level('Advance');

        // a list for every level is on every level; one for Rookie only on Rookie's
        $this->actingAs($admin)->get(route('admin.spending.lists', ['level' => $advance->id]))->assertOk()->assertSee('Birthday Party Budget');
        Activity::where('title', 'Birthday Party Budget')->update(['levels' => json_encode([$rookie->id])]);
        $this->actingAs($admin)->get(route('admin.spending.lists', ['level' => $advance->id]))->assertOk()->assertDontSee('Birthday Party Budget');
        $this->actingAs($admin)->get(route('admin.spending.lists', ['level' => $rookie->id]))->assertOk()->assertSee('Birthday Party Budget');

        // an activity's levels are saved from its form; none checked: every level
        $trip = EasyActivity::where('title', 'Pocket money for New York')->firstOrFail();
        $form = ['title' => $trip->title, 'currency' => 'USD', 'min_amount' => 100, 'reason' => $trip->content['reason']];
        $this->actingAs($admin)->put(route('admin.engagement.easy-activities.update', $trip->id), $form + ['levels' => [$advance->id, $rookie->id]])->assertRedirect();
        $this->assertEqualsCanonicalizing([$rookie->id, $advance->id], $trip->fresh()->levels);
        $this->actingAs($admin)->put(route('admin.engagement.easy-activities.update', $trip->id), $form)->assertRedirect();
        $this->assertNull($trip->fresh()->levels);
    }

    public function test_outside_the_level_screens_nothing_is_filtered(): void
    {
        $this->classOf('Rookie', 'Class 8R');
        $this->classOf('Advance', 'Class 11A');

        $this->withSession(['admin_level' => $this->level('Rookie')->id]);
        $this->assertEqualsCanonicalizing(['Class 8R', 'Class 11A'], ClassLabel::classes(1)->pluck('name')->all());
    }

    public function test_a_library_module_gets_its_levels(): void
    {
        $module = FinheroLibraryModule::create(['module_key' => 'task_9', 'title' => 'Inflation', 'icon' => 'chart', 'sort_order' => 9, 'lesson' => []]);
        $levels = [$this->level('Junior')->id, $this->level('Rookie')->id];

        $this->actingAs($this->admin())->post('/admin/finhero/library-modules/' . $module->id . '/levels', ['levels' => $levels])->assertRedirect();

        $this->assertEqualsCanonicalizing($levels, $module->fresh()->levels);
    }

    public function test_an_emergency_fund_assignment_can_be_removed(): void
    {
        // it said "Record not found": the removal only knew the Needs, Wants & Savings Match
        $class = $this->classOf('Rookie', 'Class 8R');
        $id = DB::table('mba_position')->insertGetId(['user_id' => 1, 'activity_key' => 'emeaccount', 'sid' => '1', 'cid' => $class->id,
            'required' => 1, 'optional' => 0, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('calendar_events')->insert(['sid' => '1', 'classId' => $class->id, 'title' => 'Emergency Fund', 'position' => 'required',
            'start' => now(), 'end' => now()->addMonth(), 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->admin())->withSession(['selected_school' => 1])->get('/admin/education/mba-position/delete/' . $id)
            ->assertSessionHas('success', 'Emergency Fund assignment removed.');

        $this->assertDatabaseMissing('mba_position', ['id' => $id]);
        $this->assertDatabaseMissing('calendar_events', ['classId' => $class->id, 'title' => 'Emergency Fund']);
    }
}
