<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\SettlementService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class SettlementServiceTest extends TestCase
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

    public function test_transactions_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new SettlementService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->transactions('settlement-123', 25, 2);

        $this->assertSame('/api/v1/transactions/find-by-settlement-reference', $history[0]['request']->getUri()->getPath());
        $this->assertSame('reference=settlement-123&size=25&page=2', $history[0]['request']->getUri()->getQuery());
    }

    public function test_get_by_transaction_requires_a_transaction_reference(): void
    {
        $service = new SettlementService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction Reference must be provided.');

        $service->getByTransaction('');
    }

    public function test_get_by_transaction_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new SettlementService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->getByTransaction('txn-123');

        $this->assertSame('/api/v1/settlement-detail', $history[0]['request']->getUri()->getPath());
        $this->assertSame('transactionReference=txn-123', $history[0]['request']->getUri()->getQuery());
    }
}
