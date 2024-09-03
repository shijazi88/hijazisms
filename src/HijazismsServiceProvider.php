<?php

namespace Hijazi\Hijazisms;

use Illuminate\Support\ServiceProvider;

class HijazismsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hijazisms.php', 'hijazisms');

        $this->app->singleton(SmsManager::class, function ($app) {
            return new SmsManager($app);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/hijazisms.php' => config_path('hijazisms.php'),
        ], 'config');
    }
}
