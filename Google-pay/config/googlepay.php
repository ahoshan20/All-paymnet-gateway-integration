<?php

return [
    'environment' => env('GOOGLE_PAY_ENVIRONMENT', 'TEST'), // TEST or PRODUCTION
    'gateway_merchant_id' => env('GOOGLE_PAY_GATEWAY_MERCHANT_ID'),
    'merchant_id' => env('GOOGLE_PAY_MERCHANT_ID'),
    'merchant_name' => env('GOOGLE_PAY_MERCHANT_NAME', 'Your Store Name'),
    'allowed_auth_methods' => ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
    'allowed_card_networks' => ['AMEX', 'DISCOVER', 'JCB', 'MASTERCARD', 'VISA'],
    'gateway' => env('GOOGLE_PAY_GATEWAY', 'stripe'), // stripe, square, etc.
    'gateway_merchant_id' => env('GOOGLE_PAY_GATEWAY_MERCHANT_ID'),
];