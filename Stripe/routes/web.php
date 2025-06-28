<?php

use App\Http\Controllers\API\StripePaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/payment', [StripePaymentController::class, 'showPaymentForm'])->name('payment.form');
// Route::post('/payment/create-intent', [StripePaymentController::class, 'createPaymentIntent'])->name('payment.create-intent');
// Route::get('/payment/success', [StripePaymentController::class, 'paymentSuccess'])->name('payment.success');
// Route::get('/payment/cancel', [StripePaymentController::class, 'paymentCancel'])->name('payment.cancel');

// // Webhook route (should be in api.php for production)
// Route::post('/stripe/webhook', [StripePaymentController::class, 'handleWebhook'])->name('stripe.webhook');

// Payment routes
Route::get('/payment', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
Route::post('/payment/create-intent', [PaymentController::class, 'createPaymentIntent'])->name('payment.create-intent');
Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');

// Customer management routes
Route::get('/customer/payments', [PaymentController::class, 'getCustomerPayments'])->name('customer.payments');
Route::get('/customer/payment-methods', [PaymentController::class, 'getCustomerPaymentMethods'])->name('customer.payment-methods');
Route::put('/customer/{customer}', [PaymentController::class, 'updateCustomer'])->name('customer.update');

// Webhook route
Route::post('/stripe/webhook', [WebhookController::class, 'handle'])->name('stripe.webhook');
