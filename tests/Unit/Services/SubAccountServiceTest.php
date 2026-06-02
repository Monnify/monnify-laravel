<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\SubAccountService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SubAccountServiceTest extends TestCase
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
    public function create_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new SubAccountService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = $this->validPayload();
        $service->create($payload);

        $this->assertSame('/api/v1/sub-accounts', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function all_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new SubAccountService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->all();

        $this->assertSame('/api/v1/sub-accounts', $history[0]['request']->getUri()->getPath());
        $this->assertSame('GET', $history[0]['request']->getMethod());
    }

    #[Test]
    public function update_uses_the_expected_endpoint_and_payload(): void
    {
        $history = [];
        $service = new SubAccountService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = [
            ...$this->validSinglePayload(),
            'subAccountCode' => 'sub-123',
        ];

        $service->update($payload);

        $this->assertSame('/api/v1/sub-accounts', $history[0]['request']->getUri()->getPath());
        $this->assertSame('PUT', $history[0]['request']->getMethod());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function delete_requires_a_sub_account_code(): void
    {
        $service = new SubAccountService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sub Account Code must be provided');

        $service->delete('');
    }

    #[Test]
    public function delete_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new SubAccountService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $service->delete('sub-123');

        $this->assertSame('DELETE', $history[0]['request']->getMethod());
        $this->assertSame('/api/v1/sub-accounts/sub-123', $history[0]['request']->getUri()->getPath());
    }

    private function validPayload(): array
    {
        return [[
            'currencyCode' => 'NGN',
            'accountNumber' => '0123456789',
            'bankCode' => '058',
            'email' => 'jane@example.com',
            'defaultSplitPercentage' => 20,
        ]];
    }

    private function validSinglePayload(): array
    {
        return $this->validPayload()[0];
    }
}
