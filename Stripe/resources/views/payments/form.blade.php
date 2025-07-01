<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Payment - Stripe Gateway</title>
    <script src="https://js.stripe.com/basil/stripe.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .payment-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .payment-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="50" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="30" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }

        .payment-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .payment-header p {
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            position: relative;
            z-index: 1;
        }

        .security-badge i {
            color: #00d4aa;
        }

        .payment-form {
            padding: 40px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .amount-input {
            position: relative;
        }

        .amount-input::before {
            content: '$';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            color: #4a5568;
            z-index: 1;
        }

        .amount-input input {
            padding-left: 35px;
            font-weight: bold;
            font-size: 18px;
        }

        .card-section {
            background: #f7fafc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .card-section:focus-within {
            border-color: #667eea;
            background: white;
        }

        .card-section h3 {
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #card-element {
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }

        #card-element:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        #card-errors {
            color: #e53e3e;
            margin-top: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            padding: 15px;
            background: #ebf8ff;
            border-radius: 10px;
            border: 1px solid #bee3f8;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .checkbox-group label {
            margin: 0;
            color: #2c5282;
            font-size: 14px;
            cursor: pointer;
        }

        .submit-button {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .submit-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .button-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #e53e3e;
        }

        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #38a169;
        }

        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            opacity: 0.7;
        }

        .payment-methods img {
            height: 30px;
            filter: grayscale(1);
            transition: all 0.3s ease;
        }

        .payment-methods img:hover {
            filter: grayscale(0);
        }

        .security-info {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .security-info p {
            color: #22543d;
            font-size: 13px;
            margin: 0;
        }

        @media (max-width: 480px) {
            .payment-container {
                margin: 10px;
            }
            
            .payment-form {
                padding: 30px 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .payment-header {
                padding: 25px 20px;
            }
        }

        /* Custom Stripe Element Styling */
        .StripeElement {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
        }

        .StripeElement--focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .StripeElement--invalid {
            border-color: #e53e3e;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h1><i class="fas fa-credit-card"></i> Secure Payment</h1>
            <p>Complete your transaction safely and securely</p>
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>256-bit SSL Encrypted</span>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="payment-form">
            <!-- Error/Success Messages -->
            @if(session('error'))
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form id="payment-form">
                <!-- Customer Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_name">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" id="customer_name" name="customer_name" required 
                               placeholder="Enter your full name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" id="customer_email" name="customer_email" required 
                               placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">
                            <i class="fas fa-phone"></i> Phone (Optional)
                        </label>
                        <input type="tel" id="customer_phone" name="customer_phone" 
                               placeholder="+1 (555) 123-4567">
                    </div>
                </div>

                <!-- Amount Section -->
                <div class="form-group">
                    <label for="amount">
                        <i class="fas fa-dollar-sign"></i> Payment Amount
                    </label>
                    <div class="amount-input">
                        <input type="number" id="amount" name="amount" step="0.01" min="0.50" 
                               value="10.00" required placeholder="0.00">
                    </div>
                </div>

                <!-- Currency Selection -->
                <div class="form-group">
                    <label for="currency">
                        <i class="fas fa-coins"></i> Currency
                    </label>
                    <select id="currency" name="currency">
                        <option value="usd">USD - US Dollar</option>
                        <option value="eur">EUR - Euro</option>
                        <option value="gbp">GBP - British Pound</option>
                        <option value="cad">CAD - Canadian Dollar</option>
                        <option value="aud">AUD - Australian Dollar</option>
                    </select>
                </div>

                <!-- Card Information -->
                <div class="card-section">
                    <h3>
                        <i class="fas fa-credit-card"></i>
                        Payment Information
                    </h3>
                    <div id="card-element">
                        <!-- Stripe Elements will create form elements here -->
                    </div>
                    <div id="card-errors" role="alert"></div>
                </div>

                <!-- Save Payment Method -->
                <div class="checkbox-group">
                    <input type="checkbox" id="save_payment_method" name="save_payment_method">
                    <label for="save_payment_method">
                        <i class="fas fa-bookmark"></i>
                        Save this payment method for future purchases
                    </label>
                </div>

                <!-- Order Summary (Optional) -->
                <div class="form-group">
                    <label for="order_notes">
                        <i class="fas fa-sticky-note"></i> Order Notes (Optional)
                    </label>
                    <input type="text" id="order_notes" name="order_notes" 
                           placeholder="Any special instructions...">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-button" class="submit-button">
                    <div class="button-content">
                        <div class="loading-spinner" id="loading-spinner"></div>
                        <span id="button-text">
                            <i class="fas fa-lock"></i>
                            Complete Secure Payment
                        </span>
                    </div>
                </button>

                <!-- Payment Methods -->
                <div class="payment-methods">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo.svg" alt="American Express">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Google_Pay_Logo.svg" alt="Google Pay">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b0/Apple_Pay_logo.svg" alt="Apple Pay">
                </div>

                <!-- Security Information -->
                <div class="security-info">
                    <p>
                        <i class="fas fa-shield-alt"></i>
                        Your payment information is encrypted and secure. We never store your card details.
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Stripe
        const stripe = Stripe('{{ config("services.stripe.key") }}');
        const elements = stripe.elements();

        // Custom styling for Stripe Elements
        const style = {
            base: {
                fontSize: '16px',
                color: '#2d3748',
                fontFamily: '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
                '::placeholder': {
                    color: '#a0aec0',
                },
                padding: '15px',
            },
            invalid: {
                color: '#e53e3e',
                iconColor: '#e53e3e'
            }
        };

        // Create card element
        const cardElement = elements.create('card', {
            style: style,
            hidePostalCode: false
        });
        cardElement.mount('#card-element');

        // Handle real-time validation errors from the card Element
        cardElement.on('change', ({error}) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error.message}`;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const loadingSpinner = document.getElementById('loading-spinner');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Disable submit button and show loading
            setLoadingState(true);

            const formData = new FormData(form);
            
            try {
                // Validate form
                if (!validateForm(formData)) {
                    setLoadingState(false);
                    return;
                }

                // Create payment intent
                const response = await fetch('{{ route("payment.create-intent") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        amount: parseFloat(formData.get('amount')),
                        currency: formData.get('currency'),
                        customer_name: formData.get('customer_name'),
                        customer_email: formData.get('customer_email'),
                        customer_phone: formData.get('customer_phone'),
                        save_payment_method: formData.get('save_payment_method') === 'on',
                        order_notes: formData.get('order_notes')
                    })
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Confirm payment with Stripe
                const {error, paymentIntent} = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: formData.get('customer_name'),
                            email: formData.get('customer_email'),
                            phone: formData.get('customer_phone')
                        }
                    }
                });

                if (error) {
                    // Show error to customer
                    showError(error.message);
                } else {
                    // Payment succeeded, redirect to success page
                    window.location.href = '{{ route("payment.success") }}?payment_intent_id=' + data.payment_intent_id;
                }
            } catch (error) {
                showError(error.message);
            }

            setLoadingState(false);
        });

        // Helper functions
        function setLoadingState(loading) {
            submitButton.disabled = loading;
            if (loading) {
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'block';
            } else {
                buttonText.style.display = 'flex';
                loadingSpinner.style.display = 'none';
            }
        }

        function validateForm(formData) {
            const requiredFields = ['customer_name', 'customer_email', 'amount'];
            
            for (let field of requiredFields) {
                if (!formData.get(field) || formData.get(field).trim() === '') {
                    showError(`Please fill in the ${field.replace('_', ' ')} field.`);
                    return false;
                }
            }

            const amount = parseFloat(formData.get('amount'));
            if (amount < 0.50) {
                showError('Minimum payment amount is $0.50');
                return false;
            }

            const email = formData.get('customer_email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address.');
                return false;
            }

            return true;
        }

        function showError(message) {
            const errorDiv = document.getElementById('card-errors');
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Format amount input
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });

        // Auto-format phone number
        document.getElementById('customer_phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
            }
            e.target.value = value;
        });

        // Real-time email validation
        document.getElementById('customer_email').addEventListener('blur', function(e) {
            const email = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                e.target.style.borderColor = '#e53e3e';
                showError('Please enter a valid email address.');
            } else {
                e.target.style.borderColor = '#e2e8f0';
                document.getElementById('card-errors').textContent = '';
            }
        });
    </script>
</body>
</html>