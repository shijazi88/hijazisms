<?php

return [
    'providers' => [
        'whatsapp' => [
            'api_key' => env('WHATSAPP_API_KEY'),
        ],
        'msgat' => [
            'api_key' => env('MSGAT_API_KEY'),
            'sender_name' => env('MSGAT_SENDER_NAME'),
        ],
        'unifonic' => [
            'app_sid' => env('UNIFONIC_APP_SID'),
        ],
    ],
];
