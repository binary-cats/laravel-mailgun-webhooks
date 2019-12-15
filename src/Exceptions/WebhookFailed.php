<?php

namespace BinaryCats\MailgunWebhooks\Exceptions;

use Exception;
use Spatie\WebhookClient\Models\WebhookCall;

class WebhookFailed extends Exception
{
    public static function signingSecretNotSet() : WebhookFailed
    {
        return new static('The webhook signing secret is not set. Make sure that the `signing_secret` config key is set to the correct value.');
    }

    public static function jobClassDoesNotExist(string $jobClass, WebhookCall $webhookCall): WebhookFailed
    {
        return new static("Could not process webhook id `{$webhookCall->id}` of type `{$webhookCall->type} because the configured jobclass `$jobClass` does not exist.");
    }

    public static function missingType(WebhookCall $webhookCall): WebhookFailed
    {
        return new static("Webhook call id `{$webhookCall->id}` did not contain a type. Valid Mailgun webhook calls should always contain a type.");
    }

    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }
}
