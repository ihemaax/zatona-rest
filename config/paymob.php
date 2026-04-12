<?php

return [
    'enabled' => (bool) env('PAYMOB_ENABLED', false),
    'base_url' => rtrim((string) env('PAYMOB_BASE_URL', 'https://accept.paymob.com/api'), '/'),
    'api_key' => (string) env('PAYMOB_API_KEY', ''),
    'hmac_secret' => (string) env('PAYMOB_HMAC_SECRET', ''),
    'card_integration_id' => (int) env('PAYMOB_CARD_INTEGRATION_ID', 0),
    'iframe_id' => (int) env('PAYMOB_IFRAME_ID', 0),
    'success_url' => (string) env('PAYMOB_SUCCESS_URL', ''),
    'failure_url' => (string) env('PAYMOB_FAILURE_URL', ''),
    'pending_url' => (string) env('PAYMOB_PENDING_URL', ''),
    'webhook_url' => (string) env('PAYMOB_WEBHOOK_URL', ''),
    'timeout' => (int) env('PAYMOB_TIMEOUT', 20),
    'currency' => 'EGP',
];
