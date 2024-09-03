<?php

namespace Hijazi\Hijazisms;

use Hijazi\Hijazisms\Contracts\SmsProviderInterface;

class SmsManager
{
    protected $provider;

    public function setProvider(SmsProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function sendSms($recipient, $message, $otp)
    {
        if (!$this->provider) {
            throw new \Exception('No SMS provider has been set.');
        }

        return $this->provider->sendSms($recipient, $message, $otp);
    }
}
