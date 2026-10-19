<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MailboxController;
use App\Http\Controllers\ConsumerSurveyController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LoginQuizController;
use App\Http\Controllers\Admin\FinheroAdminController;
use App\Http\Controllers\Student\FinheroStudentController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*Route::get('/citizen-activation', function () {
    $user = auth()->user();

    return view('citizen-activation', compact('user'));
})->middleware(['auth', 'verified'])->name('citizen-activation');*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/autopilot', [\App\Http\Controllers\StudentAutopilotController::class, 'store'])->name('profile.autopilot.store');
    Route::post('/profile/autopilot/{id}/cancel', [\App\Http\Controllers\StudentAutopilotController::class, 'cancel'])->name('profile.autopilot.cancel');
    Route::post('/profile/update-avatar', [ProfileController::class, 'updateAvatar'])->name('profile.updateAvatar');
    Route::post('/profile/save-mood', [MoodController::class, 'saveMode'])->name('profile.saveMode');
    // Mailbox routes
    Route::get('/profile/mailbox', [MailboxController::class, 'mailbox'])->name('profile.mailbox');
    Route::get('/profile/mailbox/show/{encryptedId}', [MailboxController::class, 'showMail'])->name('profile.showMail');
    Route::post('/profile/mailbox/update-status', [MailboxController::class, 'updateMailStatus'])->name('profile.mailbox.updateStatus');

     // ✅ ADD THESE LINES HERE (BEFORE THIS CLOSING BRACE)
    Route::post('/cart/save', [CartController::class, 'save'])->name('cart.save');
    Route::get('/cart/get', [CartController::class, 'get'])->name('cart.get');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/order/place', [OrderController::class, 'placeOrder'])->name('order.place');
    Route::post('/order/placeActivity', [OrderController::class, 'placeOrder'])->name('order.place');

    // Substitute Goods activity - Engagement badge, not FinHero (2026-10-19)
    Route::get('/profile/basket/state', [\App\Http\Controllers\BasketController::class, 'state'])->name('basket.state');
    Route::get('/profile/basket/options', [\App\Http\Controllers\BasketController::class, 'options'])->name('basket.options');
    Route::post('/profile/basket/swap', [\App\Http\Controllers\BasketController::class, 'swap'])->name('basket.swap');
    Route::get('/engagement/substitute-goods', fn () => view('engagement.substitute-goods'))->name('engagement.substitute-goods');
    Route::get('/toggle-view-mode', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'toggleViewMode'])->name('toggle-view-mode');
});

// Notification bell (read/unread) + Settings > Notifications preferences
Route::middleware('auth')->group(function () {
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'readOne'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
});
require __DIR__.'/auth.php';
require __DIR__.'/closet_routes.php';
Route::get('/profile/consumer-profile-survey', [ConsumerSurveyController::class, 'consumerProfileSurvey'])->name('consumer-profile-survey')->middleware('auth');
Route::post('/profile/storesurvey', [ConsumerSurveyController::class, 'storesurvey'])->name('storesurvey')->middleware('auth');

//Cms Routes
Route::get('/how-it-work', [\App\Http\Controllers\CMSController::class, 'howItWork'])->name('how-it-work');
Route::get('/faq', [\App\Http\Controllers\CMSController::class, 'faq'])->name('faq');
Route::get('/contact', [\App\Http\Controllers\CMSController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [\App\Http\Controllers\CMSController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [\App\Http\Controllers\CMSController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/thank-you', [\App\Http\Controllers\CMSController::class, 'thankYou'])->name('thank-you');
//Consumer Profile Survey
/*Route::get('/api/groceries/{diet}', function ($diet) {
    switch($diet) {
        case 'omnivore':
            return \DB::table('GroceriesOmnivore')->get();
        case 'vegetarian':
            return \DB::table('GroceriesVegetarian')->get();
        case 'pescatarian':
            return \DB::table('GroceriesPecastarian')->get();
        case 'vegan':
            return \DB::table('GroceriesVegan')->get();
        default:
            return response()->json([]);
    }
});*/
Route::get('/api/groceries/{diet}', function ($diet) {

    return \DB::table('supermarkets')
        ->whereRaw('LOWER(type) = ?', [strtolower($diet)])
        ->get();

});


//Consumer Profile Survey
//Calender Api Admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/api/admin/calendar-events', [\App\Http\Controllers\Admin\CalendarEventController::class, 'getEvents'])->name('admin.calendar-events');
    Route::post('/api/admin/calendar-events', [\App\Http\Controllers\Admin\CalendarEventController::class, 'storeEvent'])->name('admin.calendar-events.store');
    Route::put('/api/admin/calendar-events/{id}', [\App\Http\Controllers\Admin\CalendarEventController::class, 'updateEvent'])->name('admin.calendar-events.update');
    Route::delete('/api/admin/calendar-events/{id}', [\App\Http\Controllers\Admin\CalendarEventController::class, 'deleteEvent'])->name('admin.calendar-events.delete');
});
//For Login Quiz
Route::get('/login-quiz/today',   [LoginQuizController::class, 'getTodayQuestion'])->name('login.quiz.today')->middleware('auth');
Route::post('/login-quiz/submit', [LoginQuizController::class, 'submit'])->name('login.quiz.submit')->middleware('auth');

//City Mood & Profile Mood
Route::get('/city-mood',   [MoodController::class, 'city_mood'])->name('city-mood')->middleware('auth');
Route::post('/award-poster-room', function () {
    app(\App\Services\ParticipationService::class)
        ->award(auth()->id(), 'poster_room_enter');

    return response()->json(['success' => true]);
})->middleware('auth');

// Generic secure endpoint for frontend-triggered Participation actions
// that have no natural page-load/form-submit hook of their own (spec
// §7: these must go through a backend endpoint, never be awarded
// client-side only). Restricted to a small allow-list - extend the list
// as more such actions come up, see bugs.md, 2026-10-19.
Route::post('/participation/award', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'action' => 'required|in:street_sign_press',
        'detail' => 'nullable|string|max:255',
    ]);

    $awarded = app(\App\Services\ParticipationService::class)
        ->award(auth()->id(), $request->input('action'), $request->input('detail'));

    return response()->json(['success' => true, 'awarded' => $awarded]);
})->middleware('auth')->name('participation.award');

//Finhero route
Route::middleware(['auth'])->group(function () {
    Route::get('/finhero-badge',       [FinheroStudentController::class, 'index'])->name('finhero.index');
    Route::get('/finhero-badge/data',  [FinheroStudentController::class, 'data'])->name('finhero.data');

    // Called by IT when a student earns activity points
    // e.g. after Task 1 completion: POST /finhero/award-points { activity_key: 'task_1', points: 1 }
    Route::post('/finhero/award-points', [FinheroStudentController::class, 'awardPoints'])->name('finhero.award')->middleware('throttle:20,1');
     // Dynamic task page route
    Route::get('/finhero/{task}', [FinheroStudentController::class, 'taskPage'])
        ->where('task', 'task-[0-9]+')
        ->name('task.page');
        // Save task points
    Route::post('/finhero/save-points', [FinheroStudentController::class, 'savePoints'])
        ->name('finhero.save-points')->middleware('throttle:20,1');

});

// ── Admin / Tutor routes ───────────────────────────────────
// TODO: replace 'admin' with your actual admin/tutor middleware
Route::middleware(['auth', 'admin'])->prefix('admin/finhero')->group(function () {
  // Reports (student history, class summary) stay open to the admin tier;
  // everything that changes data is limited by role below.
  Route::middleware('role:super_admin,school_admin')->group(function () {

    // Activity Manager
    Route::get   ('/activities',       [FinheroAdminController::class, 'listActivities']);
    Route::get   ('/task-activities',       [FinheroAdminController::class, 'taskActivities']);
    Route::post  ('/activities',       [FinheroAdminController::class, 'addActivity']);
    Route::put   ('/activities/{id}',  [FinheroAdminController::class, 'updateActivity']);
    Route::delete('/activities/{id}',  [FinheroAdminController::class, 'deleteActivity']);

    // Badge Settings
    Route::get ('/settings',           [FinheroAdminController::class, 'getSettings']);
    Route::post('/settings',           [FinheroAdminController::class, 'saveSettings']);

  });
    // Reports
    Route::get ('/student/{studentId}',[FinheroAdminController::class, 'studentBadgeHistory']);
    Route::get ('/class-summary',      [FinheroAdminController::class, 'classSummary']);
  Route::middleware('role:super_admin,school_admin')->group(function () {
    // Override
    Route::post('/override',           [FinheroAdminController::class, 'override']);
    Route::post('/override/remove',    [FinheroAdminController::class, 'removeOverride']);

    // Tools
    Route::post('/recalculate',        [FinheroAdminController::class, 'recalculate']);
  });
    Route::post('/reset-year',         [FinheroAdminController::class, 'resetAcademicYear'])->middleware('role:super_admin');

    // Global point defaults - one value per activity, shared across every
    // school. Super Admin only: it is a platform-wide setting, not a
    // single-school one.
    Route::middleware('role:super_admin')->group(function () {
        Route::get ('/point-defaults',       [FinheroAdminController::class, 'pointDefaultsIndex'])->name('finhero.point-defaults.index');
        Route::post('/point-defaults',       [FinheroAdminController::class, 'pointDefaultsStore'])->name('finhero.point-defaults.store');
        Route::put ('/point-defaults/{id}',  [FinheroAdminController::class, 'pointDefaultsUpdate'])->name('finhero.point-defaults.update');
    });
});