<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\AcademyController;

Route::get('/', [AcademyController::class, 'index'])->name('index');
Route::get('/pisa', [AcademyController::class, 'pisaShortcut'])->name('pisa');
Route::get('/category/{category}', [AcademyController::class, 'category'])->name('category');
Route::get('/item/{item}', [AcademyController::class, 'item'])->name('item');
Route::post('/item/{item}/submit', [AcademyController::class, 'submit'])->name('item.submit');
