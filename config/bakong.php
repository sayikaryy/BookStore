<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bakong Open API Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and endpoints for NBC Bakong Open API Integration
    | Documentation: https://api-bakong.nbc.gov.kh/document
    |
    */

    'account_id' => env('BAKONG_ACCOUNT_ID', 'vathana_sieng@bkrt'),
    'token' => env('BAKONG_TOKEN', ''),
    'base_url' => env('BAKONG_BASE_URL', 'https://api-bakong.nbc.gov.kh'),
    'merchant_name' => env('BAKONG_MERCHANT_NAME', 'vathana Sieng'),
    'merchant_city' => env('BAKONG_MERCHANT_CITY', 'Phnom Penh'),
    'currency' => env('BAKONG_CURRENCY', 'USD'),

    // Minutes a dynamic KHQR stays payable (written into the QR's expiry tag)
    'qr_lifetime' => (int) env('BAKONG_QR_LIFETIME', 15),

    // Lets customers mark their own pending order as paid without scanning.
    // Bakong moves real money, so only enable this for local testing.
    'allow_simulation' => (bool) env('BAKONG_ALLOW_SIMULATION', false),

    // Mobile App Deep Link Metadata
    'app_name' => env('BAKONG_APP_NAME', 'BookVerse'),
    'app_icon_url' => env('BAKONG_APP_ICON_URL', 'https://bakong.nbc.gov.kh/images/logo.svg'),
    'app_deeplink_callback' => env('BAKONG_APP_CALLBACK', 'bookverse://payment/complete'),
];
