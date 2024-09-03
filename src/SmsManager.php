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
            return [
                'status' => 'failed',
                'reason' => 'No SMS provider has been set.'
            ];
        }

        // Check rate limit
        if (!$this->checkRateLimit($recipient)) {
            return [
                'status' => 'failed',
                'reason' => 'Rate limit exceeded for this recipient.'
            ];
        }

        // Send the SMS
        $status = $this->provider->sendSms($recipient, $message) ? 'success' : 'failed';

        // Update rate limit tracking if the SMS was sent successfully
        if ($status === 'success') {
            $this->updateRateLimit($recipient);
        }

        return [
            'status' => $status,
            'reason' => $status === 'success' ? 'SMS sent successfully.' : 'Failed to send SMS.'
        ];
    }

    public function sendSmsAndLog($recipient, $message)
    {
        return $this->sendSms($recipient, $message); // Now combined with sendSms method
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

            // Update rate limit tracking
            if ($status === 'success') {
                $this->updateRateLimit($log->mobile);
            }
        }
    }

    // Schedule an SMS to be sent at a later time
    public function scheduleSms($recipient, $message, \DateTime $sendAt)
    {
        DB::table('sms_logs')->insert([
            'mobile' => $recipient,
            'sms' => $message,
            'status' => 'scheduled',
            'send_at' => $sendAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Process scheduled SMS messages
    public function processScheduledSms()
    {
        $scheduledMessages = DB::table('sms_logs')
            ->where('status', 'scheduled')
            ->where('send_at', '<=', now())
            ->get();

        foreach ($scheduledMessages as $message) {
            // Check rate limit
            if ($this->checkRateLimit($message->mobile)) {
                $status = $this->provider->sendSms($message->mobile, $message->sms) ? 'success' : 'failed';

                DB::table('sms_logs')
                    ->where('id', $message->id)
                    ->update([
                        'status' => $status,
                        'updated_at' => now(),
                    ]);

                // Update rate limit tracking
                if ($status === 'success') {
                    $this->updateRateLimit($message->mobile);
                }
            } else {
                DB::table('sms_logs')
                    ->where('id', $message->id)
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    // Check if the recipient has exceeded the rate limit
    protected function checkRateLimit($mobile)
    {
        // Retrieve the rate limit values from the configuration
        $limitCount = config('hijazisms.rate_limit.count'); // Default is handled in config file
        $limitHours = config('hijazisms.rate_limit.hours'); // Default is handled in config file

        $rateLimit = DB::table('sms_rate_limits')->where('mobile', $mobile)->first();

        if (!$rateLimit) {
            return true; // No rate limit record, allow sending
        }

        $sentCount = $rateLimit->sent_count;
        $lastSentAt = $rateLimit->last_sent_at;

        // Check against the dynamic rate limits
        if ($sentCount >= $limitCount && now()->diffInHours($lastSentAt) < $limitHours) {
            return false;
        }

        return true;
    }


    // Update the rate limit tracking after sending an SMS
    protected function updateRateLimit($mobile)
    {
        $rateLimit = DB::table('sms_rate_limits')->where('mobile', $mobile)->first();

        if ($rateLimit) {
            // Increment the sent count and update the last sent time
            DB::table('sms_rate_limits')
                ->where('mobile', $mobile)
                ->update([
                    'sent_count' => DB::raw('sent_count + 1'),
                    'last_sent_at' => now(),
                    'updated_at' => now(),
                ]);
        } else {
            // Create a new rate limit record
            DB::table('sms_rate_limits')->insert([
                'mobile' => $mobile,
                'sent_count' => 1,
                'last_sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
