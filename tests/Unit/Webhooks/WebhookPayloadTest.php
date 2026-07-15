<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Webhooks;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\WebhookEventType;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Webhooks\WebhookPayload;
use PHPUnit\Framework\Attributes\Test;

class WebhookPayloadTest extends TestCase
{
    #[Test]
    public function wraps_known_webhook_events(): void
    {
        $payload = WebhookPayload::fromArray([
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => ['paymentReference' => 'MNFY|123'],
        ]);

        $this->assertSame('SUCCESSFUL_TRANSACTION', $payload->eventType);
        $this->assertSame(['paymentReference' => 'MNFY|123'], $payload->eventData);
        $this->assertSame(WebhookEventType::SuccessfulTransaction, $payload->knownEventType());
        $this->assertTrue($payload->is(WebhookEventType::SuccessfulTransaction));
    }

    #[Test]
    public function keeps_unknown_webhook_events_receivable(): void
    {
        $payload = WebhookPayload::fromArray([
            'eventType' => 'NEW_MONNIFY_EVENT',
            'eventData' => ['reference' => 'REF-123'],
        ]);

        $this->assertSame('NEW_MONNIFY_EVENT', $payload->eventType);
        $this->assertNull($payload->knownEventType());
    }

    #[Test]
    public function requires_a_valid_event_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('eventType');

        WebhookPayload::fromArray(['eventData' => []]);
    }

    #[Test]
    public function requires_event_data_to_be_an_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('eventData');

        WebhookPayload::fromArray([
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => 'invalid',
        ]);
    }
}
