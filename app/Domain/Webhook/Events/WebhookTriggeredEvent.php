<?php

namespace App\Domain\Webhook\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookTriggeredEvent
{
    use Dispatchable, SerializesModels;

    public $webhook;
    public $eventName;
    public $payload;

    public function __construct($webhook, $eventName, $payload)
    {
        $this->webhook = $webhook;
        $this->eventName = $eventName;
        $this->payload = $payload;
    }
}
