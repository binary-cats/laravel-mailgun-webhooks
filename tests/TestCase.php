<?php

namespace BinaryCats\MailgunWebhooks\Tests;

use Exception;
use CreateWebhookCallsTable;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use BinaryCats\MailgunWebhooks\MailgunWebhooksServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config(['mailgun-webhooks.signing_secret' => 'test_signing_secret']);
    }

    protected function setUpDatabase()
    {
        include_once __DIR__.'/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        (new CreateWebhookCallsTable())->up();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MailgunWebhooksServiceProvider::class,
        ];
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler
        {
            public function __construct()
            {
            }

            public function report(Exception $e)
            {
            }

            public function render($request, Exception $exception)
            {
                throw $exception;
            }
        });
    }

    protected function determineMailgunSignature(array $payload, string $configKey = null): array
    {
        $secret = ($configKey) ?
            config("mailgun-webhooks.signing_secret_{$configKey}") :
            config('mailgun-webhooks.signing_secret');

        $timestamp = time();

        $timestampedPayload = $timestamp.'.'.json_encode($payload);

        $token = hash_hmac('sha256', $timestampedPayload, $secret);

        return [
            "timestamp" => $timestamp,
            "token" => $token,
            "signature" => hash_hmac('sha256', "{$timestamp}.{$token}", $secret),
        ];
    }
}
