<?php
// ============================================================
// FILE: app/Http/Controllers/Admin/FinheroAdminController.php
// ============================================================
// INSTRUCTIONS: Place in app/Http/Controllers/Admin/
// All routes require admin/tutor middleware (see routes file).

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinheroActivityDefault;
use App\Models\FinheroBadgeRecord;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class FinheroAdminController extends Controller
{
    public function __construct(private FinheroBadgeCalculatorService $calculator)
    {
    }

    // ─────────────────────────────────────────────────────────────
    // ACTIVITY MANAGER
    // ─────────────────────────────────────────────────────────────

    /** GET /admin/finhero/activities — list all registered activities */
    /*public function listActivities(): JsonResponse
    {
        $activities = DB::table('finhero_activity_registry')
            ->orderBy('created_at')
            ->get();
        return response()->json($activities);
    }*/
    /*public function listActivities()
    {
        $activities = DB::table('finhero_activity_registry')
            ->where('type', 'library')
            ->orderBy('created_at')
            ->get();

            $type = 'library';

        return view('admin.finhero.activities', compact('activities', 'type'));
    }*/
    public function listActivities()
    {
        //$query = DB::table('finhero_activity_registry')
        //->where('type', 'task');

        $query = DB::table('finhero_activity_registry as far')
            ->leftJoin('classes as c', 'far.cid', '=', 'c.id')
            ->select('far.*', 'c.name as class_name')
            ->where('far.type', 'task');

        if (session()->has('selected_school')) {
            $query->where('far.sid', session('selected_school'));
        }

        $activities = $query
            ->orderBy('created_at')
            ->get();
        // Get classes
        $classQuery = DB::table('classes');

        // If school selected, show only classes of that school
        if (session()->has('selected_school')) {
            $classQuery->where('sid', session('selected_school'));
        }

        $classes = $classQuery
            ->orderBy('id')
            ->get();

        $type = 'task';

        return view('admin.finhero.activities', compact('activities', 'type', 'classes'));
    }
    public function taskActivities()
    {
        /*$activities = DB::table('finhero_activity_registry')
            ->where('type', 'task')
            ->orderBy('created_at')
            ->get();

        $type = 'task';

        return view('admin.finhero.activities', compact('activities', 'type'));*/
        // $query = DB::table('finhero_activity_registry')
        //->where('type', 'library');
        $query = DB::table('finhero_activity_registry as far')
            ->leftJoin('classes as c', 'far.cid', '=', 'c.id')
            ->select('far.*', 'c.name as class_name')
            ->where('far.type', 'library');

        if (session()->has('selected_school')) {
            $query->where('far.sid', session('selected_school'));
        }

        $activities = $query
            ->orderBy('created_at')
            ->get();

        // Get classes
        $classQuery = DB::table('classes');

        // If school selected, show only classes of that school
        if (session()->has('selected_school')) {
            $classQuery->where('sid', session('selected_school'));
        }

        $classes = $classQuery
            ->orderBy('id')
            ->get();

        $type = 'library';

        return view('admin.finhero.activities', compact('activities', 'type', 'classes'));
    }
    /**
     * POST /admin/finhero/activities — register a new activity
     * Body: { activity_key, activity_name, max_points, position, description }
     *
     * activity_key is set by IT (e.g. 'task_1') and must be unique.
     * Admin fills in name, points, position via the UI form.
     */
    //public function addActivity(Request $request): JsonResponse
    /*public function addActivity(Request $request)
    {
        $validated = $request->validate([
            'activity_key' => [
                'required',
                'string',
                Rule::unique('finhero_activity_registry')
                    ->where(function ($query) use ($request) {

                        return $query
                            ->where('type', $request->type)
                            ->where('sid', session('selected_school'));
                    }),
            ],
            'cid' => 'nullable|exists:classes,id',
            'activity_name' => 'required|string|max:255',
            'max_points' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
                'required_if:type,task',
            ],
            'position' => 'required|in:required,optional,free',
            'type' => 'required|string',
            // Salary required only for task
            'salary' => 'required_if:type,task|nullable|numeric|min:0',
            'is_active' => 'required|in:0,1',
            'description' => 'nullable|string',
        ]);

        $id = DB::table('finhero_activity_registry')->insertGetId([
            'activity_key' => $validated['activity_key'],
            'activity_name' => $validated['activity_name'],
            'max_points' => $validated['max_points'],
            'position' => $validated['position'],
            'type' => $validated['type'],
            // ADD THIS LINE
            'sid' => session('selected_school') ?: null,
            'cid' => $request->classId ?: null,

            // Store salary only for task
            'salary' => $request->type == 'task'
                ? $request->salary
                : null,
            'is_active' => $validated['is_active'],
            'description' => $validated['description'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Calendar Event Insert / Update
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', session('selected_school') ?: null, )
            ->where('title', $request->type . '-' . $validated['activity_name'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $validated['position'],
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => session('selected_school') ?: null,
                'classId' => $request->classId ?: null,
                'title' => $request->type . '-' . $validated['activity_name'],
                'description' => $validated['description'] ?? '',
                'position' => $validated['position'],
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => now(),
                'end' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $route = $request->type == 'task'
            ? '/admin/finhero/activities'
            : '/admin/finhero/task-activities';

        return redirect($route)
            ->with('success', 'Activity added successfully.');
    }*/

    public function addActivity(Request $request)
    {
        $selectedSchool = session('selected_school') ?: null;
        $classId = $request->classId ?: null;

        $validated = $request->validate([

            // Activity key must be unique per school + class + type
            'activity_key' => [
                'required',
                'string',
                Rule::unique('finhero_activity_registry')
                    ->where(function ($query) use ($request, $selectedSchool, $classId) {
                        return $query
                            ->where('type', $request->type)
                            ->where('sid', $selectedSchool)
                            ->where('cid', $classId);
                    }),
            ],

            'cid' => 'nullable|exists:classes,id',

            // Daniel, 2026-10-21: "Name, points, status are not required.
            // For name, just add the name of the activity." - left blank,
            // Name falls back to the activity_key below, same philosophy
            // as max_points/salary falling back to their global defaults.
            'activity_name' => 'nullable|string|max:255',

            'position' => 'required|in:required,optional,free',

            'type' => 'required|string',

            'salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            // Daniel, 2026-10-21: "status are not required" - defaults to
            // active (1) below, matching the modal's own "Active" default.
            'is_active' => 'nullable|in:0,1',

            'description' => 'nullable|string',

            // Daniel, 2026-10-21: "i need to be able to add start and due
            // date in all activities, including the tasks, library and so
            // on" - same pattern as the other 4 "position" screens.
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Check duplicate activity name by SCHOOL + CLASS + TYPE
        |--------------------------------------------------------------------------
        */

        $activityExists = DB::table('finhero_activity_registry')
            ->where('sid', $selectedSchool)
            ->where('cid', $classId)
            ->where('type', $validated['type'])
            ->where('activity_name', $validated['activity_name'])
            ->exists();

        if ($activityExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'activity_name' =>
                        'This activity already exists for the selected class.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Insert Activity
        |--------------------------------------------------------------------------
        */

        $id = DB::table('finhero_activity_registry')->insertGetId([
            'activity_key' => $validated['activity_key'],

            // Not required - falls back to the activity_key itself so the
            // list never shows a blank name.
            'activity_name' => $validated['activity_name'] ?: $validated['activity_key'],

            // Daniel, 2026-10-21: "i can still see all the unnecesarry
            // settings here" (Max Points, redundant now that points come
            // from badges separately per his earlier statement) - no
            // longer collected per-activity at all. Always null here;
            // FinheroStudentController already falls back to the global
            // default (FinheroActivityDefault) for every activity whose
            // own max_points is null, so the underlying mechanic is
            // unchanged, just no longer manually overridden per activity.
            'max_points' => null,

            'position' => $validated['position'],

            'type' => $validated['type'],

            'sid' => $selectedSchool,

            'cid' => $classId,

            // Salary only for task - not required, falls back to the
            // global default for this activity_key (FinheroActivityDefault,
            // same philosophy as max_points), read at the point this is
            // actually shown to a student (FinheroStudentController::
            // taskPage()), not copied in here.
            'salary' => $validated['type'] === 'task'
                ? ($validated['salary'] ?? null)
                : null,

            // Not required - defaults to active.
            'is_active' => $validated['is_active'] ?? 1,

            'description' => $validated['description'] ?? null,

            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,

            'created_at' => now(),
            'updated_at' => now(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Calendar Event Insert / Update
        |--------------------------------------------------------------------------
        */

        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $selectedSchool)
            ->where('classId', $classId)
            ->where('title', $validated['type'] . '-' . $validated['activity_name'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        // See MbaPositionController - FullCalendar's end is exclusive, +1
        // day so an admin-picked end_date is still shown as included.
        $calendarStart = !empty($validated['start_date']) ? \Carbon\Carbon::parse($validated['start_date'])->startOfDay() : now();
        $calendarEnd = !empty($validated['end_date']) ? \Carbon\Carbon::parse($validated['end_date'])->addDay()->startOfDay() : now();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $validated['position'],
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'start' => $calendarStart,
                    'end' => $calendarEnd,
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => $selectedSchool,
                'classId' => $classId,
                'title' => $validated['type'] . '-' . $validated['activity_name'],
                'description' => $validated['description'] ?? '',
                'position' => $validated['position'],
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => $calendarStart,
                'end' => $calendarEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        $route = $validated['type'] === 'task'
            ? '/admin/finhero/activities'
            : '/admin/finhero/task-activities';

        return redirect($route)
            ->with('success', 'Activity added successfully.');
    }

    /**
     * PUT /admin/finhero/activities/{id} — update an activity
     * Body: { activity_name, max_points, position, is_active, description }
     */
    //public function updateActivity(Request $request, int $id): JsonResponse
    /*public function updateActivity(Request $request, int $id)
    {
         $activity = DB::table('finhero_activity_registry')
        ->where('id', $id)
        ->first();

        $validated = $request->validate([
            'activity_name' => 'sometimes|string|max:255',
            'max_points'    => 'sometimes|integer|min:1|max:100',
            'position'      => 'sometimes|in:required,optional,free',
             // Salary required only for task type
            'salary'        => $activity->type == 'task'
                ? 'required|numeric|min:0'
                : 'nullable',
            'is_active'     => 'sometimes|boolean',
            'description'   => 'nullable|string',
        ]);

        DB::table('finhero_activity_registry')
            ->where('id', $id)
            ->update(array_merge($validated, ['updated_at' => now()]));
         // Remove salary update for library type
        if ($activity->type != 'task') {
            unset($updateData['salary']);
        }


        //return response()->json(['success' => true, 'message' => 'Activity updated.']);
        //return redirect('/admin/finhero/activities')->with('success', 'Activity updated.');
        $route = $activity->type == 'library'
        ? '/admin/finhero/activities'
        : '/admin/finhero/task-activities';

        return redirect($route)->with('success', 'Activity updated.');
    }*/
    public function updateActivity(Request $request, int $id)
    {
        $activity = DB::table('finhero_activity_registry')
            ->where('id', $id)
            ->first();

        if (!$activity) {
            return redirect()->back()->with('error', 'Activity not found.');
        }

        $validated = $request->validate([
            'activity_name' => 'sometimes|string|max:255',
            'position' => 'sometimes|in:required,optional,free',

            // Salary required only for task type
            'salary' => $activity->type == 'task'
                ? 'required|numeric|min:0'
                : 'nullable',

            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string',

            // Daniel, 2026-10-21: "i need to be able to add start and due
            // date in all activities, including the tasks, library and so
            // on."
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);


        // Prepare update data
        $updateData = array_merge($validated, [
            'cid' => $request->classId ?: null,
            // Daniel, 2026-10-21: "i can still see all the unnecesarry
            // settings here" - Max Points no longer collected per-activity
            // (see addActivity()); always cleared here too so an existing
            // row edited through this form also falls back to the global
            // default from this point on.
            'max_points' => null,
            'updated_at' => now()
        ]);
        // Remove salary for library type
        if ($activity->type != 'task') {
            unset($updateData['salary']);
        }

        DB::table('finhero_activity_registry')
            ->where('id', $id)
            ->update($updateData);
        // Calendar Event Insert / Update
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', session('selected_school') ?: null, )
            ->where('title', $activity->type . '-' . $validated['activity_name'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        // See MbaPositionController - FullCalendar's end is exclusive, +1
        // day so an admin-picked end_date is still shown as included.
        $calendarStart = !empty($validated['start_date']) ? \Carbon\Carbon::parse($validated['start_date'])->startOfDay() : now();
        $calendarEnd = !empty($validated['end_date']) ? \Carbon\Carbon::parse($validated['end_date'])->addDay()->startOfDay() : now();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'classId' => $request->classId ?: null,
                    'position' => $validated['position'],
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'start' => $calendarStart,
                    'end' => $calendarEnd,
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => session('selected_school') ?: null,
                'classId' => $request->classId ?: null,
                'title' => $activity->type . '-' . $validated['activity_name'],
                'description' => $validated['description'] ?? '',
                'position' => $validated['position'],
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => $calendarStart,
                'end' => $calendarEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $route = $activity->type == 'task'
            ? '/admin/finhero/activities'
            : '/admin/finhero/task-activities';

        return redirect($route)
            ->with('success', 'Activity updated.');
    }
    /** DELETE /admin/finhero/activities/{id} — remove an activity */
    //public function deleteActivity(int $id): JsonResponse
    public function deleteActivity(Request $request, int $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        DB::table('finhero_activity_registry')->where('id', $id)->delete();
        //return response()->json(['success' => true, 'message' => 'Activity removed.']);
        return redirect('/admin/finhero/activities')
            ->with('success', 'Activity removed.');
    }

    // ─────────────────────────────────────────────────────────────
    // GLOBAL POINT DEFAULTS
    // One default point value (and, for tasks, salary) per activity_key,
    // shared across every school. A finhero_activity_registry row only
    // overrides it when an admin explicitly set a value on that row.
    // ─────────────────────────────────────────────────────────────

    /** GET /admin/finhero/point-defaults */
    public function pointDefaultsIndex()
    {
        $defaults = FinheroActivityDefault::orderBy('activity_key')->get();

        return view('admin.finhero.point-defaults', compact('defaults'));
    }

    /** POST /admin/finhero/point-defaults — add a new default for an activity_key not yet registered anywhere */
    public function pointDefaultsStore(Request $request)
    {
        $validated = $request->validate([
            'activity_key' => ['required', 'string', 'max:255', 'unique:finhero_activity_defaults,activity_key'],
            'activity_name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:task,library'],
            'max_points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0'],
        ]);

        FinheroActivityDefault::create($validated);

        return back()->with('success', 'Default added.');
    }

    /** PUT /admin/finhero/point-defaults/{id} */
    public function pointDefaultsUpdate(Request $request, int $id)
    {
        $default = FinheroActivityDefault::findOrFail($id);

        $validated = $request->validate([
            'max_points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'salary' => ['nullable', 'numeric', 'min:0'],
        ]);

        $default->update($validated);

        return back()->with('success', 'Default updated - applies to every school that has not manually overridden this activity.');
    }

    // ─────────────────────────────────────────────────────────────
    // BADGE SETTINGS
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /admin/finhero/settings — save monthly badge settings
     * Body: { month, year, active_library_module_id, badge_active,
     *         threshold_legend, threshold_champion, threshold_finhero, threshold_rookie }
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'active_library_module_id' => 'nullable|integer',
            'badge_active' => 'required|boolean',
            'threshold_legend' => 'required|integer|min:1|max:100',
            'threshold_champion' => 'required|integer|min:1|max:100',
            'threshold_finhero' => 'required|integer|min:1|max:100',
            'threshold_rookie' => 'required|integer|min:1|max:100',
        ]);

        $academicYear = $this->calculator->getAcademicYear($validated['month'], $validated['year']);

        DB::table('finhero_monthly_settings')->updateOrInsert(
            ['month' => $validated['month'], 'year' => $validated['year']],
            array_merge($validated, ['academic_year' => $academicYear, 'updated_at' => now(), 'created_at' => now()])
        );

        return response()->json(['success' => true, 'message' => 'Settings saved.']);
    }

    /** GET /admin/finhero/settings?month=4&year=2026 — get settings for a month */
    public function getSettings(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $settings = DB::table('finhero_monthly_settings')->where('month', $month)->where('year', $year)->first();

        // Return defaults if no settings exist yet
        return response()->json($settings ?? [
            'month' => $month,
            'year' => $year,
            'active_library_module_id' => null,
            'badge_active' => true,
            'threshold_legend' => 90,
            'threshold_champion' => 70,
            'threshold_finhero' => 50,
            'threshold_rookie' => 25,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // REPORTS & STUDENT DATA
    // ─────────────────────────────────────────────────────────────

    /** GET /admin/finhero/student/{studentId} — full badge history for a student */
    public function studentBadgeHistory(int $studentId): JsonResponse
    {
        abort_unless(auth()->user()->canManageStudent($studentId), 403);
        $academicYear = $this->calculator->getAcademicYear(now()->month, now()->year);

        $records = FinheroBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->orderBy('year')->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'year' => $r->year,
                'quiz_pts' => $r->quiz_pts,
                'library_pts' => $r->library_pts,
                'activity_pts' => $r->activity_pts,
                'total_earned' => $r->total_earned,
                'total_available' => $r->total_available,
                'monthly_pct' => $r->monthly_pct,
                'monthly_badge' => $r->effective_monthly_badge,
                'monthly_badge_points' => $r->monthly_badge_points,
                'accumulated_points' => $r->accumulated_points,
                'accumulated_badge' => $r->accumulated_badge,
                'is_overridden' => $r->is_overridden,
                'badge_meta' => FinheroBadgeRecord::BADGE_META[$r->effective_monthly_badge],
            ]);

        $current = $records->last();

        return response()->json([
            'student_id' => $studentId,
            'academic_year' => $academicYear,
            'current_month' => $current,
            'accumulated_badge' => $current['accumulated_badge'] ?? 'NONE',
            'accumulated_points' => $current['accumulated_points'] ?? 0,
            'history' => $records,
        ]);
    }

    /** GET /admin/finhero/class-summary?month=4&year=2026 — class badge distribution */
    public function classSummary(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $summary = FinheroBadgeRecord::when(auth()->user()->manageableStudentIds() !== null, fn ($q) => $q->whereIn('student_id', auth()->user()->manageableStudentIds()))
            ->where('month', $month)->where('year', $year)
            ->selectRaw('monthly_badge, COUNT(*) as count')
            ->groupBy('monthly_badge')
            ->pluck('count', 'monthly_badge');

        return response()->json([
            'month' => $month,
            'year' => $year,
            'summary' => [
                'LEGEND' => $summary['LEGEND'] ?? 0,
                'CHAMPION' => $summary['CHAMPION'] ?? 0,
                'FINHERO' => $summary['FINHERO'] ?? 0,
                'ROOKIE' => $summary['ROOKIE'] ?? 0,
                'NONE' => $summary['NONE'] ?? 0,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ADMIN OVERRIDE
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /admin/finhero/override
     * Body: { student_id, month, year, badge: 'LEGEND'|'CHAMPION'|'FINHERO'|'ROOKIE'|'NONE' }
     */
    public function override(Request $request): JsonResponse
    {
        $v = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'badge' => 'required|in:LEGEND,CHAMPION,FINHERO,ROOKIE,NONE',
        ]);

        if (!empty($v['student_id'])) { abort_unless(auth()->user()->canManageStudent($v['student_id']), 403); }

        $record = FinheroBadgeRecord::where('student_id', $v['student_id'])
            ->where('month', $v['month'])->where('year', $v['year'])->first();

        if (!$record)
            return response()->json(['error' => 'No record found.'], 404);

        $record->update(['is_overridden' => true, 'override_badge' => $v['badge'], 'overridden_at' => now()]);

        return response()->json(['success' => true, 'message' => "Badge overridden to {$v['badge']}."]);
    }

    /** POST /admin/finhero/override/remove — remove override, revert to calculated */
    public function removeOverride(Request $request): JsonResponse
    {
        $v = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        if (!empty($v['student_id'])) { abort_unless(auth()->user()->canManageStudent($v['student_id']), 403); }

        FinheroBadgeRecord::where('student_id', $v['student_id'])
            ->where('month', $v['month'])->where('year', $v['year'])
            ->update(['is_overridden' => false, 'override_badge' => null, 'overridden_at' => null]);

        return response()->json(['success' => true, 'message' => 'Override removed.']);
    }

    /** POST /admin/finhero/recalculate — manual recalculation trigger */
    public function recalculate(Request $request): JsonResponse
    {
        $v = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'student_id' => 'nullable|integer|exists:users,id',
        ]);

        if (!empty($v['student_id'])) { abort_unless(auth()->user()->canManageStudent($v['student_id']), 403); }

        if (!empty($v['student_id'])) {
            $this->calculator->calculateForStudent($v['student_id'], $v['month'], $v['year']);
            $msg = "Recalculated for student {$v['student_id']}.";
        } else {
            $this->calculator->calculateAllStudentsForMonth($v['month'], $v['year']);
            $msg = "Recalculated for all students for {$v['month']}/{$v['year']}.";
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    /** POST /admin/finhero/reset-year — trigger academic year reset */
    public function resetAcademicYear(Request $request): JsonResponse
    {
        $v = $request->validate(['new_academic_year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/']]);
        // Historical records are kept. New year starts fresh automatically.
        return response()->json([
            'success' => true,
            'message' => "Academic year reset. New year: {$v['new_academic_year']}. Accumulated points restart from 0.",
        ]);
    }
}