<?php

namespace BinaryCats\MailgunWebhooks;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class MailgunSignatureValidator implements SignatureValidator
{
    /**
     * Bind the implemetation.
     *
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Inject the config.
     *
     * @var Spatie\WebhookClient\WebhookConfig
     */
    protected $config;

    /**
     * True if the signature has been valiates.
     *
     * @param  Illuminate\Http\Request       $request
     * @param  Spatie\WebhookClient\WebhookConfig $config
     *
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $this->signature($request);

        $secret = $config->signingSecret;

        try {
            Webhook::constructEvent($request->all(), $signature, $secret);
        } catch (Exception $exception) {
            report($exception);

            return false;
        }

        return true;
    }

    /**
     * Validate the incoming signature' schema.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function signature(Request $request): array
    {
        $validated = $request->validate([
            'signature.signature' => 'bail|required',
            'signature.timestamp' => 'required',
            'signature.token' => 'required',
        ]);

        return $validated['signature'];
    }
}
