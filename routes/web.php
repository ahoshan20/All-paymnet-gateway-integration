<?php

use App\Http\Controllers\StripePaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/checkout', [StripePaymentController::class, 'showForm'])->name('stripe.form');
// Route::post('/charge', [StripePaymentController::class, 'charge'])->name('stripe.charge');
// Payment routes
Route::get('/payment', [StripePaymentController::class, 'showPaymentForm'])->name('payment.form');
Route::post('/payment/create-intent', [StripePaymentController::class, 'createPaymentIntent'])->name('payment.create-intent');
Route::get('/payment/success', [StripePaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancel', [StripePaymentController::class, 'paymentCancel'])->name('payment.cancel');

// Webhook route (should be in api.php for production)
Route::post('/stripe/webhook', [StripePaymentController::class, 'handleWebhook'])->name('stripe.webhook');
