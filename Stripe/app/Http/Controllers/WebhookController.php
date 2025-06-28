<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook.secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event['data']['object']);
                break;
                
            case 'customer.created':
                $this->handleCustomerCreated($event['data']['object']);
                break;
                
            case 'customer.updated':
                $this->handleCustomerUpdated($event['data']['object']);
                break;
                
            case 'customer.deleted':
                $this->handleCustomerDeleted($event['data']['object']);
                break;
                
            case 'payment_method.attached':
                $this->handlePaymentMethodAttached($event['data']['object']);
                break;
        }

        return response('Webhook handled', 200);
    }

    private function handlePaymentSucceeded($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        
        if ($payment) {
            $payment->update([
                'status' => 'succeeded',
                'payment_method' => $paymentIntent['payment_method'] ?? null,
                'paid_at' => now(),
            ]);

            // Update customer's last payment date
            if ($payment->customer) {
                $payment->customer->update([
                    'metadata' => array_merge($payment->customer->metadata ?? [], [
                        'last_payment_at' => now()->toISOString(),
                        'total_payments' => $payment->customer->payment_count,
                    ])
                ]);
            }

            Log::info('Payment succeeded for customer', [
                'payment_id' => $payment->id,
                'customer_id' => $payment->customer_id,
                'amount' => $payment->amount,
            ]);
        }
    }

    private function handleCustomerCreated($customer)
    {
        Log::info('Customer created in Stripe', [
            'stripe_customer_id' => $customer['id'],
            'email' => $customer['email'],
        ]);
    }

    private function handleCustomerUpdated($customer)
    {
        $localCustomer = Customer::findByStripeId($customer['id']);
        
        if ($localCustomer) {
            $localCustomer->update([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'address' => $customer['address'] ? (array) $customer['address'] : null,
            ]);

            Log::info('Customer updated', [
                'customer_id' => $localCustomer->id,
                'stripe_customer_id' => $customer['id'],
            ]);
        }
    }

    private function handleCustomerDeleted($customer)
    {
        $localCustomer = Customer::findByStripeId($customer['id']);
        
        if ($localCustomer) {
            // Don't delete the customer, just mark as deleted in Stripe
            $localCustomer->update([
                'metadata' => array_merge($localCustomer->metadata ?? [], [
                    'deleted_in_stripe' => true,
                    'deleted_at_stripe' => now()->toISOString(),
                ])
            ]);

            Log::info('Customer deleted in Stripe', [
                'customer_id' => $localCustomer->id,
                'stripe_customer_id' => $customer['id'],
            ]);
        }
    }

    private function handlePaymentMethodAttached($paymentMethod)
    {
        Log::info('Payment method attached to customer', [
            'payment_method_id' => $paymentMethod['id'],
            'customer_id' => $paymentMethod['customer'],
            'type' => $paymentMethod['type'],
        ]);
    }
}
