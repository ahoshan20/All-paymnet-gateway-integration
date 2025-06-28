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

    /**
     * Create or get existing customer
     */
    public function createOrGetCustomer(array $customerData): array
    {
        try {
            // Check if customer already exists in our database
            $existingCustomer = Customer::findByEmail($customerData['email']);
            
            if ($existingCustomer) {
                // Verify customer still exists in Stripe
                try {
                    $stripeCustomer = StripeCustomer::retrieve($existingCustomer->stripe_customer_id);
                    return [
                        'customer' => $existingCustomer,
                        'stripe_customer' => $stripeCustomer
                    ];
                } catch (ApiErrorException $e) {
                    // Customer doesn't exist in Stripe anymore, create new one
                    $existingCustomer->delete();
                }
            }

            // Create new customer in Stripe
            $stripeCustomer = StripeCustomer::create([
                'email' => $customerData['email'],
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'address' => $customerData['address'] ?? null,
                'metadata' => $customerData['metadata'] ?? [],
            ]);

            // Save customer in our database
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

    /**
     * Create payment intent with customer
     */
    public function createPaymentIntentWithCustomer(array $data): array
    {
        try {
            // Create or get customer
            $customerResult = $this->createOrGetCustomer($data['customer']);
            $customer = $customerResult['customer'];
            $stripeCustomer = $customerResult['stripe_customer'];

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'] * 100, // Convert to cents
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

    /**
     * Get customer's payment methods
     */
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

    /**
     * Update customer information
     */
    public function updateCustomer(string $stripeCustomerId, array $data): \Stripe\Customer
    {
        try {
            return StripeCustomer::update($stripeCustomerId, $data);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Delete customer
     */
    public function deleteCustomer(string $stripeCustomerId): \Stripe\Customer
    {
        try {
            // return StripeCustomer::delete($stripeCustomerId);
            return StripeCustomer::retrieve($stripeCustomerId)->delete();
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to delete customer: ' . $e->getMessage());
        }
    }
}
