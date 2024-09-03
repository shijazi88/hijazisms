<?php

namespace Hijazi\Hijazisms\Providers;

use Hijazi\Hijazisms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;

class WhatsappProvider implements SmsProviderInterface
{
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function sendSms($recipient, $message, $otp)
    {
        $whatsappNumberFormatted = '966' . ltrim($recipient, '0');
        $apiUrl = 'https://live-mt-server.wati.io/300738/api/v1/sendTemplateMessage?whatsappNumber=' . $whatsappNumberFormatted;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json-patch+json',
        ])->post($apiUrl, [
            'broadcast_name' => $whatsappNumberFormatted,
            'template_name' => 'verification_code',
            'parameters' => [
                [
                    'name' => '1',
                    'value' => $otp,
                ],
            ],
        ]);

        return $response->successful();
    }
}
