<?php

namespace Hijazi\Hijazisms;

use Hijazi\Hijazisms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\DB;

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

        // Log the SMS to the database using DB::table
        DB::table('sms_logs')->insert([
            'mobile' => $recipient,
            'sms' => $message,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $status;
    }

    // Retry sending failed SMS messages
    public function retryFailedSms()
    {
        $failedSmsLogs = DB::table('sms_logs')->where('status', 'failed')->get();

        foreach ($failedSmsLogs as $log) {
            $status = $this->provider->sendSms($log->mobile, $log->sms, null) ? 'success' : 'failed';

            // Update the status in the database using DB::table
            DB::table('sms_logs')
                ->where('id', $log->id)
                ->update([
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        }
    }
}
