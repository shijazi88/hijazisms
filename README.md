# Hijazisms - SMS Sending Package for Laravel

This package provides a comprehensive solution for sending SMS using different providers in Laravel applications. It includes functionality for sending SMS directly, logging SMS messages to a database, and implementing a retry mechanism for failed SMS deliveries.

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Usage](#usage)
   - [Sending SMS Without Logging](#sending-sms-without-logging)
   - [Sending SMS with Logging](#sending-sms-with-logging)
   - [Retrying Failed SMS](#retrying-failed-sms)
5. [Managing Migrations](#managing-migrations)
6. [Explanation of Modules](#explanation-of-modules)
7. [Contributing](#contributing)
8. [License](#license)

## Features

- Send SMS using multiple providers (e.g., WhatsApp, Msgat, Unifonic).
- Log SMS messages to a database with status tracking (success/failed).
- Retry failed SMS messages.
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

After publishing, run the migration to create the `sms_logs` table:

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

### 1. Sending SMS Without Logging

To send SMS using a specific provider without logging it to the database, use the `sendSms` method:

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

### 3. Retrying Failed SMS

To retry sending SMS messages that previously failed, use the `retryFailedSms` method:

```php
$smsManager->retryFailedSms();
```

## Managing Migrations

The package includes a migration file that creates the `sms_logs` table used for logging SMS messages. To publish the migration to your main Laravel application, run:

```bash
php artisan vendor:publish --provider="Hijazi\Hijazisms\HijazismsServiceProvider" --tag=migrations
```

After publishing, run the migration:

```bash
php artisan migrate
```

## Explanation of Modules

1. **SmsManager**: Manages sending SMS messages and can log them to the database.
2. **SmsProviderInterface**: Defines the contract that all SMS providers must implement.
3. **Providers**: Each provider (e.g., `WhatsappProvider`, `MsgatProvider`, `UnifonicProvider`) implements the `SmsProviderInterface`.
4. **SmsLog**: (Implemented in the main application) Logs SMS messages to the database, including the recipient, message, status, and timestamps.

## Contributing

If you encounter any issues or have suggestions for improvements, feel free to open an issue or submit a pull request.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
