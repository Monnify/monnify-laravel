<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Monnify\MonnifyLaravel\Services\OtherService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class OtherServiceTest extends TestCase
{
    use CreatesMockClient;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('accessToken', 'cached-token');
        Config::set('expiresIn', time() + 300);
    }

    public function test_banks_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new OtherService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->banks();

        $this->assertSame('/api/v1/banks', $history[0]['request']->getUri()->getPath());
    }

    public function test_banks_with_ussd_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new OtherService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->banksWithUSSD();

        $this->assertSame('/api/v1/sdk/transactions/banks', $history[0]['request']->getUri()->getPath());
    }
}
