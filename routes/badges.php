<?php

/**
 * ZEDVILLE — Engagement Badge
 * Routes: Badge System
 *
 * INSTRUCTIONS FOR IT:
 * Add these routes to your existing routes/web.php (or routes/api.php).
 *
 * 1. If using routes/web.php, add inside your existing route group
 *    that has your auth middleware applied.
 *
 * 2. Replace 'auth' and 'admin' with your actual middleware names.
 *    - 'auth'  → whatever middleware checks the user is logged in
 *    - 'admin' → whatever middleware checks the user is admin or tutor
 *
 * 3. Add this import at the top of your routes file if not already present:
 *    use App\Http\Controllers\Admin\BadgeAdminController;
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BadgeAdminController;

// Students see their badges on the Badges tab of their settings and on the
// home page (App\Services\BadgeOverview). The old /badges page, which no menu
// linked to and still showed the old rule's numbers, is gone (Daniel,
// 2026-11-02: "bórrala").

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN / TUTOR ROUTES — admin and tutor access only
// TODO: Replace 'admin' with your actual admin/tutor middleware name
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin/badges')->group(function () {
    // ✅ View page (Blade)
    Route::get('/student/{studentId}/view', [BadgeAdminController::class, 'studentView'])
        ->name('admin.badges.student.view');

    // View a specific student's full badge history
    Route::get('/student/{studentId}', [BadgeAdminController::class, 'studentBadgeHistory'])
        ->name('admin.badges.student');

    // Class-wide badge summary for a given month
    Route::get('/class-summary', [BadgeAdminController::class, 'classSummary'])
        ->name('admin.badges.class-summary');

    // Override a student's monthly badge
    Route::post('/override', [BadgeAdminController::class, 'override'])
        ->name('admin.badges.override')->middleware('role:super_admin,school_admin');

    // Remove an override and revert to calculated badge
    Route::post('/override/remove', [BadgeAdminController::class, 'removeOverride'])
        ->name('admin.badges.override.remove')->middleware('role:super_admin,school_admin');

    // Manually trigger recalculation (single student or all)
    Route::post('/recalculate', [BadgeAdminController::class, 'recalculate'])
        ->name('admin.badges.recalculate')->middleware('role:super_admin,school_admin');

    // Academic year reset
    Route::post('/reset-year', [BadgeAdminController::class, 'resetAcademicYear'])
        ->name('admin.badges.reset-year')->middleware('role:super_admin');

});
