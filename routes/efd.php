<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EFDController;

Route::get('/', [EFDController::class, 'index'])->name('index');
Route::get('/educational-finance-department', [EFDController::class, 'educational_finance_department'])->name('educational_finance_department');
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

// The Money Factory (Daniel, 2026-11-02): where Zedland prints its money, the
// currency exchange, and two pages to find out more.
Route::get('/money-factory', [\App\Http\Controllers\MoneyFactoryController::class, 'index'])->name('money-factory');
Route::get('/money-factory/money-supply', [\App\Http\Controllers\MoneyFactoryController::class, 'supply'])->name('money-factory.supply');
Route::get('/money-factory/too-little-or-too-much', [\App\Http\Controllers\MoneyFactoryController::class, 'circulation'])->name('money-factory.circulation');
Route::get('/money-factory/how-money-gets-in', [\App\Http\Controllers\MoneyFactoryController::class, 'enter'])->name('money-factory.enter');
Route::post('/money-factory/exchange', [\App\Http\Controllers\MoneyFactoryController::class, 'exchange'])->name('money-factory.exchange');
Route::get('/money-factory/tracker', [\App\Http\Controllers\MoneyFactoryController::class, 'tracker'])->name('money-factory.tracker');
Route::post('/money-factory/tracker/answers', [\App\Http\Controllers\MoneyFactoryController::class, 'trackerAnswers'])->name('money-factory.tracker.answers');
Route::get('/money-factory/activities/{slug}', [\App\Http\Controllers\MoneyFactoryController::class, 'activity'])->name('money-factory.activity');
Route::post('/money-factory/activities/{slug}/answers', [\App\Http\Controllers\MoneyFactoryController::class, 'activityAnswers'])->name('money-factory.activity.answers');

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


