<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Stripe payment gateway</title>
    <script src="https://js.stripe.com/basil/stripe.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
    
	@yield('content')


    {{-- Checkout form --}}
    <script>
        const stripe = Stripe('{{ config('services.stripe.key') }}');
        const elements = stripe.elements();

        // Create card element
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        // Handle real-time validation errors from the card Element
        cardElement.on('change', ({
            error
        }) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const loading = document.getElementById('loading');

            // Disable submit button and show loading
            submitButton.disabled = true;
            buttonText.style.display = 'none';
            loading.style.display = 'inline';

            const formData = new FormData(form);

            try {
                // Create payment intent
                const response = await fetch('{{ route('payment.create-intent') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        amount: formData.get('amount'),
                        customer_email: formData.get('customer_email')
                    })
                });

                const data = await response.json();
                console.log(data);

                if (data.error) {
                    throw new Error(data.error);
                }

                // Confirm payment with Stripe
                const {
                    error
                } = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            email: formData.get('customer_email')
                        }
                    }
                });

                if (error) {
                    // Show error to customer
                    document.getElementById('card-errors').textContent = error.message;
                } else {
                    // Payment succeeded, redirect to success page
                    window.location.href = '{{ route('payment.success') }}?payment_intent_id=' + data
                        .payment_intent_id;
                }
            } catch (error) {
                document.getElementById('card-errors').textContent = error.message;
            }

            // Re-enable submit button
            submitButton.disabled = false;
            buttonText.style.display = 'inline';
            loading.style.display = 'none';
        });
    </script>
    {{-- success  --}}
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Print receipt functionality (optional)
            function printReceipt() {
                window.print();
            }

            // Add print button if needed
            const actionButtons = document.querySelector('.action-buttons');
            const printBtn = document.createElement('a');
            printBtn.href = '#';
            printBtn.className = 'btn btn-secondary';
            printBtn.innerHTML = '<i class="fas fa-print"></i> Print Receipt';
            printBtn.onclick = function(e) {
                e.preventDefault();
                printReceipt();
            };

            // Uncomment to add print button
            // actionButtons.appendChild(printBtn);
        });
    </script>
</body>

</html>
