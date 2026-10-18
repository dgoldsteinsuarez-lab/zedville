<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CitizenActivationController;

Route::controller(CitizenActivationController::class)->group(function () {

    Route::get('/', 'index')->name('index');

    Route::get('/bank-account', 'bankAccount')->name('bankAccount');
    Route::post('/store', 'store')->name('store');

    //Route::get('/salary-authorization', 'salaryAuthorization')->name('salaryAuthorization');

    Route::get('/salary-authorization', 'salaryAuthorization')->name('salaryAuthorization');
    Route::post('/authorize-direct-deposit', 'authorize_direct_deposit')->name('authorize-direct-deposit');

    Route::get('/auto-debit', 'autoDebit')->name('autoDebit');
    Route::post('/store-auto-debit-authorization-form', 'store_auto_debit_authorization_form')->name('store-auto-debit-authorization-form');

    Route::get('/consumer-profile', 'consumerProfile')->name('consumerProfile');
    // Temporary unblock - see CitizenActivationController::skipConsumerProfile()
    Route::post('/consumer-profile/skip', 'skipConsumerProfile')->name('consumerProfile.skip');

    Route::get('/activation-complete', 'activationComplete')->name('activationComplete');

});