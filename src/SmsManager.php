<?php

namespace Hijazi\Hijazisms;

use Hijazi\Hijazisms\Contracts\SmsProviderInterface;
use App\Models\SmsLog;

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

    // New method to send SMS and log to the database
    public function sendSmsAndLog($recipient, $message, $otp)
    {
        if (!$this->provider) {
            throw new \Exception('No SMS provider has been set.');
        }

        // Attempt to send the SMS
        $status = $this->provider->sendSms($recipient, $message, $otp) ? 'success' : 'failed';

        // Log the SMS to the database
        SmsLog::create([
            'mobile' => $recipient,
            'sms' => $message,
            'status' => $status,
        ]);

        return $status;
    }

    // Retry sending failed SMS messages
    public function retryFailedSms()
    {
        $failedSmsLogs = SmsLog::where('status', 'failed')->get();

        foreach ($failedSmsLogs as $log) {
            $status = $this->provider->sendSms($log->mobile, $log->sms, null) ? 'success' : 'failed';

            // Update the status in the database
            $log->status = $status;
            $log->save();
        }
    }
}
