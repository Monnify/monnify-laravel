<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\CustomerReservedAccountService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class CustomerReservedAccountServiceTest extends TestCase
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

    public function test_create_general_account_posts_the_expected_payload(): void
    {
        $payload = $this->validGeneralAccountPayload();
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->createGeneralAccount($payload);

        $this->assertSame('/api/v2/bank-transfer/reserved-accounts', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_create_invoice_account_posts_the_expected_payload(): void
    {
        $payload = $this->validInvoiceAccountPayload();
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->createInvoiceAccount($payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts', $history[0]['request']->getUri()->getPath());
    }

    public function test_get_requires_an_account_reference(): void
    {
        $service = new CustomerReservedAccountService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account Reference must be provided');

        $service->get('');
    }

    public function test_get_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->get('acct-ref');

        $this->assertSame('/api/v2/bank-transfer/reserved-accounts/acct-ref', $history[0]['request']->getUri()->getPath());
    }

    public function test_add_linked_accounts_uses_the_expected_endpoint_and_payload(): void
    {
        $payload = [
            'getAllAvailableBanks' => false,
            'preferredBanks' => ['058', '011'],
        ];
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->addLinkedAccounts('acct-ref', $payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/add-linked-accounts/acct-ref', $history[0]['request']->getUri()->getPath());
        $this->assertSame('PUT', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_update_bvn_uses_the_expected_payload(): void
    {
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->updateBVN('acct-ref', '12345678901');

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/update-customer-bvn/acct-ref', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode(['bvn' => '12345678901']), (string) $history[0]['request']->getBody());
    }

    public function test_deallocate_account_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->deallocateAccount('acct-ref');

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/reference/acct-ref', $history[0]['request']->getUri()->getPath());
        $this->assertSame('DELETE', $history[0]['request']->getMethod());
    }

    public function test_transactions_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->transactions('acct-ref', ['page' => 2, 'size' => 25]);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/transactions', $history[0]['request']->getUri()->getPath());
        $this->assertSame('accountReference=acct-ref&page=2&size=25', $history[0]['request']->getUri()->getQuery());
    }

    public function test_update_kyc_info_uses_the_expected_endpoint_and_payload(): void
    {
        $payload = ['bvn' => '12345678901'];
        $history = [];
        $service = new CustomerReservedAccountService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->updateKYCInfo('acct-ref', $payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/acct-ref/kyc-info', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    private function validGeneralAccountPayload(): array
    {
        return [
            'accountReference' => 'acct-ref',
            'accountName' => 'Main account',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'customerEmail' => 'jane@example.com',
            'customerName' => 'Jane Doe',
            'getAllAvailableBanks' => true,
            'restrictPaymentSource' => false,
            'bvn' => '12345678901',
        ];
    }

    private function validInvoiceAccountPayload(): array
    {
        return [
            'contractCode' => 'contract-123',
            'accountName' => 'Invoice account',
            'currencyCode' => 'NGN',
            'accountReference' => 'acct-ref',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
        ];
    }
}
