<?php

namespace BinaryCats\MailgunWebhooks;

use BinaryCats\MailgunWebhooks\Exceptions\WebhookFailed;

class Webhook
{
    /**
     * Validate and raise an appropriate event.
     *
     * @param mixed[] $payload
     * @param string[] $signature
     * @param string $secret
     * @return \BinaryCats\MailgunWebhooks\Event
     * @throws WebhookFailed
     */
    public static function constructEvent(array $payload, array $signature, string $secret): Event
    {
        // verify we are good, else throw an exception
        if (! WebhookSignature::make($signature, $secret)->verify()) {
            throw WebhookFailed::invalidSignature();
        }
        // Make an event
        return Event::constructFrom($payload);
    }
}
