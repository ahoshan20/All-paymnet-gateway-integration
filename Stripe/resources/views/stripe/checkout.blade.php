<!DOCTYPE html>
<html>
<head>
    <title>Stripe Checkout Example</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
<h2>Stripe Payment Example</h2>
<form id="payment-form">
    <div id="card-element"></div>
    <button id="submit">Pay</button>
</form>

<script>
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const {error, paymentMethod} = await stripe.createPaymentMethod({
            type: 'card',
            card: card,
        });

        if (error) {
            alert(error.message);
        } else {
            const res = await fetch("{{ route('stripe.charge') }}", {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({payment_method: paymentMethod.id})
            });
            const data = await res.json();
            if (data.success) {
                alert('Payment successful!');
                window.location.reload();
            } else {
                alert('Payment failed: ' + data.error);
            }
        }
    });
</script>
</body>
</html>
