<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EFDController;

Route::get('/', [EFDController::class, 'index'])->name('index');
Route::get('/educational-finance-department', [EFDController::class, 'educational_finance_department'])->name('educational_finance_department');
Route::get('/multiple-choice-email', [EFDController::class, 'multipleChoiceEmail'])->name('multipleChoiceEmail');
Route::get('/quize-multiple-choice-question', [EFDController::class, 'quizMultipleChoiceQuestion'])->name('quizMultipleChoiceQuestion');
Route::get('/budget-quiz', [EFDController::class, 'budgetQuiz'])->name('budgetQuiz');
Route::get('/budget-quiz-2', [EFDController::class, 'budgetQuiz2'])->name('budgetQuiz2');
Route::post('/store-mba', [EFDController::class, 'storemba'])->name('storemba');
Route::get('/mba-data', [EFDController::class, 'getMbaData']);
Route::get('/city-mall', [EFDController::class, 'city_mall'])->name('city-mall');
Route::get('/spending-tracker/{type}', [EFDController::class, 'spending_tracker'])->name('spending-tracker');
Route::get('/calendar', [EFDController::class, 'calendar'])->name('calendar');
Route::get('/get-event-by-user', [EFDController::class, 'getEventsByUserClass'])->name('get-event-by-user');
Route::get('/npos', [EFDController::class, 'npos'])->name('npos');
Route::get('/npos/{slug}', [EFDController::class, 'nposDonate'])->name('npos.donate');
Route::post('/store-donation', [EFDController::class,'storeDonation'])->name('store.storeDonation');
Route::get('/high-spending-activities', [EFDController::class,'high_spending_activities'])->name('high-spending-activities');
// low-spending-activities removed (Daniel, 2026-10-21: "remove low
// spending activities from the system") - see bugs.md.
//For City Hall
Route::get('/city-hall', [EFDController::class, 'city_hall'])->name('city-hall');
Route::get('/main-hall', [EFDController::class, 'main_hall'])->name('main-hall');
Route::get('/civic-chamber', [EFDController::class, 'civic_chamber'])->name('civic-chamber');

// City Map, Phase 1 (2026-10-19) - the interactive city map with building
// and street-plaque hotspots, split into an overview + 9 district pages.
Route::get('/city', [\App\Http\Controllers\CityController::class, 'index'])->name('city');
Route::get('/city/{district}', [\App\Http\Controllers\CityController::class, 'district'])->name('city.district');
Route::get('/well-being-room', [EFDController::class, 'well_being_room'])->name('well-being-room');

// Civic Chamber AJAX

Route::post('/petition/create', [EFDController::class, 'submitPetition'])
    ->name('petition.create');

Route::post('/city-proposal/create', [EFDController::class, 'submitCityProposal'])
    ->name('city-proposal.create');

//Route::post('/referendum/vote', [EFDController::class, 'castVote'])
    //->name('referendum.vote');
Route::post('/referendum/{id}/vote', [EFDController::class, 'castVote'])
    ->name('referendum.vote');

//Route::post('/petition/sign/{id}', [EFDController::class, 'signPetition'])
   // ->name('petition.sign');
Route::post('/petition/{id}/sign', [EFDController::class, 'signPetition'])->name('petition.sign');

Route::get('/referendum/result/{id}', [EFDController::class, 'referendumResult'])
    ->name('referendum.result');


