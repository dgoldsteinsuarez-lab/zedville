<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\AcademyController;
use App\Http\Controllers\Student\MonthlyQuestionsController;

Route::get('/', [AcademyController::class, 'index'])->name('index');
Route::get('/monthly-questions', [MonthlyQuestionsController::class, 'index'])->name('monthly');
Route::get('/monthly-questions/final-review', [MonthlyQuestionsController::class, 'finalReview'])->name('monthly.final');
Route::post('/monthly-questions/final-review', [MonthlyQuestionsController::class, 'submitFinalReview'])->name('monthly.final.submit');
Route::get('/monthly-questions/{month}', [MonthlyQuestionsController::class, 'show'])->whereNumber('month')->name('monthly.show');
Route::post('/monthly-questions/{month}', [MonthlyQuestionsController::class, 'submit'])->whereNumber('month')->name('monthly.submit');
Route::get('/category/{category}', [AcademyController::class, 'category'])->name('category');
Route::get('/item/{item}', [AcademyController::class, 'item'])->name('item');
Route::post('/item/{item}/submit', [AcademyController::class, 'submit'])->name('item.submit');
