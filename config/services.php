<?php

return [


'firebase' => [
    'credentials' => env(
        'FIREBASE_CREDENTIALS',
        '/etc/secrets/firebase-service-account.json'
    ),
],
    
'openwa' => [
    'base_url' => env('OPENWA_BASE_URL'),
    'api_key' => env('OPENWA_API_KEY'),
    'session_id' => env('OPENWA_SESSION_ID'),
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
    
    'sendlib' => [
    'api_key' => env('SENDLIB_API_KEY'),
    'from_email' => env('SENDLIB_FROM_EMAIL'),
],

];