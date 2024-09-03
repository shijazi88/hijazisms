<?php

namespace Hijazi\Hijazisms\Providers;

use Hijazi\Hijazisms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;

class MsgatProvider implements SmsProviderInterface
{
    protected $apiKey;
    protected $senderName;

    public function __construct($apiKey, $senderName)
    {
        $this->apiKey = $apiKey;
        $this->senderName = $senderName;
    }

    public function sendSms($recipient, $message, $otp)
    {
        $response = Http::post('https://www.msegat.com/gw/sendsms.php', [
            'userName' => $this->senderName,
            'apiKey' => $this->apiKey,
            'numbers' => $recipient,
            'userSender' => $this->senderName,
            'msg' => $message,
            'timeToSend' => 'now',
            'msgEncoding' => 'UTF8',
            'lang' => 'en',
        ]);

        return $response->successful();
    }
}
