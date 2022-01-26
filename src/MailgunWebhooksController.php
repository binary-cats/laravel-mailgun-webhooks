<?php

namespace BinaryCats\MailgunWebhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class MailgunWebhooksController
{
    /**
     * Invoke controller method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $configKey
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, string $configKey = null)
    {
        $webhookConfig = new WebhookConfig([
            'name' => 'mailgun',
            'signing_secret' => ($configKey) ?
                config('mailgun-webhooks.signing_secret_'.$configKey) :
                config('mailgun-webhooks.signing_secret'),
            'signature_header_name' => null,
            'signature_validator' => MailgunSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_model' => config('mailgun-webhooks.model'),
            'process_webhook_job' => config('mailgun-webhooks.process_webhook_job'),
        ]);

        return (new WebhookProcessor($request, $webhookConfig))->process();
    }
}
