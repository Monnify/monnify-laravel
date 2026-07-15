<?php

namespace Monnify\MonnifyLaravel\Tests\Integration\Webhooks;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Monnify\MonnifyLaravel\Enums\WebhookEventType;
use Monnify\MonnifyLaravel\Events\MonnifyWebhookReceived;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MonnifyWebhookRouteEnabledTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('monnify.webhooks.route_enabled', true);
        $app['config']->set('monnify.webhooks.route_path', 'monnify/webhook');
        $app['config']->set('monnify.secret_key', 'test-secret-key');
    }

    #[Test]
    public function registers_the_webhook_route_when_enabled(): void
    {
        $this->assertTrue(Route::has('monnify.webhook'));
    }

    #[Test]
    public function dispatches_a_webhook_received_event_for_valid_payloads(): void
    {
        Event::fake();

        $body = json_encode([
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => ['paymentReference' => 'MNFY|123'],
        ]);

        $this->call('POST', '/monnify/webhook', [], [], [], [
            'CONTENT_TYPE'           => 'application/json',
            'HTTP_MONNIFY_SIGNATURE' => hash_hmac('sha512', $body, 'test-secret-key'),
        ], $body)
            ->assertOk()
            ->assertJson(['message' => 'Webhook received.']);

        Event::assertDispatched(MonnifyWebhookReceived::class, function (MonnifyWebhookReceived $event): bool {
            return $event->payload->is(WebhookEventType::SuccessfulTransaction)
                && $event->payload->eventData['paymentReference'] === 'MNFY|123';
        });
    }

    #[Test]
    public function rejects_invalid_signatures(): void
    {
        Event::fake();

        $this->postJson('/monnify/webhook', [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [],
        ], [
            'monnify-signature' => 'invalid',
        ])->assertUnauthorized()
            ->assertJson(['message' => 'The Monnify webhook signature is invalid.']);

        Event::assertNotDispatched(MonnifyWebhookReceived::class);
    }

    #[Test]
    public function rejects_invalid_payloads(): void
    {
        Event::fake();
        $body = json_encode(['eventType' => 'SUCCESSFUL_TRANSACTION']);

        $this->call('POST', '/monnify/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_MONNIFY_SIGNATURE' => hash_hmac('sha512', $body, 'test-secret-key'),
        ], $body)->assertBadRequest()
            ->assertJson(['message' => 'The webhook payload must contain a valid eventData object.']);

        Event::assertNotDispatched(MonnifyWebhookReceived::class);
    }

    #[Test]
    public function rejects_valid_json_that_is_not_an_object(): void
    {
        Event::fake();
        $body = json_encode('ping');

        $this->call('POST', '/monnify/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_MONNIFY_SIGNATURE' => hash_hmac('sha512', $body, 'test-secret-key'),
        ], $body)->assertBadRequest()
            ->assertJson(['message' => 'The webhook payload must be a valid JSON object.']);

        Event::assertNotDispatched(MonnifyWebhookReceived::class);
    }
}
