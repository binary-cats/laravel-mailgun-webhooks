<?php

namespace BinaryCats\MailgunWebhooks;

use BinaryCats\MailgunWebhooks\Exceptions\WebhookFailed;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessMailgunWebhookJob extends ProcessWebhookJob
{
    /**
     * Name of the payload key to contain the type of event
     *
     * @var string
     */
    protected $key = 'event-data.event';

    /**
     * Handle the process
     *
     * @return void
     */
    public function handle()
    {
        $type = Arr::get($this->webhookCall, "payload.{$this->key}");

        if (! $type) {
            throw WebhookFailed::missingType($this->webhookCall);
        }

        event("mailgun-webhooks::{$type}", $this->webhookCall);

        $jobClass = $this->determineJobClass($type);

        if ($jobClass === '') {
            return;
        }

        if (! class_exists($jobClass)) {
            throw WebhookFailed::jobClassDoesNotExist($jobClass, $this->webhookCall);
        }

        dispatch(new $jobClass($this->webhookCall));
    }

    protected function determineJobClass(string $eventType): string
    {
        $jobConfigKey = str_replace('.', '_', $eventType);

        return config("mailgun-webhooks.jobs.{$jobConfigKey}", '');
    }
}
