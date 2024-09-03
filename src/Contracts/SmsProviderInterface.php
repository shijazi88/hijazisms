<?php

namespace Hijazi\Hijazisms\Contracts;

interface SmsProviderInterface
{
    public function sendSms($recipient, $message);
}
