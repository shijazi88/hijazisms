<?php

namespace Hijazi\Hijazisms;

use Illuminate\Support\ServiceProvider;

class HijazismsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge the package configuration with the application's configuration
        $this->mergeConfigFrom(__DIR__.'/../config/hijazisms.php', 'hijazisms');

        // Register the SmsManager service
        $this->app->singleton(SmsManager::class, function ($app) {
            return new SmsManager($app);
        });
    }

    public function boot()
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/hijazisms.php' => config_path('hijazisms.php'),
        ], 'config');

        // Publish the migration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/2024_09_03_000000_create_sms_logs_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_sms_logs_table.php'),
            ], 'migrations');
        }
    }
}
