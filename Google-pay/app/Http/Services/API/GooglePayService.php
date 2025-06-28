<?php

namespace App\Http\Services\API;

use Illuminate\Support\Facades\Log;

class GooglePayService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('googlepay');
    }

    /**
     * Get Google Pay configuration for frontend
     */
    public function getPaymentDataRequest($amount, $currency = 'USD')
    {
        return [
            'apiVersion' => 2,
            'apiVersionMinor' => 0,
            'allowedPaymentMethods' => [
                [
                    'type' => 'CARD',
                    'parameters' => [
                        'allowedAuthMethods' => $this->config['allowed_auth_methods'],
                        'allowedCardNetworks' => $this->config['allowed_card_networks'],
                    ],
                    'tokenizationSpecification' => [
                        'type' => 'PAYMENT_GATEWAY',
                        'parameters' => [
                            'gateway' => $this->config['gateway'],
                            'gatewayMerchantId' => $this->config['gateway_merchant_id'],
                        ],
                    ],
                ],
            ],
            'merchantInfo' => [
                'merchantId' => $this->config['merchant_id'],
                'merchantName' => $this->config['merchant_name'],
            ],
            'transactionInfo' => [
                'totalPriceStatus' => 'FINAL',
                'totalPriceLabel' => 'Total',
                'totalPrice' => number_format($amount, 2, '.', ''),
                'currencyCode' => $currency,
                'countryCode' => 'US',
            ],
            'emailRequired' => true,
            'shippingAddressRequired' => false,
        ];
    }

    /**
     * Process Google Pay payment token
     */
    public function processPayment($paymentToken, $amount, $currency = 'USD')
    {
        try {
            // Decode the payment token
            $paymentData = json_decode($paymentToken, true);
            
            if (!$paymentData) {
                throw new \Exception('Invalid payment token');
            }

            // Extract payment method data
            $paymentMethodData = $paymentData['paymentMethodData'];
            $tokenizationData = $paymentMethodData['tokenizationData'];
            
            // Process with your payment gateway (example with Stripe)
            return $this->processWithStripe($tokenizationData, $amount, $currency);
            
        } catch (\Exception $e) {
            Log::error('Google Pay processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process payment with Stripe (example)
     */
    private function processWithStripe($tokenizationData, $amount, $currency)
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        
        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => [
                        'token' => $tokenizationData['token'],
                    ],
                ],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('payment.success'),
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
            ];
            
        } catch (\Exception $e) {
            Log::error('Stripe processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
