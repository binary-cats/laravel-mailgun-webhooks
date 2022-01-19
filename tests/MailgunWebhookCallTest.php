<?php

namespace Tests;

use BinaryCats\MailgunWebhooks\ProcessMailgunWebhookJob;
use Illuminate\Support\Facades\Event;
use Spatie\WebhookClient\Models\WebhookCall;

class MailgunWebhookCallTest extends TestCase
{
    /** @var \BinaryCats\MailgunWebhooks\ProcessMailgunWebhookJob */
    public $processMailgunWebhookJob;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config(['mailgun-webhooks.jobs' => ['my_type' => DummyJob::class]]);

        $this->webhookCall = WebhookCall::create([
            'name' => 'mailgun',
            'payload' => [
                'event-data' => [
                    'event' => 'my.type',
                    'key' => 'value',
                ],
            ],
        ]);

        $this->processMailgunWebhookJob = new ProcessMailgunWebhookJob($this->webhookCall);
    }

    /** @test */
    public function it_will_fire_off_the_configured_job()
    {
        $this->processMailgunWebhookJob->handle();

        $this->assertEquals($this->webhookCall->id, cache('dummyjob')->id);
    }

    /** @test */
    public function it_will_not_dispatch_a_job_for_another_type()
    {
        config(['mailgun-webhooks.jobs' => ['another_type' => DummyJob::class]]);

        $this->processMailgunWebhookJob->handle();

        $this->assertNull(cache('dummyjob'));
    }

    /** @test */
    public function it_will_not_dispatch_jobs_when_no_jobs_are_configured()
    {
        config(['mailgun-webhooks.jobs' => []]);

        $this->processMailgunWebhookJob->handle();

        $this->assertNull(cache('dummyjob'));
    }

    /** @test */
    public function it_will_dispatch_events_even_when_no_corresponding_job_is_configured()
    {
        config(['mailgun-webhooks.jobs' => ['another_type' => DummyJob::class]]);

        $this->processMailgunWebhookJob->handle();

        $webhookCall = $this->webhookCall;

        Event::assertDispatched("mailgun-webhooks::{$webhookCall->payload['event-data']['event']}", function ($event, $eventPayload) use ($webhookCall) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);
            $this->assertEquals($webhookCall->id, $eventPayload->id);

            return true;
        });

        $this->assertNull(cache('dummyjob'));
    }
}
