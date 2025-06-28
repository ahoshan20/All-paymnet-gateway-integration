<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Services\API\GooglePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GooglePayController extends Controller
{
    protected GooglePayService $googlePayService;

    public function __construct(GooglePayService $googlePayService)
    {
        $this->googlePayService = $googlePayService;
    }

    /**
     * Show Google Pay checkout page
     */
    public function checkout(Request $request)
    {
        $amount = $request->get('amount', 10.00);
        $currency = $request->get('currency', 'USD');

        $paymentConfig = $this->googlePayService->getPaymentDataRequest($amount, $currency);

        return view('gpay.checkout', compact('paymentConfig', 'amount', 'currency'));
    }

    /**
     * Get payment configuration for AJAX requests
     */
    public function getPaymentConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'string|size:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        $amount = $request->input('amount');
        $currency = $request->input('currency', 'USD');

        $config = $this->googlePayService->getPaymentDataRequest($amount, $currency);

        return response()->json([
            'success' => true,
            'config' => $config,
        ]);
    }

    /**
     * Process Google Pay payment
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_token' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'string|size:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $result = $this->googlePayService->processPayment(
                $request->input('payment_token'),
                $request->input('amount'),
                $request->input('currency', 'USD')
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_intent_id' => $result['payment_intent_id'],
                    'redirect_url' => route('payment.success'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed: ' . $result['error'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Payment success page
     */
    public function success()
    {
        return view('gpay.success');
    }

    /**
     * Payment failure page
     */
    public function failure()
    {
        return view('googlepay.failure');
    }
}
