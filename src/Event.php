<?php

namespace BinaryCats\MailgunWebhooks;

use BinaryCats\MailgunWebhooks\Contracts\WebhookEvent;
use Illuminate\Support\Arr;

class Event implements WebhookEvent
{
    /**
     * Attributes from the event
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Create new Event
     *
     * @param array $attributes
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Construct the event
     *
     * @return Event
     */
    public static function constructFrom($data) : Event
    {
        return new static($data);
    }
}
