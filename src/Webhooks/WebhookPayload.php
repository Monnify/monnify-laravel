<?php

namespace Monnify\MonnifyLaravel\Webhooks;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\WebhookEventType;

class WebhookPayload
{
    /**
     * @param array<string, mixed> $eventData
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $eventType,
        public readonly array $eventData,
        /** The full raw payload array, including any undocumented top-level fields. */
        public readonly array $raw,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (! isset($payload['eventType']) || ! is_string($payload['eventType']) || $payload['eventType'] === '') {
            throw new InvalidArgumentException('The webhook payload must contain a valid eventType.');
        }

        if (! isset($payload['eventData']) || ! is_array($payload['eventData'])) {
            throw new InvalidArgumentException('The webhook payload must contain a valid eventData object.');
        }

        return new self($payload['eventType'], $payload['eventData'], $payload);
    }

    public function knownEventType(): ?WebhookEventType
    {
        return WebhookEventType::tryFrom($this->eventType);
    }

    public function is(WebhookEventType $eventType): bool
    {
        return $this->eventType === $eventType->value;
    }
}
