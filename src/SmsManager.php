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

        // Check rate limit
        if (!$this->checkRateLimit($recipient)) {
            throw new \Exception('Rate limit exceeded for this recipient.');
        }

        // Send the SMS
        $status = $this->provider->sendSms($recipient, $message) ? 'success' : 'failed';

        // Log the SMS to the database
        DB::table('sms_logs')->insert([
            'mobile' => $recipient,
            'sms' => $message,
            'status' => $status,
            'send_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update rate limit tracking
        $this->updateRateLimit($recipient);

        return $status;
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
        $rateLimit = DB::table('sms_rate_limits')->where('mobile', $mobile)->first();

        if (!$rateLimit) {
            return true; // No rate limit record, allow sending
        }

        $sentCount = $rateLimit->sent_count;
        $lastSentAt = $rateLimit->last_sent_at;

        // Assuming a limit of 5 messages per hour
        if ($sentCount >= 5 && now()->diffInHours($lastSentAt) < 1) {
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
