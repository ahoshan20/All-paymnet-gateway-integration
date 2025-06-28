<?php

use App\Http\Controllers\API\GooglePayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Google Pay routes
Route::prefix('googlepay')->name('googlepay.')->group(function () {
    Route::get('/checkout', [GooglePayController::class, 'checkout'])->name('checkout');
    Route::post('/config', [GooglePayController::class, 'getPaymentConfig'])->name('config');
    Route::post('/process', [GooglePayController::class, 'processPayment'])->name('process');
    Route::get('/success', [GooglePayController::class, 'success'])->name('success');
    Route::get('/failure', [GooglePayController::class, 'failure'])->name('failure');
});
