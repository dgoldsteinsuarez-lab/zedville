<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Create and manage School Admin / Tutor accounts and tutor<->class
 * assignments. Per the Admin Roles & Permissions doc:
 *  - Super Admin creates School Admins and Tutors (any school).
 *  - School Admin creates Tutors and assigns them to classes - own school only.
 * Routes are wrapped in role:super_admin,school_admin; every action here
 * additionally checks the target belongs to the viewer's scope.
 */
class StaffController extends Controller
{
    private const ROLE_SCHOOL_ADMIN = 2;
    private const ROLE_TUTOR = 3;

    /** Staff accounts the current viewer is allowed to see. */
    private function visibleStaff()
    {
        $viewer = Auth::user();
        $query = User::whereIn('role', [self::ROLE_SCHOOL_ADMIN, self::ROLE_TUTOR]);

        if (!$viewer->isSuperAdmin()) {
            // School Admin: only Tutors of their own school.
            $query->where('role', self::ROLE_TUTOR)->where('sid', $viewer->sid);
        }

        return $query;
    }

    private function classesForViewer()
    {
        $viewer = Auth::user();
        $query = Grade::query();

        if (!$viewer->isSuperAdmin()) {
            $query->where('sid', $viewer->sid);
        }

        return $query->orderBy('name')->get();
    }

    public function index()
    {
        $staff = $this->visibleStaff()->with('tutoredClasses')->orderBy('name')->get();
        $classes = $this->classesForViewer();
        $schools = Auth::user()->isSuperAdmin() ? SchoolDomain::orderBy('school_name')->get() : collect();

        return view('admin.staff.index', compact('staff', 'classes', 'schools'));
    }

    public function store(Request $request)
    {
        $viewer = Auth::user();

        $allowedRoles = $viewer->isSuperAdmin()
            ? [self::ROLE_SCHOOL_ADMIN, self::ROLE_TUTOR]
            : [self::ROLE_TUTOR];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['required', 'string', 'min:2', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'integer', 'in:' . implode(',', $allowedRoles)],
            'sid' => [$viewer->isSuperAdmin() ? 'required' : 'nullable', 'integer', 'exists:school_domains,id'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);

        // A School Admin can only ever create inside their own school.
        $sid = $viewer->isSuperAdmin() ? (int) $data['sid'] : (int) $viewer->sid;

        // Classes must belong to that school (blocks assigning across schools).
        $classIds = collect($data['class_ids'] ?? [])
            ->filter(fn ($id) => Grade::where('id', $id)->where('sid', $sid)->exists())
            ->values();

        $temporaryPassword = Str::random(10) . random_int(10, 99) . '!';

        $user = User::create([
            'sid' => $sid,
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'role' => (int) $data['role'],
        ]);

        if ((int) $data['role'] === self::ROLE_TUTOR && $classIds->isNotEmpty()) {
            $user->tutoredClasses()->sync($classIds);
        }

        // Shown once to the creator, never stored in readable form.
        return redirect()->route('admin.staff.index')->with([
            'success' => 'Account created for ' . $user->email . '.',
            'temporary_password' => $temporaryPassword,
        ]);
    }

    /** Replace a Tutor's class assignments. */
    public function updateClasses(Request $request, $id)
    {
        $viewer = Auth::user();
        $tutor = $this->visibleStaff()->where('role', self::ROLE_TUTOR)->findOrFail($id);

        $request->validate([
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);

        $classIds = collect($request->input('class_ids', []))
            ->filter(fn ($cid) => Grade::where('id', $cid)->where('sid', $tutor->sid)->exists())
            ->values();

        $tutor->tutoredClasses()->sync($classIds);

        return redirect()->route('admin.staff.index')->with('success', 'Class assignments updated for ' . $tutor->nickname . '.');
    }
}
