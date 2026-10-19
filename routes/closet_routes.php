<?php

use App\Http\Controllers\ClosetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('closet')->name('closet.')->group(function () {
    // Closet page
    Route::get('/', function () {
        app(\App\Services\ParticipationService::class)->award(auth()->id(), 'closet_enter');
        return view('closet.index');
    })->name('index');

    // ── Student: own closet ──────────────────────────────────
    Route::get('/mine',                [ClosetController::class, 'mine'])->name('mine');

    // ── Tutor / Admin: view any student's closet ─────────────
    Route::get('/student/{studentId}', [ClosetController::class, 'studentCloset'])->name('student');

    // ── Catalog: read (all roles) ────────────────────────────
    Route::get('/catalog',             [ClosetController::class, 'catalog'])->name('catalog');

    // ── Catalog: write (admin only) ──────────────────────────
    Route::post('/catalog',            [ClosetController::class, 'catalogStore'])->name('catalog.store');
    Route::put('/catalog/{id}',        [ClosetController::class, 'catalogUpdate'])->name('catalog.update');
});
