<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-20: "I also would like to be able to switch from my
 * superadmin to all other roles without having to sign in and out...
 * being able to see the rights of School Admin, tutor or student."
 *
 * Real-user impersonation, not a generic role preview - every role check
 * in this app (User::isSuperAdmin()/isSchoolAdmin()/isTutor()/isStudent())
 * reads $this->role straight off the authenticated user row, so actually
 * logging in as a real user of that role shows its exact real rights with
 * no separate permission layer to build or keep in sync.
 *
 * start() is Super-Admin-only and lives under routes/admin.php's existing
 * role:super_admin group. stop() deliberately does NOT - while
 * impersonating a Student, the active session's role genuinely is a
 * Student, so the IsAdmin middleware would 403 it before this controller
 * ever ran. stop() is registered in routes/web.php under plain auth
 * middleware instead; its authorization is "does session('impersonator_id')
 * exist," not a role check.
 */
class ImpersonateController extends Controller
{
    public function start(User $user)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        abort_if(session()->has('impersonator_id'), 403, 'Already viewing as someone else - return to your own account first.');
        abort_if($user->isSuperAdmin(), 403, 'Cannot view as another Super Admin.');

        $impersonatorId = Auth::id();

        DB::table('impersonation_logs')->insert([
            'impersonator_id' => $impersonatorId,
            'impersonated_id' => $user->id,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['impersonator_id' => $impersonatorId]);
        Auth::login($user);

        return $user->isStudent() ? redirect('/dashboard') : redirect('/admin/dashboard');
    }

    public function stop()
    {
        $impersonatorId = session('impersonator_id');
        abort_unless($impersonatorId, 403);

        DB::table('impersonation_logs')
            ->where('impersonator_id', $impersonatorId)
            ->where('impersonated_id', Auth::id())
            ->whereNull('ended_at')
            ->orderByDesc('id')
            ->limit(1)
            ->update(['ended_at' => now()]);

        session()->forget('impersonator_id');
        Auth::loginUsingId($impersonatorId);

        return redirect('/admin/dashboard');
    }
}
