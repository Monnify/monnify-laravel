<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\RefundService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class RefundServiceTest extends TestCase
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

    public function test_initialise_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new RefundService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = [
            'transactionReference' => 'txn-123',
            'refundAmount' => 5000,
            'refundReference' => 'refund-123',
            'refundReason' => 'Customer request',
            'customerNote' => 'Refund approved',
            'destinationAccountNumber' => '0123456789',
            'destnationAccountBankCode' => '058',
        ];

        $service->initialise($payload);

        $this->assertSame('/api/v1/refunds/initiate-refund', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_all_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new RefundService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->all(25, 2);

        $this->assertSame('/api/v1/refunds', $history[0]['request']->getUri()->getPath());
        $this->assertSame('size=25&page=2', $history[0]['request']->getUri()->getQuery());
    }

    public function test_status_requires_a_refund_reference(): void
    {
        $service = new RefundService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Refund Reference must be provided.');

        $service->status('');
    }

    public function test_status_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new RefundService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->status('refund-123');

        $this->assertSame('/api/v1/refunds/refund-123', $history[0]['request']->getUri()->getPath());
    }
}
