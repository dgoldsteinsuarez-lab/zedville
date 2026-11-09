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
use App\Http\Controllers\Student\LanguageController;

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

// Daniel, 2026-10-26: the home page is the log-in page - the skyline with
// the log-in box, and "What is Zedville?" for schools below it. A signed-in
// citizen goes straight to their own home (the guest middleware).
Route::get('/', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest', 'guest.locale'])
    ->name('home');

/*Route::get('/citizen-activation', function () {
    $user = auth()->user();

    return view('citizen-activation', compact('user'));
})->middleware(['auth', 'verified'])->name('citizen-activation');*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Student language switcher (bugs.md, 2026-10-21) - SetStudentLocale
// middleware (global on the web group) reads what this writes.
Route::post('/language/switch', [LanguageController::class, 'switch'])
    ->middleware(['auth'])
    ->name('language.switch');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
    // Language activities of hybrid classes: match the words (2026-10-29)
    Route::get('/activities/words/{activity}', [\App\Http\Controllers\Student\WordMatchController::class, 'show'])->name('easy.words');
    Route::post('/activities/words/{activity}', [\App\Http\Controllers\Student\WordMatchController::class, 'done'])->name('easy.words.done');
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

    // product_name stays English (it is what the survey saves and groups
    // by); label is what the student reads, in their language.
    return \DB::table('supermarkets')
        ->whereRaw('LOWER(type) = ?', [strtolower($diet)])
        ->get()
        ->map(fn ($item) => tap($item, fn ($i) => $i->label = \App\Support\ContentTranslations::get('products', $i->product_name)));

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
// the plan of the month, chosen once a month after the question of the day (2026-10-31)
Route::post('/wants-plan/choose', [\App\Http\Controllers\WantsPlanController::class, 'choose'])->name('wants-plan.choose')->middleware('auth');

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

// "View as" (impersonation) - stop/return. Deliberately plain `auth`, not
// the admin-gated group in routes/admin.php: while impersonating a
// Student, the session's role genuinely is a Student, so the IsAdmin
// middleware would 403 this before it ever ran. Its own authorization is
// "does session('impersonator_id') exist," not a role check - see
// ImpersonateController.
Route::post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])
    ->middleware('auth')->name('impersonate.stop');

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
    // A Task page reached its "all questions done" screen
    Route::post('/finhero/task-complete', [FinheroStudentController::class, 'completeTask'])
        ->name('finhero.task-complete')->middleware('throttle:20,1');
    // A chart-and-questions Task (compound interest), graded on the server
    Route::post('/finhero/quiz/{task}/answers', [FinheroStudentController::class, 'quizAnswers'])
        ->where('task', 'task-[0-9]+')->name('finhero.quiz.answers')->middleware('throttle:30,1');
    // The Intermediate's price rise, as the Zedville Statistics Office tells it (App\Services\PriceRise)
    Route::get('/prices', [\App\Http\Controllers\Student\PriceIndexController::class, 'index'])->name('prices.index');
    // Zed Directo, the Intermediate's second bank from its third month (App\Services\ZedDirecto)
    Route::get('/zed-directo', [\App\Http\Controllers\ZedDirectoController::class, 'index'])->name('zeddirecto.index');
    Route::post('/zed-directo/open', [\App\Http\Controllers\ZedDirectoController::class, 'open'])->name('zeddirecto.open')->middleware('throttle:20,1');
    Route::post('/zed-directo/move', [\App\Http\Controllers\ZedDirectoController::class, 'move'])->name('zeddirecto.move')->middleware('throttle:20,1');
    Route::post('/zed-directo/deposit', [\App\Http\Controllers\ZedDirectoController::class, 'deposit'])->name('zeddirecto.deposit')->middleware('throttle:20,1');
    Route::post('/zed-directo/deposit/cancel', [\App\Http\Controllers\ZedDirectoController::class, 'cancel'])->name('zeddirecto.cancel')->middleware('throttle:20,1');
    // «Resuelve tu imprevisto»: the Intermediate's emergencies (App\Services\EmergencyService)
    Route::get('/emergency', [\App\Http\Controllers\EmergencyController::class, 'show'])->name('emergency.show');
    Route::post('/emergency/pay', [\App\Http\Controllers\EmergencyController::class, 'pay'])->name('emergency.pay')->middleware('throttle:20,1');
    Route::post('/emergency/answers', [\App\Http\Controllers\EmergencyController::class, 'answers'])->name('emergency.answers')->middleware('throttle:30,1');
    // the Intermediate's frauds (App\Services\FraudService): the call on screen, the fake email's page and button
    Route::post('/fraud/call/{fraud}', [\App\Http\Controllers\FraudController::class, 'call'])->name('fraud.call')->middleware('throttle:20,1');
    Route::get('/fraud/{token}', [\App\Http\Controllers\FraudController::class, 'lesson'])->name('fraud.lesson');
    Route::post('/fraud/{token}/report', [\App\Http\Controllers\FraudController::class, 'report'])->name('fraud.report')->middleware('throttle:20,1');
    Route::get('/secure-check/{token}', [\App\Http\Controllers\FraudController::class, 'fake'])->name('fraud.fake');
    Route::post('/secure-check/{token}', [\App\Http\Controllers\FraudController::class, 'verify'])->name('fraud.verify')->middleware('throttle:20,1');

    // Library activity reading/quiz page (Daniel, 2026-10-21: "5. do it")
    Route::get('/finhero/library/{key}', [FinheroStudentController::class, 'libraryPage'])
        ->name('library.page');
    Route::post('/finhero/library/{key}/submit', [FinheroStudentController::class, 'submitLibrary'])
        ->name('library.submit')->middleware('throttle:20,1');

});

// ── Admin / Tutor routes ───────────────────────────────────
// TODO: replace 'admin' with your actual admin/tutor middleware
Route::middleware(['auth', 'admin'])->prefix('admin/finhero')->group(function () {
  // Reports (student history, class summary) stay open to the admin tier;
  // everything that changes data is limited by role below.
  Route::middleware('role:super_admin,school_admin')->group(function () {

    // Activity Manager - each level's from the Admin menu by level (App\Support\AdminLevel, 2026-11-02)
    Route::middleware('admin.level')->group(function () {
    Route::get   ('/activities',       [FinheroAdminController::class, 'listActivities']);
    Route::get   ('/task-activities',       [FinheroAdminController::class, 'taskActivities']);
    Route::post  ('/activities',       [FinheroAdminController::class, 'addActivity']);
    Route::put   ('/activities/{id}',  [FinheroAdminController::class, 'updateActivity']);
    Route::delete('/activities/{id}',  [FinheroAdminController::class, 'deleteActivity']);
    });

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
        // A Library module's questions are shared by every school it's
        // assigned to, so editing them is platform-wide too.
        Route::get   ('/library-modules/{moduleId}/questions',              [FinheroAdminController::class, 'libraryQuestions']);
        Route::post  ('/library-modules/{moduleId}/levels',                 [FinheroAdminController::class, 'saveLibraryModuleLevels']);
        Route::post  ('/library-modules/{moduleId}/questions/save',         [FinheroAdminController::class, 'saveLibraryQuestion']);
        Route::delete('/library-modules/{moduleId}/questions/{questionId}', [FinheroAdminController::class, 'deleteLibraryQuestion']);

        Route::get ('/point-defaults',       [FinheroAdminController::class, 'pointDefaultsIndex'])->name('finhero.point-defaults.index');
        Route::post('/point-defaults',       [FinheroAdminController::class, 'pointDefaultsStore'])->name('finhero.point-defaults.store');
        Route::put ('/point-defaults/{id}',  [FinheroAdminController::class, 'pointDefaultsUpdate'])->name('finhero.point-defaults.update');
    });
});