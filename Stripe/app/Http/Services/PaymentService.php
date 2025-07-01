<?php

namespace App\Http\Services;

use App\Models\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createOrGetCustomer(array $customerData): array
    {
        try {
            $existingCustomer = Customer::findByEmail($customerData['email']);
            
            if ($existingCustomer) {
                try {
                    $stripeCustomer = StripeCustomer::retrieve($existingCustomer->stripe_customer_id);
                    return [
                        'customer' => $existingCustomer,
                        'stripe_customer' => $stripeCustomer
                    ];
                } catch (ApiErrorException $e) {
                    $existingCustomer->delete();
                }
            }

            $stripeCustomer = StripeCustomer::create([
                'email' => $customerData['email'],
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'address' => $customerData['address'] ?? null,
                'metadata' => $customerData['metadata'] ?? [],
            ]);

            $customer = Customer::create([
                'stripe_customer_id' => $stripeCustomer->id,
                'name' => $stripeCustomer->name,
                'email' => $stripeCustomer->email,
                'phone' => $stripeCustomer->phone,
                'address' => $stripeCustomer->address ? $stripeCustomer->address->toArray() : null,
                'metadata' => $stripeCustomer->metadata->toArray(),
                'created_at_stripe' => now(),
            ]);

            return [
                'customer' => $customer,
                'stripe_customer' => $stripeCustomer
            ];

        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to create customer: ' . $e->getMessage());
        }
    }

    public function createPaymentIntentWithCustomer(array $data): array
    {
        try {
            $customerResult = $this->createOrGetCustomer($data['customer']);
            $customer = $customerResult['customer'];
            $stripeCustomer = $customerResult['stripe_customer'];

            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'] * 100,
                'currency' => $data['currency'] ?? 'usd',
                'customer' => $stripeCustomer->id,
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                ]),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'setup_future_usage' => $data['save_payment_method'] ?? false ? 'on_session' : null,
            ]);

            return [
                'payment_intent' => $paymentIntent,
                'customer' => $customer,
            ];

        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    public function retrievePaymentIntent(string $paymentIntentId)
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to retrieve payment intent: ' . $e->getMessage());
        }
    }

    public function getCustomerPaymentMethods(string $stripeCustomerId): array
    {
        try {
            $paymentMethods = \Stripe\PaymentMethod::all([
                'customer' => $stripeCustomerId,
                'type' => 'card',
            ]);

            return $paymentMethods->data;
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to retrieve payment methods: ' . $e->getMessage());
        }
    }

    public function updateCustomer(string $stripeCustomerId, array $data): \Stripe\Customer
    {
        try {
            return StripeCustomer::update($stripeCustomerId, $data);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to update customer: ' . $e->getMessage());
        }
    }
}
