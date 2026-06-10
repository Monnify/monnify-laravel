<?php

namespace Monnify\MonnifyLaravel\Webhooks;

use Illuminate\Http\Request;
use Monnify\MonnifyLaravel\Exceptions\InvalidWebhookSignatureException;

class WebhookSignatureVerifier
{
    private const SIGNATURE_HEADER = 'monnify-signature';

    public function verify(Request $request): void
    {
        $secretKey = config('monnify.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw InvalidWebhookSignatureException::missingSecret();
        }

        $signature = $request->headers->get(self::SIGNATURE_HEADER);

        if (! is_string($signature) || $signature === '') {
            throw InvalidWebhookSignatureException::missingSignature();
        }

        $expectedSignature = hash_hmac('sha512', $request->getContent(), $secretKey);

        if (! hash_equals($expectedSignature, $signature)) {
            throw InvalidWebhookSignatureException::invalidSignature();
        }
    }
}
