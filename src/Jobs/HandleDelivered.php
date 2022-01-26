<?php

namespace BinaryCats\MailgunWebhooks\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleDelivered
{
    use Dispatchable, SerializesModels;

    /**
     * Bind the implementation.
     *
     * @var \Spatie\WebhookClient\Models\WebhookCall
     */
    protected WebhookCall $webhookCall;

    /**
     * Create new Job.
     *
     * @param  \Spatie\WebhookClient\Models\WebhookCall  $webhookCall
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    }
}
