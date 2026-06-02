<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\LimitProfileService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LimitProfileServiceTest extends TestCase
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
    public function all_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new LimitProfileService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->all();

        $this->assertSame('/api/v1/limit-profile/', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function create_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new LimitProfileService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = $this->validLimitProfilePayload();
        $service->create($payload);

        $this->assertSame('/api/v1/limit-profile/', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function update_requires_a_limit_profile_code(): void
    {
        $service = new LimitProfileService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit Profile Code must be provided.');

        $service->update('', $this->validLimitProfilePayload());
    }

    #[Test]
    public function update_uses_the_expected_endpoint_and_payload(): void
    {
        $history = [];
        $service = new LimitProfileService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = $this->validLimitProfilePayload();
        $service->update('limit-123', $payload);

        $this->assertSame('/api/v1/limit-profile/limit-123', $history[0]['request']->getUri()->getPath());
        $this->assertSame('PUT', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function reserve_account_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new LimitProfileService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = $this->validReserveAccountPayload();
        $service->reserveAccount($payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/limit', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function update_reserve_account_uses_the_expected_payload(): void
    {
        $history = [];
        $service = new LimitProfileService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $service->updateReserveAccount('acct-ref', 'limit-123');

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/limit', $history[0]['request']->getUri()->getPath());
        $this->assertSame('PUT', $history[0]['request']->getMethod());
        $this->assertSame(
            json_encode([
                'accountReference' => 'acct-ref',
                'limitProfileCode' => 'limit-123',
            ]),
            (string) $history[0]['request']->getBody()
        );
    }

    private function validLimitProfilePayload(): array
    {
        return [
            'limitProfileName' => 'Tier 1',
            'singleTransactionValue' => 50000,
            'dailyTransactionValue' => 250000,
            'dailyTransactionVolume' => 10,
        ];
    }

    private function validReserveAccountPayload(): array
    {
        return [
            'accountReference' => 'acct-ref',
            'limitProfileCode' => 'limit-123',
            'accountName' => 'Reserved account',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'customerEmail' => 'jane@example.com',
            'incomeSplitConfig' => [],
        ];
    }
}
