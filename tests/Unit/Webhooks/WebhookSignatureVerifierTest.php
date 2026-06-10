<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Webhooks;

use Illuminate\Http\Request;
use Monnify\MonnifyLaravel\Exceptions\InvalidWebhookSignatureException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Webhooks\WebhookSignatureVerifier;
use PHPUnit\Framework\Attributes\Test;

class WebhookSignatureVerifierTest extends TestCase
{
    private WebhookSignatureVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifier = new WebhookSignatureVerifier();
        $this->app['config']->set('monnify.secret_key', 'test-secret-key');
    }

    #[Test]
    public function accepts_a_valid_signature(): void
    {
        $body = json_encode(['eventType' => 'SUCCESSFUL_TRANSACTION']);
        $signature = hash_hmac('sha512', $body, 'test-secret-key');

        $this->verifier->verify($this->requestWithBody($body, $signature));

        $this->assertTrue(true);
    }

    #[Test]
    public function rejects_an_invalid_signature(): void
    {
        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage('invalid');

        $this->verifier->verify($this->requestWithBody('{"eventType":"SUCCESSFUL_TRANSACTION"}', 'invalid'));
    }

    #[Test]
    public function rejects_a_missing_signature(): void
    {
        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage('missing');

        $this->verifier->verify($this->requestWithBody('{"eventType":"SUCCESSFUL_TRANSACTION"}'));
    }

    #[Test]
    public function rejects_a_missing_secret_key(): void
    {
        $this->app['config']->set('monnify.secret_key', null);

        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage('secret key');

        $this->verifier->verify($this->requestWithBody('{"eventType":"SUCCESSFUL_TRANSACTION"}', 'signature'));
    }

    private function requestWithBody(string $body, ?string $signature = null): Request
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];

        if ($signature !== null) {
            $headers['HTTP_MONNIFY_SIGNATURE'] = $signature;
        }

        return Request::create(
            '/monnify/webhook',
            'POST',
            [],
            [],
            [],
            $headers,
            $body
        );
    }
}
