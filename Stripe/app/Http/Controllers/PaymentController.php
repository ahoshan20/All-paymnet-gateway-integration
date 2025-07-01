<?php

namespace App\Http\Controllers;

use App\Http\Services\PaymentService;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $stripeService;

    public function __construct(PaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function showPaymentForm()
    {
        return view('payments.form');
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.50',
            'currency' => 'sometimes|string|size:3',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'sometimes|string',
            'save_payment_method' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();

        try {
            $customerData = [
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'phone' => $request->customer_phone,
                'metadata' => [
                    'source' => 'payment_form',
                    'ip_address' => $request->ip(),
                ],
            ];

            $result = $this->stripeService->createPaymentIntentWithCustomer([
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'usd',
                'customer' => $customerData,
                'save_payment_method' => $request->save_payment_method ?? false,
                'metadata' => [
                    'order_id' => $request->order_id ?? null,
                ],
            ]);

            $paymentIntent = $result['payment_intent'];
            $customer = $result['customer'];

            $payment = Payment::create([
                'customer_id' => $customer->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_customer_id' => $customer->stripe_customer_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'usd',
                'status' => $paymentIntent->status,
                'metadata' => $paymentIntent->metadata->toArray(),
            ]);

            DB::commit();

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment intent creation failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create payment intent'
            ], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = $this->stripeService->retrievePaymentIntent($request->payment_intent_id);

            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

            if ($payment) {
                $payment->update([
                    'status' => $paymentIntent->status,
                    'payment_method' => $paymentIntent->payment_method ?? null,
                    'paid_at' => $paymentIntent->status === 'succeeded' ? now() : null,
                ]);
            }

            return view('payments.success', compact('payment', 'paymentIntent'));

        } catch (\Exception $e) {
            Log::error('Payment success handling failed: ' . $e->getMessage());
            return redirect()->route('payment.form')->with('error', 'Payment verification failed');
        }
    }

    public function paymentCancel()
    {
        return view('payments.cancel');
    }

    public function getCustomerPayments(Request $request)
    {
        $request->validate([
            'customer_email' => 'required|email',
        ]);

        $customer = Customer::findByEmail($request->customer_email);

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found'
            ], 404);
        }

        $payments = $customer->payments()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'customer' => $customer,
            'payments' => $payments,
            'total_paid' => $customer->total_paid,
            'payment_count' => $customer->payment_count,
        ]);
    }

    public function getCustomerPaymentMethods(Request $request)
    {
        $request->validate([
            'customer_email' => 'required|email',
        ]);

        try {
            $customer = Customer::findByEmail($request->customer_email);

            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $paymentMethods = $this->stripeService->getCustomerPaymentMethods(
                $customer->stripe_customer_id
            );

            return response()->json([
                'customer' => $customer,
                'payment_methods' => $paymentMethods,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get payment methods: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve payment methods'], 500);
        }
    }

    public function updateCustomer(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,' . $customer->id,
            'phone' => 'sometimes|string',
        ]);

        DB::beginTransaction();

        try {
            $stripeCustomer = $this->stripeService->updateCustomer(
                $customer->stripe_customer_id,
                $request->only(['name', 'email', 'phone'])
            );

            $customer->update([
                'name' => $stripeCustomer->name,
                'email' => $stripeCustomer->email,
                'phone' => $stripeCustomer->phone,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Customer updated successfully',
                'customer' => $customer->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer update failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update customer'
            ], 500);
        }
    }
}