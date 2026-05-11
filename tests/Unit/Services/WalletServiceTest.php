<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\WalletService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class WalletServiceTest extends TestCase
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

    public function test_create_posts_the_expected_payload(): void
    {
        $payload = [
            'walletReference' => 'wallet-123',
            'walletName' => 'Main Wallet',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
        ];
        $history = [];
        $service = new WalletService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->create($payload);

        $this->assertSame('/api/v1/disbursements/wallet', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_get_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new WalletService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->get('jane@example.com', 20, 2);

        $this->assertSame('/api/v1/disbursements/wallet', $history[0]['request']->getUri()->getPath());
        $this->assertSame('customerEmail=jane%40example.com&pageSize=20&pageNo=2', $history[0]['request']->getUri()->getQuery());
    }

    public function test_balance_requires_an_account_number(): void
    {
        $service = new WalletService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account Number must provided.');

        $service->balance('');
    }

    public function test_balance_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new WalletService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->balance('0123456789');

        $this->assertSame('/api/v1/disbursements/wallet/balance', $history[0]['request']->getUri()->getPath());
        $this->assertSame('accountNumber=0123456789', $history[0]['request']->getUri()->getQuery());
    }

    public function test_transactions_requires_an_account_number(): void
    {
        $service = new WalletService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account Number must provided.');

        $service->transactions('');
    }

    public function test_transactions_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new WalletService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->transactions('0123456789', 50, 3);

        $this->assertSame('/api/v1/disbursements/wallet/transactions', $history[0]['request']->getUri()->getPath());
        $this->assertSame('accountNumber=0123456789&pageSize=50&pageNo=3', $history[0]['request']->getUri()->getQuery());
    }
}
