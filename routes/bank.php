<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankController;
use App\Http\Controllers\AutoDebitController;
use App\Http\Controllers\SavingsAccountController;

Route::get('/', [BankController::class, 'index'])->name('index');
Route::post('/', [BankController::class, 'store'])->name('store');
Route::get('/my-account', [BankController::class, 'my_account'])->name('my_account');
Route::get('/transfer', [BankController::class, 'transfer'])->name('transfer');
Route::post('/transfer', [BankController::class, 'transfer_store'])->name('transfer_store');
Route::post('/beneficiary', [BankController::class, 'beneficiary_store'])->name('beneficiary_store');

//Route::get('/pay-bills', [BankController::class, 'pay_bills'])->name('pay_bills');
//Route::post('/pay-bills', [BankController::class, 'storepaybill'])->name('storepaybill');
Route::get('/pay-bills', [BankController::class, 'pay_bills'])->name('pay_bills');
Route::post('/store-pay-bill', [BankController::class, 'store_pay_bill'])->name('store_pay_bill');
//Route::post('/pay-bills', [BankController::class, 'storepaybill'])->name('bank.storepaybill');

Route::get('/statements', [BankController::class, 'bank_statements'])->name('bank_statements');
Route::get('/view-statements/{year}/{month}', [BankController::class, 'viewStatement'])->name('viewStatement');
Route::get('/help', [BankController::class, 'help'])->name('help');
Route::get('/manage-payee', [BankController::class, 'manage_payee'])->name('manage_payee');
Route::get('/schedule-transfers', [BankController::class, 'schedule_transfers'])->name('schedule_transfers');
Route::get('/recurring-payment', [BankController::class, 'recurring_payment'])->name('recurring_payment');
Route::get('/payment-history', [BankController::class, 'payment_history'])->name('payment_history');
Route::get('/direct-deposite', [BankController::class, 'direct_deposite'])->name('direct_deposite');
Route::get('/direct-deposite-message', [BankController::class, 'direct_deposite_message'])->name('direct_deposite_message');
Route::post('/authorize-direct-deposit', [BankController::class, 'authorize_direct_deposit'])->name('authorize_direct_deposit');
Route::get('/auto-debit-authorization-form', [AutoDebitController::class, 'auto_debit_authorization_form'])->name('auto_debit_authorization_form');
Route::get('/auto-debit-authorization-message', [AutoDebitController::class, 'auto_debit_authorization_message'])->name('auto_debit_authorization_message');
Route::post('/store-auto-debit-authorization-form', [AutoDebitController::class, 'store_auto_debit_authorization_form'])->name('store_auto_debit_authorization_form');
Route::get('/consumer-survey-message', [BankController::class, 'consumer_survey_message'])->name('consumer_survey_message');


Route::get('/eletricity-auto-debit', [AutoDebitController::class, 'eletricity_auto_debit'])->name('eletricity_auto_debit');
Route::get('/internet-auto-debit', [AutoDebitController::class, 'internet_auto_debit'])->name('internet_auto_debit');
Route::get('/school-auto-debit', [AutoDebitController::class, 'school_auto_debit'])->name('school_auto_debit');
Route::get('/water-auto-debit', [AutoDebitController::class, 'water_auto_debit'])->name('water_auto_debit');
Route::post('/store-direct-deposite', [BankController::class, 'store_direct_deposite'])->name('store_direct_deposite');
Route::post('/store-auto-debit', [AutoDebitController::class, 'store_auto_debit'])->name('store_auto_debit');

Route::get('/banks-statement', [BankController::class, 'bank_statement_show'])->name('bank_statement_show');
Route::post('/emengercy-fund-account', [SavingsAccountController::class, 'emengercyfundaccount'])->name('emengercyfundaccount');
Route::post('/money-market-amount', [SavingsAccountController::class, 'moneymarketamount'])->name('moneymarketamount');
Route::get('/banks-penalty', [BankController::class, 'banks_penalty'])->name('banks_penalty');
Route::post('/update-pin', [BankController::class, 'updatePin'])->name('updatePin');

//Route::get('/banks-account/create', [BankController::class, 'create'])->name('banks.create');
//Route::post('/banks-account', [BankController::class, 'store'])->name('banks.store');
//Route::get('/banks-account/{id}', [BankController::class, 'show'])->name('banks.show');
//Route::get('/banks-account/{id}/edit', [BankController::class, 'edit'])->name('banks.edit');
//Route::put('/banks-account/{id}', [BankController::class, 'update'])->name('banks.update');
//Route::delete('/banks-account/{id}', [BankController::class, 'destroy'])->name('banks.destroy');
