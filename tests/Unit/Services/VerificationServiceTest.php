<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\VerificationService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class VerificationServiceTest extends TestCase
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

    public function test_bank_account_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new VerificationService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->bankAccount('0123456789', '058');

        $this->assertSame('/api/v1/disbursements/account/validate', $history[0]['request']->getUri()->getPath());
        $this->assertSame('accountNumber=0123456789&bankCode=058', $history[0]['request']->getUri()->getQuery());
    }

    public function test_bvn_information_posts_the_expected_payload(): void
    {
        $payload = [
            'bvn' => '12345678901',
            'name' => 'Jane Doe',
            'dateOfBirth' => '1990-01-01',
            'mobileNo' => '08012345678',
        ];
        $history = [];
        $service = new VerificationService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->bvnInformation($payload);

        $this->assertSame('/api/v1/vas/bvn-details-match', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_match_bvn_and_bank_account_builds_the_expected_payload(): void
    {
        $history = [];
        $service = new VerificationService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->matchBVNAndBankAccount('12345678901', '058', '0123456789');

        $this->assertSame('/api/v1/vas/bvn-account-match', $history[0]['request']->getUri()->getPath());
        $this->assertSame(
            json_encode([
                'bvn' => '12345678901',
                'bankCode' => '058',
                'accountNumber' => '0123456789',
            ]),
            (string) $history[0]['request']->getBody()
        );
    }

    public function test_nin_requires_a_nin_value(): void
    {
        $service = new VerificationService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('NIN must be provided.');

        $service->nin('');
    }

    public function test_nin_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new VerificationService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->nin('12345678901');

        $this->assertSame('/api/v1/vas/nin-details', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode(['nin' => '12345678901']), (string) $history[0]['request']->getBody());
    }
}
