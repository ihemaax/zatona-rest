<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'groq' => [
    'api_key' => env('GROQ_API_KEY'),
    'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
],
'openrouter' => [
    'api_key' => env('OPENROUTER_API_KEY'),
    'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-70b-instruct:free'),
],

'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
],
    'wapilot' => [
        'base_url' => env('WAPILOT_BASE_URL', 'https://app.wapilot.net/api/v2'),
        'api_token' => env('WAPILOT_API_TOKEN'),
        'session_id' => env('WAPILOT_SESSION_ID'),
        'timeout' => env('WAPILOT_TIMEOUT', 20),
        'enabled' => env('WAPILOT_ENABLED', true),
        'auth_header' => env('WAPILOT_AUTH_HEADER', 'Authorization'),
        'auth_prefix' => env('WAPILOT_AUTH_PREFIX', 'Bearer '),
        'endpoints' => [
            'otp_send' => env('WAPILOT_ENDPOINT_OTP_SEND', '/otp/send'),
            'otp_verify' => env('WAPILOT_ENDPOINT_OTP_VERIFY', '/otp/verify'),
            'sessions_list' => env('WAPILOT_ENDPOINT_SESSIONS_LIST', '/whatsapp-session/list'),
            'session_status' => env('WAPILOT_ENDPOINT_SESSION_STATUS', '/whatsapp-session/{sessionId}/status'),
        ],
    ],
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    'analytics' => [
        'measurement_id' => env('ANALYTICS_MEASUREMENT_ID'),
        'load_external_script' => env('ANALYTICS_LOAD_EXTERNAL_SCRIPT', false),
    ],
];
