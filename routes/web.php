<?php

use App\Http\Controllers\StripePaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/checkout', [StripePaymentController::class, 'showForm'])->name('stripe.form');
Route::post('/charge', [StripePaymentController::class, 'charge'])->name('stripe.charge');
