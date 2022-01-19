<?php

namespace Tests;

use Spatie\WebhookClient\Models\WebhookCall;

class DummyJob
{
    /**
     * Bind the implementation.
     *
     * @var \Spatie\WebhookClient\Models\WebhookCall
     */
    public WebhookCall $webhookCall;

    /**
     * Create new Job.
     *
     * @param \Spatie\WebhookClient\Models\WebhookCall $webhookCall
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        cache()->put('dummyjob', $this->webhookCall);
    }
}
