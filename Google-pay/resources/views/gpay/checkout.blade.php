<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Google Pay Checkout</title>
    <script src="https://pay.google.com/gp/p/js/pay.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .checkout-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            background: #f9f9f9;
        }
        .amount-display {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        #google-pay-button {
            margin: 20px auto;
            display: block;
        }
        .loading {
            text-align: center;
            margin: 20px 0;
        }
        .error {
            color: red;
            margin: 10px 0;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 4px;
        }
        .success {
            color: green;
            margin: 10px 0;
            padding: 10px;
            background: #e6ffe6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1>Google Pay Checkout</h1>
        
        <div class="amount-display">
            Total: {{ $currency }} {{ number_format($amount, 2) }}
        </div>

        <div id="error-message" class="error" style="display: none;"></div>
        <div id="success-message" class="success" style="display: none;"></div>
        <div id="loading" class="loading" style="display: none;">Processing payment...</div>

        <div id="google-pay-button"></div>
    </div>

    <script>
        let paymentsClient;
        let paymentConfig = @json($paymentConfig);

        // Initialize Google Pay
        function initializeGooglePay() {
            paymentsClient = new google.payments.api.PaymentsClient({
                environment: '{{ config("googlepay.environment") }}'
            });

            // Check if Google Pay is available
            paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest())
                .then(function(response) {
                    if (response.result) {
                        addGooglePayButton();
                    } else {
                        showError('Google Pay is not available');
                    }
                })
                .catch(function(err) {
                    showError('Error checking Google Pay availability: ' + err);
                });
        }

        function getGoogleIsReadyToPayRequest() {
            return Object.assign({}, {
                apiVersion: paymentConfig.apiVersion,
                apiVersionMinor: paymentConfig.apiVersionMinor,
                allowedPaymentMethods: paymentConfig.allowedPaymentMethods
            });
        }

        function addGooglePayButton() {
            const button = paymentsClient.createButton({
                onClick: onGooglePaymentButtonClicked,
                allowedPaymentMethods: paymentConfig.allowedPaymentMethods,
                buttonColor: 'black',
                buttonType: 'buy'
            });
            
            document.getElementById('google-pay-button').appendChild(button);
        }

        function onGooglePaymentButtonClicked() {
            hideMessages();
            showLoading(true);

            paymentsClient.loadPaymentData(paymentConfig)
                .then(function(paymentData) {
                    processPayment(paymentData);
                })
                .catch(function(err) {
                    showLoading(false);
                    if (err.statusCode === 'CANCELED') {
                        showError('Payment was canceled');
                    } else {
                        showError('Payment failed: ' + err.statusMessage);
                    }
                });
        }

        function processPayment(paymentData) {
            fetch('{{ route("googlepay.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    payment_token: JSON.stringify(paymentData),
                    amount: {{ $amount }},
                    currency: '{{ $currency }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    showSuccess('Payment successful! Redirecting...');
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 2000);
                } else {
                    showError(data.message || 'Payment failed');
                }
            })
            .catch(error => {
                showLoading(false);
                showError('Network error: ' + error.message);
            });
        }

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('success-message');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
        }

        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        function hideMessages() {
            document.getElementById('error-message').style.display = 'none';
            document.getElementById('success-message').style.display = 'none';
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeGooglePay();
        });
    </script>
</body>
</html>
