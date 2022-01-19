<?php

namespace Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\Models\WebhookCall;

class IntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Route::mailgunWebhooks('mailgun-webhooks');
        Route::mailgunWebhooks('mailgun-webhooks/{configKey}');

        config(['mailgun-webhooks.jobs' => ['my_type' => DummyJob::class]]);

        cache()->clear();
    }

    /** @test */
    public function it_can_handle_a_valid_request()
    {
        $payload = [
            'event-data' => [
                'event' => 'my.type',
                'key' => 'value',
            ],
        ];

        Arr::set($payload, 'signature', $this->determineMailgunSignature($payload));

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertSuccessful();

        $this->assertCount(1, WebhookCall::get());

        $webhookCall = WebhookCall::first();

        $this->assertEquals('my.type', $webhookCall->payload['event-data']['event']);
        $this->assertEquals($payload, $webhookCall->payload);
        $this->assertNull($webhookCall->exception);

        Event::assertDispatched('mailgun-webhooks::my.type', function ($event, $eventPayload) use ($webhookCall) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);
            $this->assertEquals($webhookCall->id, $eventPayload->id);

            return true;
        });

        $this->assertEquals($webhookCall->id, cache('dummyjob')->id);
    }

    /** @test */
    public function it_can_handle_a_valid_request_even_with_wrong_case()
    {
        $payload = [
            'event-data' => [
                'event' => 'mY.tYpE',
                'key' => 'value',
            ],
        ];

        Arr::set($payload, 'signature', $this->determineMailgunSignature($payload));

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertSuccessful();

        $this->assertCount(1, WebhookCall::get());

        $webhookCall = WebhookCall::first();

        $this->assertNull($webhookCall->exception);

        Event::assertDispatched('mailgun-webhooks::my.type', function ($event, $eventPayload) use ($webhookCall) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);
            $this->assertEquals($webhookCall->id, $eventPayload->id);

            return true;
        });

        $this->assertEquals($webhookCall->id, cache('dummyjob')->id);
    }

    public function in_will_ignore_empty_reququest()
    {
        $payload = [];

        Arr::set($payload, 'signature', $this->determineMailgunSignature($payload));

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertStatus(422);

        $this->assertCount(0, WebhookCall::get());

        Event::assertNotDispatched('mailgun-webhooks::my.type');

        $this->assertNull(cache('dummyjob'));
    }

    public function in_will_ignore_unsinged_reququest()
    {
        $payload = [
            'event-data' => [
                'event' => 'my.type',
                'key' => 'value',
            ],
        ];

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertStatus(422);

        $this->assertCount(0, WebhookCall::get());

        Event::assertNotDispatched('mailgun-webhooks::my.type');

        $this->assertNull(cache('dummyjob'));
    }

    /** @test */
    public function a_request_with_an_invalid_signature_wont_be_logged()
    {
        $payload = [
            'event-data' => [
                'event' => 'my.type',
                'key' => 'value',
            ],
        ];

        Arr::set($payload, 'signature', 'invalid_signature');

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertStatus(422);

        $this->assertCount(0, WebhookCall::get());

        Event::assertNotDispatched('mailgun-webhooks::my.type');

        $this->assertNull(cache('dummyjob'));
    }

    /** @test */
    public function a_request_with_an_invalid_payload_will_be_logged_but_events_and_jobs_will_not_be_dispatched()
    {
        $payload = ['invalid_payload'];

        $signature = $this->determineMailgunSignature($payload);

        Arr::set($payload, 'signature', $signature);

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertStatus(400);

        $this->assertCount(1, WebhookCall::get());

        $webhookCall = WebhookCall::first();

        $this->assertFalse(isset($webhookCall->payload['event-data']['event']));
        $this->assertEquals([
            'invalid_payload',
            'signature' => $signature,
        ], $webhookCall->payload);

        $this->assertEquals('Webhook call id `1` did not contain a type. Valid Mailgun webhook calls should always contain a type.', $webhookCall->exception['message']);

        Event::assertNotDispatched('mailgun-webhooks::my.type');

        $this->assertNull(cache('dummyjob'));
    }

    /** @test * */
    public function a_request_with_a_config_key_will_use_the_correct_signing_secret()
    {
        config()->set('mailgun-webhooks.signing_secret', 'secret1');
        config()->set('mailgun-webhooks.signing_secret_somekey', 'secret2');

        $payload = [
            'event-data' => [
                'event' => 'my.type',
                'key' => 'value',
            ],
        ];

        Arr::set($payload, 'signature', $this->determineMailgunSignature($payload, 'somekey'));

        $this
            ->postJson('mailgun-webhooks/somekey', $payload)
            ->assertSuccessful();
    }

    /** @test */
    public function an_invalid_signature_value_generates_a_500_error()
    {
        $payload = [
            'event-data' => [
                'event' => 'my.type',
                'key' => 'value',
            ],
        ];

        Arr::set($payload, 'signature', [
            'timestamp' => time(),
            'token' => 'some token',
            'signature' => 'invalid_signature',
        ]);

        $this
            ->postJson('mailgun-webhooks', $payload)
            ->assertStatus(500);

        $this->assertCount(0, WebhookCall::get());

        Event::assertNotDispatched('mailgun-webhooks::my.type');

        $this->assertNull(cache('dummyjob'));
    }
}
