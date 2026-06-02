<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Monnify\MonnifyLaravel\Services\RecurringPaymentService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RecurringPaymentServiceTest extends TestCase
{
    use CreatesMockClient;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put('monnify_access_token', 'cached-token', 300);
    }

    protected function tearDown(): void
    {
        Cache::forget('monnify_access_token');

        parent::tearDown();
    }

    #[Test]
    public function charge_card_token_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new RecurringPaymentService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = [
            'amount' => 5000,
            'cardToken' => 'card-token',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'paymentReference' => 'payment-123',
            'paymentDescription' => 'Recurring charge',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'apiKey' => 'api-key',
            'incomeSplitConfig' => [],
            'metaData' => [
                'ipAddress' => '127.0.0.1',
                'deviceType' => 'WEB',
            ],
        ];

        $service->chargeCardToken($payload);

        $this->assertSame('/api/v1/merchant/cards/charge-card-token', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }
}
