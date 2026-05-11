<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\PayCodeService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class PayCodeServiceTest extends TestCase
{
    use CreatesMockClient;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('accessToken', 'cached-token');
        Config::set('expiresIn', time() + 300);
    }

    public function test_create_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new PayCodeService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = $this->validPayload();
        $service->create($payload);

        $this->assertSame('/api/v1/paycode', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_get_requires_a_reference(): void
    {
        $service = new PayCodeService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PayCode Reference must be provided.');

        $service->get('');
    }

    public function test_get_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new PayCodeService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->get('paycode-123');

        $this->assertSame('/api/v1/paycode/paycode-123', $history[0]['request']->getUri()->getPath());
    }

    public function test_get_unmasked_requires_a_reference(): void
    {
        $service = new PayCodeService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PayCode Reference must be provided.');

        $service->getUnMasked('');
    }

    public function test_get_unmasked_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new PayCodeService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->getUnMasked('paycode-123');

        $this->assertSame('/api/v1/paycode/paycode-123/authorize', $history[0]['request']->getUri()->getPath());
    }

    public function test_history_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new PayCodeService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $parameters = [
            'transactionReference' => 'txn-123',
            'beneficiaryName' => 'Jane Doe',
            'transactionStatus' => 'SUCCESS',
            'from' => 1715068800000,
            'to' => 1715155200000,
        ];

        $service->history($parameters);

        $this->assertSame('/api/v1/paycode', $history[0]['request']->getUri()->getPath());
        parse_str($history[0]['request']->getUri()->getQuery(), $actualParameters);
        $this->assertSame(array_map('strval', $parameters), $actualParameters);
    }

    public function test_delete_requires_a_reference(): void
    {
        $service = new PayCodeService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PayCode Reference must be provided.');

        $service->delete('');
    }

    public function test_delete_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new PayCodeService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $service->delete('paycode-123');

        $this->assertSame('DELETE', $history[0]['request']->getMethod());
        $this->assertSame('/api/v1/paycode/paycode-123', $history[0]['request']->getUri()->getPath());
    }

    private function validPayload(): array
    {
        return [
            'beneficiaryName' => 'Jane Doe',
            'amount' => 5000,
            'paycodeReference' => 'paycode-123',
            'expiryDate' => '2026-12-31',
            'clientId' => 'client-123',
        ];
    }
}
