<?php

namespace Monnify\MonnifyLaravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use JsonException;
use Monnify\MonnifyLaravel\Events\MonnifyWebhookReceived;
use Monnify\MonnifyLaravel\Exceptions\InvalidWebhookSignatureException;
use Monnify\MonnifyLaravel\Webhooks\WebhookPayload;
use Monnify\MonnifyLaravel\Webhooks\WebhookSignatureVerifier;

class MonnifyWebhookController
{
    public function __construct(private readonly WebhookSignatureVerifier $signatureVerifier)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->signatureVerifier->verify($request);
        } catch (InvalidWebhookSignatureException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        try {
            $decodedPayload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($decodedPayload)) {
                throw new InvalidArgumentException('The webhook payload must be a valid JSON object.');
            }

            $payload = WebhookPayload::fromArray($decodedPayload);
        } catch (InvalidArgumentException|JsonException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        event(new MonnifyWebhookReceived($payload));

        return response()->json(['message' => 'Webhook received.']);
    }
}
