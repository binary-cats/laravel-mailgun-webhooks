<?php

namespace BinaryCats\MailgunWebhooks;

use Exception;
use BinaryCats\MailgunWebhooks\Webhook;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;

class MailgunSignatureValidator implements SignatureValidator
{
    /**
     * Bind the implemetation
     *
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Inject the config
     *
     * @var Spatie\WebhookClient\WebhookConfig
     */
    protected $config;

    /**
     * True if the signature has been valiates
     *
     * @param  Illuminate\Http\Request       $request
     * @param  Spatie\WebhookClient\WebhookConfig $config
     *
     * @return boolean
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->get('signature', []);
        $secret = $config->signingSecret;

        try {
            Webhook::constructEvent($request->all(), $signature, $secret);
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }
}
