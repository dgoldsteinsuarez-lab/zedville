<?php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminEmailTemplateController;
use App\Http\Controllers\Admin\AdminEducationController;
use App\Http\Controllers\Admin\WellbeingController;
use App\Http\Controllers\Admin\CivicChamberController;
use App\Http\Controllers\Tutor\TutorStudentController;

Route::middleware(['auth', 'admin'])->group(function () {
// Dashboard Routes
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
Route::post('/school', [AdminDashboardController::class, 'setSchool'])->name('admin.school');
Route::post('/settings/salary', [AdminDashboardController::class, 'updateBaseSalary'])->name('admin.settings.salary');

// Profile Routes
Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
Route::post('/profile', [AdminDashboardController::class, 'updateProfile'])->name('updateProfile');
Route::post('/profile/password', [AdminDashboardController::class, 'updatePassword'])->name('updatePassword'); // ✅ Typo fixed

// Role Routes
Route::get('/role', [AdminDashboardController::class, 'role'])->name('role');
Route::post('/role/add', [AdminDashboardController::class, 'addRole'])->name('admin.role.add'); // ✅ Controller class corrected
Route::delete('/role/delete/{id}', [AdminDashboardController::class, 'deleteRole'])->name('admin.role.delete');
Route::get('/role/details/{id}', [AdminDashboardController::class, 'roleDetails'])->name('admin.role.details');
Route::put('/role/edit/{id}', [AdminDashboardController::class, 'updateRole'])->name('admin.role.update');

//Grade / CLass
Route::get('/grade', [AdminDashboardController::class, 'grade'])->name('grade');
Route::post('/grade/add', [AdminDashboardController::class, 'addGrade'])->name('admin.grade.add');
Route::put('/grade/edit/{id}', [AdminDashboardController::class, 'updateGrade'])->name('admin.grade.update');
Route::delete('/grade/delete/{id}', [AdminDashboardController::class, 'deleteGrade'])->name('admin.grade.delete');

//School Domain
Route::get('/school-domain', [AdminDashboardController::class, 'schoolDomain'])->name('school-domain');
Route::post('/school-domain/add', [AdminDashboardController::class, 'addschoolDomain'])->name('admin.school-domain.add');
Route::put('/school-domain/edit/{id}', [AdminDashboardController::class, 'updateschoolDomain'])->name('admin.grade.update');
Route::delete('/school-domain/delete/{id}', [AdminDashboardController::class, 'deleteschoolDomain'])->name('admin.school-domain.delete');

// Tutor's own student list (their assigned classes only)
Route::get('/my-students', [TutorStudentController::class, 'index'])->name('tutor.my-students');

//For Student
Route::get('/student', [AdminStudentController::class, 'index'])->name('admin.student.index');
Route::get('/student/details/{id}', [AdminStudentController::class, 'details'])->name('admin.student.details');
Route::post('/student/update-details/{id}', [AdminStudentController::class, 'update_details'])->name('admin.student.update-details');
Route::post('/student/add-student', [AdminStudentController::class, 'add_student'])->name('admin.student.add-student');

//For Email Template
Route::get('/email-template', [AdminEmailTemplateController::class, 'index'])->name('admin.email-template.index');
Route::post('/email-template/{id}', [AdminEmailTemplateController::class, 'update'])->name('admin.email-template.update');
Route::get('/email/communication', [AdminEmailTemplateController::class, 'communication'])->name('admin.email.communication');
Route::post('/email/send', [AdminEmailTemplateController::class, 'send'])->name('admin.email.send');
Route::get('/email/sent-email', [AdminEmailTemplateController::class, 'sentEmail'])->name('admin.email.sent-email');


//Education
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
//For Calender
Route::get('/calendar', [AdminDashboardController::class, 'calendar'])->name('admin.calendar');
//Class List
Route::get('/class-list', [AdminDashboardController::class, 'class_list'])->name('admin.class-list');
//City Bank Account
Route::get('/accounts', [AdminDashboardController::class, 'city_bank_account'])->name('accounts');
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
// =====================================
// Wellbeing Room
// =====================================

// List
Route::get('/wellbeing', [WellbeingController::class, 'index'])
    ->name('wellbeing.index');

// Create Form
Route::get('/wellbeing/create', [WellbeingController::class, 'create'])
    ->name('wellbeing.create');

// Store
Route::post('/wellbeing/store', [WellbeingController::class, 'store'])
    ->name('wellbeing.store');

// Edit Form
Route::get('/wellbeing/edit/{id}', [WellbeingController::class, 'edit'])
    ->name('wellbeing.edit');

// Update
Route::put('/wellbeing/update/{id}', [WellbeingController::class, 'update'])
    ->name('wellbeing.update');

// Delete
Route::delete('/wellbeing/delete/{id}', [WellbeingController::class, 'destroy'])
    ->name('wellbeing.destroy');

/*
|--------------------------------------------------------------------------
| Civic Chamber - Referendum
|--------------------------------------------------------------------------
*/

// Referendum List
Route::get('/referendum', [CivicChamberController::class, 'referendumIndex'])
    ->name('referendum.index');

// Create
Route::get('/referendum/create', [CivicChamberController::class, 'referendumCreate'])
    ->name('referendum.create');

// Store
Route::post('/referendum/store', [CivicChamberController::class, 'referendumStore'])
    ->name('referendum.store');

// Edit
Route::get('/referendum/edit/{id}', [CivicChamberController::class, 'referendumEdit'])
    ->name('referendum.edit');

// Update
Route::put('/referendum/update/{id}', [CivicChamberController::class, 'referendumUpdate'])
    ->name('referendum.update');

// Delete
Route::delete('/referendum/delete/{id}', [CivicChamberController::class, 'referendumDestroy'])
    ->name('referendum.destroy');



/*
|--------------------------------------------------------------------------
| Civic Chamber - Petition
|--------------------------------------------------------------------------
*/

// Petition List
Route::get('/petition', [CivicChamberController::class, 'petitionIndex'])
    ->name('petition.index');

// View Petition
Route::get('/petition/show/{id}', [CivicChamberController::class, 'petitionShow'])
    ->name('petition.show');

// Save Tutor Feedback
Route::post('/petition/update-feedback/{id}', [CivicChamberController::class, 'petitionFeedback'])
    ->name('petition.feedback');

// Approve Petition
Route::post('/petition/approve/{id}', [CivicChamberController::class, 'petitionApprove'])
    ->name('petition.approve');

// Reject Petition
Route::post('/petition/reject/{id}', [CivicChamberController::class, 'petitionReject'])
    ->name('petition.reject');

// Close Petition
Route::post('/petition/close/{id}', [CivicChamberController::class, 'petitionClose'])
    ->name('petition.close');

// Delete Petition
Route::delete('/petition/delete/{id}', [CivicChamberController::class, 'petitionDestroy'])
    ->name('petition.destroy');

});
