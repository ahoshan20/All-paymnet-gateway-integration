@extends('layouts.app')

@section('content')
    <div class="checkout-container">
        <h1 class="text-center">Payment Form</h1>

        @if (session('error'))
            <div style="color: red; margin-bottom: 20px;">
                {{ session('error') }}
            </div>
        @endif

        <form id="payment-form">
            <div class="form-group">
                <label for="amount">Amount ($)</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.50" value="99.00" required>
            </div>

            <div class="form-group">
                <label for="customer_email">Email</label>
                <input type="email" id="customer_email" name="customer_email" required>
            </div>

            <div class="form-group">
                <label for="card-element">Credit or debit card</label>
                <div id="card-element" class="!text-white">
                    <!-- Stripe Elements will create form elements here -->
                </div>
                <div id="card-errors" role="alert"></div>
            </div>

            <button type="submit" id="submit-button">
                <span id="button-text">Pay Now</span>
                <span class="loading" id="loading">Processing...</span>
            </button>
        </form>
    </div>
@endsection
