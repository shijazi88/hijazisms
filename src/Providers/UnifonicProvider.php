<?php

namespace Hijazi\Hijazisms\Providers;

use Hijazi\Hijazisms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;

class UnifonicProvider implements SmsProviderInterface
{
    protected $appSid;

    public function __construct($appSid)
    {
        $this->appSid = $appSid;
    }

    public function sendSms($recipient, $message, $otp)
    {
        $url = 'http://basic.unifonic.com/rest/SMS/messages?AppSid=' . $this->appSid . '&SenderID=EliteVPS&Body=' . urlencode($message) . '&Recipient=' . $recipient . '&responseType=JSON&CorrelationID=%22%22&baseEncode=true&statusCallback=sent&async=false';

        $response = Http::post($url);

        return $response->successful();
    }
}
