<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Monnify\MonnifyLaravel\Services\OtherService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OtherServiceTest extends TestCase
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
    public function banks_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new OtherService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->banks();

        $this->assertSame('/api/v1/banks', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function banks_with_ussd_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new OtherService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->banksWithUSSD();

        $this->assertSame('/api/v1/sdk/transactions/banks', $history[0]['request']->getUri()->getPath());
    }
}
