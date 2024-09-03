# Hijazisms - SMS Sending Package for Laravel

This package provides a comprehensive solution for sending SMS using different providers in Laravel applications. It includes functionality for sending SMS directly, scheduling SMS messages, logging SMS messages to a database, implementing a retry mechanism for failed SMS deliveries, and enforcing rate limits.

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Usage](#usage)
   - [Sending SMS](#sending-sms)
   - [Sending SMS with Logging](#sending-sms-with-logging)
   - [Bulk SMS Sending](#bulk-sms-sending)
   - [Scheduled SMS](#scheduled-sms)
   - [Retrying Failed SMS](#retrying-failed-sms)
   - [Rate Limiting](#rate-limiting)
5. [Managing Migrations](#managing-migrations)
6. [Explanation of Modules](#explanation-of-modules)
7. [Scheduled Task Setup](#scheduled-task-setup)
8. [Contributing](#contributing)
9. [License](#license)

## Features

- Send SMS using multiple providers (e.g., WhatsApp, Msgat, Unifonic).
- Log SMS messages to a database with status tracking (success, failed, scheduled).
- Schedule SMS messages to be sent at a future time.
- Retry failed SMS messages.
- Enforce rate limits to control the number of messages sent to a recipient within a time frame.
- Seamless integration with Laravel’s configuration and service container.

## Installation

### 1. Require the Package

To install the package, run the following command in your Laravel project:

```bash
composer require hijazi/hijazisms
```

### 2. Publish the Configuration and Migration

Next, publish the configuration file and migration using Artisan commands:

```bash
php artisan vendor:publish --provider="Hijazi\Hijazisms\HijazismsServiceProvider" --tag=config
php artisan vendor:publish --provider="Hijazi\Hijazisms\HijazismsServiceProvider" --tag=migrations
```

### 3. Run the Migration

After publishing, run the migration to create the `sms_logs` and `sms_rate_limits` tables:

```bash
php artisan migrate
```

## Configuration

### 1. Environment Variables

Add your SMS provider credentials to the `.env` file:

```env
# WhatsApp Provider
WHATSAPP_API_KEY=your-whatsapp-api-key

# Msgat Provider
MSGAT_API_KEY=your-msgat-api-key
MSGAT_SENDER_NAME=your-sender-name

# Unifonic Provider
UNIFONIC_APP_SID=your-unifonic-app-sid
```

### 2. Configuration File

The configuration file `config/hijazisms.php` is published to your Laravel application. It contains the settings for each provider:

```php
return [
    'providers' => [
        'whatsapp' => [
            'api_key' => env('WHATSAPP_API_KEY', 'your-default-whatsapp-api-key'),
        ],
        'msgat' => [
            'api_key' => env('MSGAT_API_KEY', 'your-default-msgat-api-key'),
            'sender_name' => env('MSGAT_SENDER_NAME', 'your-default-sender-name'),
        ],
        'unifonic' => [
            'app_sid' => env('UNIFONIC_APP_SID', 'your-default-unifonic-app-sid'),
        ],
    ],
];
```

## Usage

### 1. Sending SMS

To send SMS using a specific provider, use the `sendSms` method:

```php
use Hijazi\Hijazisms\SmsManager;
use Hijazi\Hijazisms\Providers\WhatsappProvider;

$smsManager = app(SmsManager::class);
$smsManager->setProvider(new WhatsappProvider(config('hijazisms.providers.whatsapp.api_key')));

$recipient = '0123456789';
$message = 'Your verification code is 123456';

$status = $smsManager->sendSms($recipient, $message);

if ($status) {
    echo "SMS sent successfully.";
} else {
    echo "Failed to send SMS.";
}
```

### 2. Sending SMS with Logging

To send SMS and log the attempt to the database, use the `sendSmsAndLog` method:

```php
use Hijazi\Hijazisms\SmsManager;
use Hijazi\Hijazisms\Providers\WhatsappProvider;

$smsManager = app(SmsManager::class);
$smsManager->setProvider(new WhatsappProvider(config('hijazisms.providers.whatsapp.api_key')));

$recipient = '0123456789';
$message = 'Your verification code is 123456';

$status = $smsManager->sendSmsAndLog($recipient, $message);

if ($status === 'success') {
    echo "SMS sent and logged successfully.";
} else {
    echo "Failed to send SMS and log it.";
}
```

### 3. Bulk SMS Sending

To send SMS to multiple recipients in bulk, use the `sendSmsBulk` method:

```php
use Hijazi\Hijazisms\SmsManager;
use Hijazi\Hijazisms\Providers\WhatsappProvider;

$smsManager = app(SmsManager::class);
$smsManager->setProvider(new WhatsappProvider(config('hijazisms.providers.whatsapp.api_key')));

$recipients = ['0123456789', '0987654321', '0112233445'];
$message = 'This is a bulk message';

$results = $smsManager->sendSmsBulk($recipients, $message);

foreach ($results as $recipient => $status) {
    echo "SMS to {$recipient}: {$status}" . PHP_EOL;
}
```

### 4. Scheduled SMS

To schedule an SMS to be sent at a later time, use the `scheduleSms` method:

```php
use Hijazi\Hijazisms\SmsManager;
use Hijazi\Hijazisms\Providers\WhatsappProvider;

$smsManager = app(SmsManager::class);
$smsManager->setProvider(new WhatsappProvider(config('hijazisms.providers.whatsapp.api_key')));

$recipient = '0123456789';
$message = 'This is a scheduled message';
$sendAt = new DateTime('2024-09-15 10:00:00');

$smsManager->scheduleSms($recipient, $message, $sendAt);

echo "SMS scheduled successfully.";
```

### 5. Retrying Failed SMS

To retry sending SMS messages that previously failed, use the `retryFailedSms` method:

```php
$smsManager->retryFailedSms();
```

### 6. Rate Limiting

The package includes rate limiting to control the number of messages sent to a recipient within a specific time frame. The rate limit is set in the code and can be modified as needed.

- **Default Rate Limit**: The default rate limit is 5 messages per hour. If a recipient exceeds this limit, no more messages will be sent to them until the limit resets.

The rate limit is checked automatically in the `sendSms`, `sendSmsAndLog`, and `sendSmsBulk` methods.

## Managing Migrations

The package includes a migration file that creates the `sms_logs` and `sms_rate_limits` tables used for logging SMS messages, scheduling SMS, and tracking rate limits. To publish the migration to your main Laravel application, run:

```bash
php artisan vendor:publish --provider="Hijazi\Hijazisms\HijazismsServiceProvider" --tag=migrations
```

After publishing, run the migration:

```bash
php artisan migrate
```

## Explanation of Modules

1. **SmsManager**: Manages sending SMS messages, scheduling, logging, retrying failed SMS, and enforcing rate limits.
2. **SmsProviderInterface**: Defines the contract that all SMS providers must implement.
3. **Providers**: Each provider (e.g., `WhatsappProvider`, `MsgatProvider`, `UnifonicProvider`) implements the `SmsProviderInterface`.
4. **sms_logs**: Database table that logs all SMS messages, including those scheduled for future sending.
5. **sms_rate_limits**: Database table that tracks the number of SMS messages sent to a recipient within a specific time frame to enforce rate limits.

## Scheduled Task Setup

To ensure scheduled SMS messages are sent, you should set up a scheduled task or cron job to call the `processScheduledSms` method regularly. Add the following entry to your Laravel schedule in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $smsManager = app(SmsManager::class);
        $smsManager->processScheduledSms();
    })->everyMinute();
}
```

This setup ensures that the scheduled SMS messages are processed and sent at the correct time.

## Contributing

If you encounter any issues or have suggestions for improvements, feel free to open an issue or submit a pull request.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
