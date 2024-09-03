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
];
