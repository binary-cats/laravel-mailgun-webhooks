<?php

namespace BinaryCats\MailgunWebhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MailgunWebhooksServiceProvider extends ServiceProvider
{
    /**
     * Boot application services
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mailgun-webhooks.php' => config_path('mailgun-webhooks.php'),
            ], 'config');
        }

        Route::macro('mailgunWebhooks', function ($url) {
            return Route::post($url, '\BinaryCats\MailgunWebhooks\MailgunWebhooksController');
        });
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mailgun-webhooks.php', 'mailgun-webhooks');
    }
}
