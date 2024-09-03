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

    public function sendSms($recipient, $message)
    {
        if (!$this->provider) {
            throw new \Exception('No SMS provider has been set.');
        }

        return $this->provider->sendSms($recipient, $message);
    }

    public function sendSmsAndLog($recipient, $message)
    {
        if (!$this->provider) {
            throw new \Exception('No SMS provider has been set.');
        }

        $status = $this->provider->sendSms($recipient, $message) ? 'success' : 'failed';

        DB::table('sms_logs')->insert([
            'mobile' => $recipient,
            'sms' => $message,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $status;
    }

    public function retryFailedSms()
    {
        $failedSmsLogs = DB::table('sms_logs')->where('status', 'failed')->get();

        foreach ($failedSmsLogs as $log) {
            $status = $this->provider->sendSms($log->mobile, $log->sms) ? 'success' : 'failed';

            DB::table('sms_logs')
                ->where('id', $log->id)
                ->update([
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        }
    }

    // Method to send SMS in bulk
    public function sendSmsBulk(array $recipients, $message)
    {
        if (!$this->provider) {
            throw new \Exception('No SMS provider has been set.');
        }

        $results = [];

        foreach ($recipients as $recipient) {
            $status = $this->provider->sendSms($recipient, $message) ? 'success' : 'failed';

            // Log each SMS attempt to the database
            DB::table('sms_logs')->insert([
                'mobile' => $recipient,
                'sms' => $message,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $results[$recipient] = $status;
        }

        return $results;
    }
}
