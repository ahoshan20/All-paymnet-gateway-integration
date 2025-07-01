<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .customer-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .payments-table { width: 100%; border-collapse: collapse; }
        .payments-table th, .payments-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .status-success { color: #28a745; font-weight: bold; }
        .status-failed { color: #dc3545; font-weight: bold; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Customer Dashboard</h1>
    
    <div style="margin-bottom: 20px;">
        <input type="email" id="email-input" placeholder="Enter your email" style="padding: 10px; margin-right: 10px;">
        <button onclick="loadCustomerData()" class="btn">Load Data</button>
    </div>

    <div class="customer-info">
        <h2>Customer Information</h2>
        <p><strong>Name:</strong> <span id="customer-name">-</span></p>
        <p><strong>Email:</strong> <span id="customer-email">-</span></p>
        <p><strong>Total Paid:</strong> $<span id="total-paid">0.00</span></p>
        <p><strong>Total Payments:</strong> <span id="payment-count">0</span></p>
    </div>

    <div>
        <h2>Payment History</h2>
        <table class="payments-table" id="payments-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align: center; color: #666;">Enter your email to load payment history</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        async function loadCustomerData() {
            const email = document.getElementById('email-input').value;
            if (!email) {
                alert('Please enter your email');
                return;
            }

            try {
                const response = await fetch('/customer/payments?' + new URLSearchParams({
                    customer_email: email
                }), {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Update customer info
                document.getElementById('customer-name').textContent = data.customer.name || 'N/A';
                document.getElementById('customer-email').textContent = data.customer.email;
                document.getElementById('total-paid').textContent = data.total_paid.toFixed(2);
                document.getElementById('payment-count').textContent = data.payment_count;

                // Update payments table
                const tbody = document.querySelector('#payments-table tbody');
                tbody.innerHTML = '';

                if (data.payments.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #666;">No payments found</td></tr>';
                    return;
                }

                data.payments.data.forEach(payment => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                        <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                        <td><span class="status-${payment.status === 'succeeded' ? 'success' : 'failed'}">${payment.status}</span></td>
                        <td>${payment.payment_method || 'N/A'}</td>
                        <td>${payment.stripe_payment_intent_id.substring(0, 20)}...</td>
                    `;
                    tbody.appendChild(row);
                });

            } catch (error) {
                console.error('Error loading customer data:', error);
                alert('Failed to load customer data');
            }
        }
    </script>
</body>
</html>