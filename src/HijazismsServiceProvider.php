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
        \Log::info('HijazismsServiceProvider boot method called.');

        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/hijazisms.php' => config_path('hijazisms.php'),
        ], 'config');
    }
}
