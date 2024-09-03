<?php

namespace Hijazi\Hijazisms;

use Illuminate\Support\ServiceProvider;

class HijazismsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge the configuration file
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
        ], 'hijazisms-config');


    }
}
