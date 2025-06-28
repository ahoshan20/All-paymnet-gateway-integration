@extends('layouts.app')

@section('content')
    <div class="success-container">
        <!-- Confetti Animation -->
        <div class="confetti" style="left: 10%; animation-delay: 0s;"></div>
        <div class="confetti" style="left: 20%; animation-delay: 0.5s; background: #ff6b6b;"></div>
        <div class="confetti" style="left: 30%; animation-delay: 1s; background: #4ecdc4;"></div>
        <div class="confetti" style="left: 40%; animation-delay: 1.5s; background: #45b7d1;"></div>
        <div class="confetti" style="left: 50%; animation-delay: 2s; background: #f9ca24;"></div>
        <div class="confetti" style="left: 60%; animation-delay: 0.3s; background: #6c5ce7;"></div>
        <div class="confetti" style="left: 70%; animation-delay: 0.8s; background: #a29bfe;"></div>
        <div class="confetti" style="left: 80%; animation-delay: 1.3s; background: #fd79a8;"></div>
        <div class="confetti" style="left: 90%; animation-delay: 1.8s; background: #00b894;"></div>

        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-subtitle">
            Thank you for your payment. Your transaction has been completed successfully.
        </p>

        @if (isset($payment))
            <div class="payment-details">
                <h3>
                    <i class="fas fa-receipt"></i>
                    Payment Details
                </h3>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-dollar-sign"></i>
                        Amount
                    </span>
                    <span class="detail-value amount-highlight">
                        ${{ number_format($payment->amount, 2) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-coins"></i>
                        Currency
                    </span>
                    <span class="detail-value">
                        {{ strtoupper($payment->currency) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-info-circle"></i>
                        Status
                    </span>
                    <span class="status-badge">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-hashtag"></i>
                        Transaction ID
                    </span>
                    <span class="detail-value" style="font-size: 12px; font-family: monospace;">
                        {{ substr($payment->stripe_payment_intent_id, 0, 20) }}...
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar-alt"></i>
                        Date & Time
                    </span>
                    <span class="detail-value">
                        {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}
                        <br>
                        <small style="color: #718096;">
                            {{ $payment->paid_at ? $payment->paid_at->format('h:i A') : '' }}
                        </small>
                    </span>
                </div>
            </div>
        @endif

        <div class="email-notice">
            <i class="fas fa-envelope"></i>
            <p>A confirmation email has been sent to your registered email address with the payment receipt.</p>
        </div>

        <div class="action-buttons">
            <a href="{{ route('payment.form') }}" class="btn btn-secondary">
                <i class="fas fa-plus"></i>
                New Payment
            </a>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
    </div>
@endsection
