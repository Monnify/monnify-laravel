<?php

namespace Monnify\MonnifyLaravel\Events;

use Monnify\MonnifyLaravel\Webhooks\WebhookPayload;

class MonnifyWebhookReceived
{
    public function __construct(public readonly WebhookPayload $payload)
    {
    }
}
