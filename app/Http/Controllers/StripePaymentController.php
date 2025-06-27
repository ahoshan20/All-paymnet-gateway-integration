<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    public function showForm()
    {
        return view('stripe.checkout');
    }

    public function charge(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Create a PaymentIntent (recommended way)
            $paymentIntent = PaymentIntent::create([
                'amount' => 5000, // amount in cents = $50
                'currency' => 'usd',
                'payment_method' => $request->payment_method,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            if ($paymentIntent->status == 'succeeded') {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'error' => 'Payment did not succeed.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // public function showSubscriptionForm() { return view('subscribe'); }

    // public function subscribe(Request $request) {
    //     try {
    //         Stripe::setApiKey(config('services.stripe.secret'));
    //         $customer = Customer::create([
    //             'email' => $request->email,
    //             'payment_method' => $request->payment_method,
    //             'invoice_settings' => ['default_payment_method' => $request->payment_method],
    //         ]);
    //         $priceId = 'price_XXXXXXXXXXXX'; // replace with your real Price ID
    //         $subscription = Subscription::create([
    //             'customer' => $customer->id,
    //             'items' => [['price' => $priceId]],
    //             'expand' => ['latest_invoice.payment_intent'],
    //         ]);
    //         StripeCustomer::create([
    //             'user_id' => auth()->id(),
    //             'stripe_customer_id' => $customer->id,
    //             'stripe_subscription_id' => $subscription->id,
    //         ]);
    //         return response()->json(['success' => true, 'subscription_id' => $subscription->id]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'error' => $e->getMessage()]);
    //     }
    // }

    // public function refund($paymentIntentId) {
    //     try {
    //         Stripe::setApiKey(config('services.stripe.secret'));
    //         Refund::create(['payment_intent' => $paymentIntentId]);
    //         return response()->json(['success' => true, 'message' => 'Refund processed.']);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'error' => $e->getMessage()]);
    //     }
    // }

    // public function billingPortal(Request $request) {
    //     Stripe::setApiKey(config('services.stripe.secret'));
    //     $stripeCustomer = StripeCustomer::where('user_id', auth()->id())->firstOrFail();
    //     $session = \Stripe\BillingPortal\Session::create([
    //         'customer' => $stripeCustomer->stripe_customer_id,
    //         'return_url' => route('dashboard'),
    //     ]);
    //     return redirect($session->url);
    // }

    // public function handleWebhook(Request $request) {
    //     $payload = @file_get_contents('php://input');
    //     $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    //     $endpoint_secret = 'whsec_XXXXXXXXXXXX'; // replace with your endpoint secret
    //     try {
    //         $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Invalid webhook signature'], 400);
    //     }
    //     switch ($event->type) {
    //         case 'invoice.payment_succeeded':
    //             $invoice = $event->data->object;
    //             $stripeCustomer = StripeCustomer::where('stripe_customer_id', $invoice->customer)->first();
    //             if ($stripeCustomer) {
    //                 $invoiceUrl = $invoice->hosted_invoice_url;
    //                 $amountPaid = $invoice->amount_paid / 100;
    //                 \Mail::to($invoice->customer_email ?? 'default@example.com')
    //                     ->send(new StripePaymentReceiptMail($invoiceUrl, $amountPaid));
    //             }
    //             break;
    //         case 'invoice.payment_failed':
    //             // handle payment failure: notify customer, suspend service, etc.
    //             break;
    //         default:
    //             \Log::info('Unhandled Stripe event: ' . $event->type);
    //     }
    //     return response()->json(['status' => 'success']);
    // }
}
