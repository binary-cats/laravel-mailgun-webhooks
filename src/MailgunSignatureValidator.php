<?php

namespace BinaryCats\MailgunWebhooks;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class MailgunSignatureValidator implements SignatureValidator
{
    /**
     * True if the signature has been valiates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\WebhookClient\WebhookConfig  $config
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $this->signature($request);

        $secret = $config->signingSecret;

        try {
            Webhook::constructEvent($request->all(), $signature, $secret);
        } catch (Exception $exception) {
            // make the app aware
            report($exception);

            return false;
        }

        return true;
    }

    /**
     * Validate the incoming signature' schema.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string[]
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
