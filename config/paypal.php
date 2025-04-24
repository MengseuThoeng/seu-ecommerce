<?php
/**
 * PayPal Setting & API Credentials
 */

return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'),
    
    // Add these REST API credentials
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'app_id' => '',
    ],
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'app_id' => '',
    ],
    
    // Common settings
    'payment_action' => 'Sale',
    'currency'       => env('PAYPAL_CURRENCY', 'USD'),
    'notify_url'     => '',
    'locale'         => '',
    'validate_ssl'   => true,
];