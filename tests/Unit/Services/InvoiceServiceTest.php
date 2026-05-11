<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\InvoiceService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class InvoiceServiceTest extends TestCase
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
        $payload = $this->validInvoicePayload();
        $history = [];
        $service = new InvoiceService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->create($payload);

        $this->assertSame('/api/v1/invoice/create', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_get_requires_an_invoice_reference(): void
    {
        $service = new InvoiceService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice Reference must be provided.');

        $service->get('');
    }

    public function test_get_uses_the_invoice_details_endpoint(): void
    {
        $history = [];
        $service = new InvoiceService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->get('inv-123');

        $this->assertSame('/api/v1/invoice/inv-123/details', $history[0]['request']->getUri()->getPath());
    }

    public function test_all_uses_the_invoice_listing_endpoint(): void
    {
        $history = [];
        $service = new InvoiceService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->all();

        $this->assertSame('/api/v1/invoice/all', $history[0]['request']->getUri()->getPath());
    }

    public function test_cancel_requires_an_invoice_reference(): void
    {
        $service = new InvoiceService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice Reference must be provided.');

        $service->cancel('');
    }

    public function test_cancel_uses_the_invoice_cancel_endpoint(): void
    {
        $history = [];
        $service = new InvoiceService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->cancel('inv-123');

        $this->assertSame('/api/v1/invoice/inv-123/cancel', $history[0]['request']->getUri()->getPath());
        $this->assertSame('DELETE', $history[0]['request']->getMethod());
    }

    public function test_attach_reserved_account_posts_the_expected_payload(): void
    {
        $payload = $this->validInvoicePayload();
        $history = [];
        $service = new InvoiceService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->attachReservedAccount($payload);

        $this->assertSame('/api/v1/invoice/create', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    private function validInvoicePayload(): array
    {
        return [
            'amount' => 5000,
            'currencyCode' => 'NGN',
            'invoiceReference' => 'inv-123',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'contractCode' => 'contract-123',
            'description' => 'Invoice payment',
            'expiryDate' => '2026-12-31',
        ];
    }
}
