<?php

return [
    'providers' => [
        'whatsapp' => [
            'api_key' => env('WHATSAPP_API_KEY', 'your-default-whatsapp-api-key'),
        ],
        'msgat' => [
            'api_key' => env('MSGAT_API_KEY', 'your-default-msgat-api-key'),
            'sender_name' => env('MSGAT_SENDER_NAME', 'your-default-sender-name'),
        ],
        'unifonic' => [
            'app_sid' => env('UNIFONIC_APP_SID', 'your-default-unifonic-app-sid'),
        ],
    ],
    // Rate limit configuration
    'rate_limit' => [
        'count' => env('SMS_RATE_LIMIT_COUNT', 5),  // Default to 5 messages
        'hours' => env('SMS_RATE_LIMIT_HOURS', 1),  // Default to 1 hour
    ],
];
