<?php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminEmailTemplateController;
use App\Http\Controllers\Admin\AdminEducationController;
use App\Http\Controllers\Admin\WellbeingController;
use App\Http\Controllers\Admin\CivicChamberController;
use App\Http\Controllers\Tutor\TutorStudentController;

Route::middleware(['auth', 'admin'])->group(function () {

// ================================================================
// Open to any admin-tier role (Super Admin, School Admin, Tutor) —
// everyone manages their own profile, has a dashboard, etc.
// ================================================================

// Dashboard Routes
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
Route::post('/school', [AdminDashboardController::class, 'setSchool'])->name('admin.school');
// updateBaseSalary already guards itself to Super Admin internally
Route::post('/settings/salary', [AdminDashboardController::class, 'updateBaseSalary'])->name('admin.settings.salary');

// Profile Routes
Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
Route::post('/profile', [AdminDashboardController::class, 'updateProfile'])->name('updateProfile');
Route::post('/profile/password', [AdminDashboardController::class, 'updatePassword'])->name('updatePassword'); // ✅ Typo fixed

// Tutor's own student list (self-scoped to their assigned classes inside the controller)
Route::get('/my-students', [TutorStudentController::class, 'index'])->name('tutor.my-students');

//For Calender
Route::get('/calendar', [AdminDashboardController::class, 'calendar'])->name('admin.calendar');

// Civic Chamber — viewing only (taking action is restricted further below)
Route::get('/referendum', [CivicChamberController::class, 'referendumIndex'])->name('referendum.index');
Route::get('/petition', [CivicChamberController::class, 'petitionIndex'])->name('petition.index');
Route::get('/petition/show/{id}', [CivicChamberController::class, 'petitionShow'])->name('petition.show');

// Wellbeing Room — already self-restricted to Super Admin inside the controller
Route::get('/wellbeing', [WellbeingController::class, 'index'])->name('wellbeing.index');
Route::get('/wellbeing/create', [WellbeingController::class, 'create'])->name('wellbeing.create');
Route::post('/wellbeing/store', [WellbeingController::class, 'store'])->name('wellbeing.store');
Route::get('/wellbeing/edit/{id}', [WellbeingController::class, 'edit'])->name('wellbeing.edit');
Route::put('/wellbeing/update/{id}', [WellbeingController::class, 'update'])->name('wellbeing.update');
Route::delete('/wellbeing/delete/{id}', [WellbeingController::class, 'destroy'])->name('wellbeing.destroy');


// ================================================================
// Super Admin only — platform-wide configuration and provisioning.
// Matches the Admin Roles & Permissions doc: Role/School Admin
// creation, mass communication, and the Roles-table CRUD are all
// Super-Admin-exclusive.
// ================================================================
Route::middleware('role:super_admin')->group(function () {
    // Role Routes
    Route::get('/role', [AdminDashboardController::class, 'role'])->name('role');
    Route::post('/role/add', [AdminDashboardController::class, 'addRole'])->name('admin.role.add');
    Route::delete('/role/delete/{id}', [AdminDashboardController::class, 'deleteRole'])->name('admin.role.delete');
    Route::get('/role/details/{id}', [AdminDashboardController::class, 'roleDetails'])->name('admin.role.details');
    Route::put('/role/edit/{id}', [AdminDashboardController::class, 'updateRole'])->name('admin.role.update');

    //School Domain — provisioning a new school on the platform
    Route::get('/school-domain', [AdminDashboardController::class, 'schoolDomain'])->name('school-domain');
    Route::post('/school-domain/add', [AdminDashboardController::class, 'addschoolDomain'])->name('admin.school-domain.add');
    Route::put('/school-domain/edit/{id}', [AdminDashboardController::class, 'updateschoolDomain'])->name('admin.grade.update');
    Route::delete('/school-domain/delete/{id}', [AdminDashboardController::class, 'deleteschoolDomain'])->name('admin.school-domain.delete');

    //For Email Template — mass communication to users
    Route::get('/email-template', [AdminEmailTemplateController::class, 'index'])->name('admin.email-template.index');
    Route::post('/email-template/{id}', [AdminEmailTemplateController::class, 'update'])->name('admin.email-template.update');
    Route::get('/email/communication', [AdminEmailTemplateController::class, 'communication'])->name('admin.email.communication');
    Route::post('/email/send', [AdminEmailTemplateController::class, 'send'])->name('admin.email.send');
    Route::get('/email/sent-email', [AdminEmailTemplateController::class, 'sentEmail'])->name('admin.email.sent-email');
});


// ================================================================
// Super Admin + School Admin — institutional/organisational setup.
// Per the doc: School Admin registers accounts and organises classes
// and curriculum content, but doesn't touch individual petitions,
// referendums, or Wellbeing content.
// ================================================================
Route::middleware('role:super_admin,school_admin')->group(function () {
    //Grade / Class
    Route::get('/grade', [AdminDashboardController::class, 'grade'])->name('grade');
    Route::post('/grade/add', [AdminDashboardController::class, 'addGrade'])->name('admin.grade.add');
    Route::put('/grade/edit/{id}', [AdminDashboardController::class, 'updateGrade'])->name('admin.grade.update');
    Route::delete('/grade/delete/{id}', [AdminDashboardController::class, 'deleteGrade'])->name('admin.grade.delete');

    //For Student (school-wide admin list — a Tutor's own class is /my-students instead)
    Route::get('/student', [AdminStudentController::class, 'index'])->name('admin.student.index');
    Route::get('/student/details/{id}', [AdminStudentController::class, 'details'])->name('admin.student.details');
    Route::post('/student/update-details/{id}', [AdminStudentController::class, 'update_details'])->name('admin.student.update-details');
    Route::post('/student/add-student', [AdminStudentController::class, 'add_student'])->name('admin.student.add-student');

    //Class List
    Route::get('/class-list', [AdminDashboardController::class, 'class_list'])->name('admin.class-list');
    //City Bank Account (all-classes dashboard — a Tutor only ever sees their own class elsewhere)
    Route::get('/accounts', [AdminDashboardController::class, 'city_bank_account'])->name('accounts');

    //Education — curriculum/content configuration
    Route::get('/education/monthly-budget-activity', [AdminEducationController::class, 'monthly_budget_activity'])->name('admin.education.monthly-budget-activity');
    Route::post('/education/mba-position', [AdminEducationController::class, 'mba_position'])->name('admin.education.mba-position');
    Route::get('/education/mba-position/delete/{id}', [AdminEducationController::class, 'mba_position_delete'])
        ->name('admin.education.mba-position.delete');

    Route::get('/education/emergency-fund-account', [AdminEducationController::class, 'emergencyFundAccount'])->name('admin.education.emergency-fund-account');
    Route::post('/education/emergency-fund-position', [AdminEducationController::class, 'emergencyFundPosition'])->name('admin.education.emergency-fund-position');

    Route::get('/education/high-budget-activity', [AdminEducationController::class, 'high_budget_activity'])->name('admin.education.high-budget-activity');
    Route::post('/education/hba-position', [AdminEducationController::class, 'hba_position'])->name('admin.education.hba-position');

    Route::get('/education/low-budget-activity', [AdminEducationController::class, 'low_budget_activity'])->name('admin.education.low-budget-activity');
    Route::post('/education/lba-position', [AdminEducationController::class, 'lba_position'])->name('admin.education.lba-position');

    //Poster
    Route::get('/education/poster', [AdminEducationController::class, 'poster'])->name('admin.education.poster');
    Route::post('/education/storePoster', [AdminEducationController::class, 'storePoster'])->name('admin.education.storePoster');
    Route::put('/education/updatePoster/{id}', [AdminEducationController::class, 'updatePoster'])->name('admin.education.updatePoster');
    Route::delete('/education/deletePoster/{id}', [AdminEducationController::class, 'deletePoster'])->name('admin.education.deletePoster');
    //spandBudget
    Route::get('/education/city-mall', [AdminEducationController::class, 'spandbudget'])->name('admin.education.city-mall');
    Route::post('/education/product-store', [AdminEducationController::class, 'productStore'])->name('admin.education.product-store');
    Route::get('/education/products-list', [AdminEducationController::class, 'productList'])->name('admin.education.products-list');
    Route::post('/education/products-update/{id}', [AdminEducationController::class, 'productUpdate'])->name('admin.education.products-update');
    Route::delete('/education/products-delete/{id}', [AdminEducationController::class, 'productDelete'])->name('admin.education.products-delete');
    //For Store
    Route::get('/education/city-mall-store', [AdminEducationController::class, 'cityMallStore'])->name('admin.education.city-mall-store');
    Route::post('/education/create-city-mall-store', [AdminEducationController::class, 'CreatecityMallStore'])->name('admin.education.create-city-mall-store');
    Route::get('/education/store-list', [AdminEducationController::class, 'storeList'])->name('admin.education.store-list');
    Route::post('/education/store-update/{id}', [AdminEducationController::class, 'storeUpdate'])->name('admin.education.store-update');
    Route::delete('/education/store-delete/{id}', [AdminEducationController::class, 'storeDelete'])->name('admin.education.store-delete');
    //For Supermarket
    Route::get('/education/supermarket', [AdminEducationController::class, 'supermarket'])->name('admin.education.supermarket');
    Route::post('/education/supermarket-store', [AdminEducationController::class, 'supermarketStore'])->name('admin.education.supermarket-store');
    Route::get('/education/supermarket-list', [AdminEducationController::class, 'supermarketList'])->name('admin.education.supermarket-list');
    Route::post('/education/supermarket-update/{id}', [AdminEducationController::class, 'supermarketUpdate'])->name('admin.education.supermarket-update');
    Route::delete('/education/supermarket-delete/{id}', [AdminEducationController::class, 'supermarketDelete'])->name('admin.education.supermarket-delete');
    //For Wants Iteam
    Route::get('/education/wants-iteams', [AdminEducationController::class, 'wantsIteam'])->name('admin.education.wants-iteams');
    Route::post('/education/wants-iteams-store', [AdminEducationController::class, 'wantsIteamStore'])->name('admin.education.wants-iteams-store');
    Route::get('/education/wants-iteams-list', [AdminEducationController::class, 'wantsIteamList'])->name('admin.education.wants-iteams-list');
    Route::post('/education/wants-iteams-update/{id}', [AdminEducationController::class, 'wantsIteamUpdate'])->name('admin.education.wants-iteams-update');
    Route::delete('/education/wants-iteams-delete/{id}', [AdminEducationController::class, 'wantsIteamDelete'])->name('admin.education.wants-iteams-delete');
    //For NPOs
    Route::get('/npos', [AdminEducationController::class, 'npos'])->name('admin.npos.npos');
    Route::post('/npo/add-npo', [AdminEducationController::class, 'add_npo'])->name('admin.npo.add-npo');
    Route::get('/npo/edit/{id}', [AdminEducationController::class, 'edit_npo']);
    Route::post('/npo/update/{id}', [AdminEducationController::class, 'update_npo']);
    Route::get('/npo/delete/{id}', [AdminEducationController::class, 'delete_npo']);
    //For Login Question
    Route::get('/login-question', [AdminDashboardController::class, 'login_question'])->name('admin.login-question.login-question');
    Route::post('/login-question/loginQuestionStore', [AdminDashboardController::class, 'loginQuestionStore'])->name('admin.login-question.loginQuestionStore');
    Route::delete('/login-question/{id}', [AdminDashboardController::class, 'loginQuestionDestroy'])->name('admin.login-question.loginQuestionDestroy');
    Route::get('/login-question/school-month-settings', [AdminDashboardController::class, 'school_month_settings'])->name('admin.login-question.school-month-settings');
    Route::post('/login-question/bulkSaveSMS', [AdminDashboardController::class, 'bulkSaveSMS'])->name('admin.login-question.bulkSaveSMS');
});


// ================================================================
// Super Admin + Tutor — day-to-day class moderation: referendums,
// petitions. School Admin is organisational, not moderation, per
// the doc ("Approve petitions" / referendum creation = Tutor only
// besides Super Admin).
// ================================================================
Route::middleware('role:super_admin,tutor')->group(function () {
    // Referendum — create/edit/delete (viewing is in the open group above)
    Route::get('/referendum/create', [CivicChamberController::class, 'referendumCreate'])->name('referendum.create');
    Route::post('/referendum/store', [CivicChamberController::class, 'referendumStore'])->name('referendum.store');
    Route::get('/referendum/edit/{id}', [CivicChamberController::class, 'referendumEdit'])->name('referendum.edit');
    Route::put('/referendum/update/{id}', [CivicChamberController::class, 'referendumUpdate'])->name('referendum.update');
    Route::delete('/referendum/delete/{id}', [CivicChamberController::class, 'referendumDestroy'])->name('referendum.destroy');

    // Petition — moderation actions (viewing is in the open group above)
    Route::post('/petition/update-feedback/{id}', [CivicChamberController::class, 'petitionFeedback'])->name('petition.feedback');
    Route::post('/petition/approve/{id}', [CivicChamberController::class, 'petitionApprove'])->name('petition.approve');
    Route::post('/petition/reject/{id}', [CivicChamberController::class, 'petitionReject'])->name('petition.reject');
    Route::post('/petition/close/{id}', [CivicChamberController::class, 'petitionClose'])->name('petition.close');
    Route::delete('/petition/delete/{id}', [CivicChamberController::class, 'petitionDestroy'])->name('petition.destroy');
});

});
