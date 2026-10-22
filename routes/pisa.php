<?php

use App\Http\Controllers\Student\PisaController;
use Illuminate\Support\Facades\Route;

// Dedicated PISA student experience (bugs.md, 2026-10-21) - replaces the
// old academy.pisa shortcut, which just redirected into the generic
// Academy category view with no explainer and no level grouping.
Route::get('/', [PisaController::class, 'index'])->name('index');
Route::get('/level/{pisaLevel}', [PisaController::class, 'level'])->name('level');
Route::get('/leaderboard', [PisaController::class, 'leaderboard'])->name('leaderboard');
