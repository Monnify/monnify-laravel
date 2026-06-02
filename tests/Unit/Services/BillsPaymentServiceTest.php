<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Monnify\MonnifyLaravel\Services\BillsPaymentService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BillsPaymentServiceTest extends TestCase
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
    public function categories_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->categories();

        $this->assertSame('/api/v1/vas/bills-payment/biller-categories', $history[0]['request']->getUri()->getPath());
        $this->assertSame('GET', $history[0]['request']->getMethod());
    }

    #[Test]
    public function categories_sends_pagination_parameters(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->categories(20, 2);

        $this->assertSame('size=20&page=2', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function billers_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->billers();

        $this->assertSame('/api/v1/vas/bills-payment/billers', $history[0]['request']->getUri()->getPath());
        $this->assertSame('GET', $history[0]['request']->getMethod());
    }

    #[Test]
    public function billers_includes_category_code_when_provided(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->billers('ELECTRICITY');

        $query = $history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('category_code=ELECTRICITY', $query);
    }

    #[Test]
    public function billers_omits_category_code_when_not_provided(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->billers();

        $this->assertStringNotContainsString('category_code', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function products_requires_a_biller_code(): void
    {
        $service = new BillsPaymentService($this->makeClient([]));

        $this->expectException(\InvalidArgumentException::class);

        $service->products('');
    }

    #[Test]
    public function products_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->products('BILLER-001');

        $this->assertSame('/api/v1/vas/bills-payment/biller-products', $history[0]['request']->getUri()->getPath());
        $this->assertStringContainsString('biller_code=BILLER-001', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function validate_customer_posts_the_expected_payload(): void
    {
        $payload = ['productCode' => 'PROD-001', 'customerId' => 'CUST-123'];
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->validateCustomer($payload);

        $this->assertSame('/api/v1/vas/bills-payment/validate-customer', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function vend_posts_the_expected_payload(): void
    {
        $payload = $this->validVendPayload();
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->vend($payload);

        $this->assertSame('/api/v1/vas/bills-payment/vend', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function requery_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new BillsPaymentService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->requery('vend-ref-123');

        $this->assertSame('/api/v1/vas/bills-payment/requery', $history[0]['request']->getUri()->getPath());
        $this->assertSame('GET', $history[0]['request']->getMethod());
        $this->assertSame('vendReference=vend-ref-123', $history[0]['request']->getUri()->getQuery());
    }

    private function validVendPayload(): array
    {
        return [
            'productCode'   => 'PROD-001',
            'customerId'    => 'CUST-123',
            'vendAmount'    => 1000,
            'vendReference' => 'vend-ref-123',
        ];
    }
}
