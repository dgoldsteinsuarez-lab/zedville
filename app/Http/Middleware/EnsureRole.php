<?php

namespace App\Http\Middleware;

use Closure;

class EnsureRole
{
    /**
     * Usage: ->middleware('role:super_admin') or ->middleware('role:super_admin,school_admin')
     * Maps to User::isSuperAdmin()/isSchoolAdmin()/isTutor()/isStudent().
     * Runs after 'admin' (IsAdmin), which already confirmed the user is
     * authenticated and admin-tier — this narrows to specific role(s) within that.
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();
        $methodMap = [
            'super_admin' => 'isSuperAdmin',
            'school_admin' => 'isSchoolAdmin',
            'tutor' => 'isTutor',
            'student' => 'isStudent',
        ];

        foreach ($roles as $role) {
            $method = $methodMap[$role] ?? null;
            if ($method && $user && $user->$method()) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this.');
    }
}
