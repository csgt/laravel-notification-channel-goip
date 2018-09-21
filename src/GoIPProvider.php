<?php
namespace NotificationChannels\GoIP;

use Illuminate\Support\ServiceProvider;

class GoIPProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(GoIPChannel::class)
            ->needs(GoIP::class)
            ->give(function () {
                return new GoIP(
                    $this->app->make(GoIPConfig::class)
                );
            });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(GoIPConfig::class, function () {
            return new GoIPConfig($this->app['config']['services.GoIP']);
        });
    }
}
