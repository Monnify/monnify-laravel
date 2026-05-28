<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\DirectDebitService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DirectDebitServiceTest extends TestCase
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
        $service = new DirectDebitService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = $this->validMandatePayload();
        $result = $service->create($payload);

        $this->assertSame(200, $result['status']);
        $this->assertSame('/api/v1/direct-debit/mandate/create', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function get_requires_a_mandate_reference(): void
    {
        $service = new DirectDebitService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mandate Reference must be provided.');

        $service->get('');
    }

    #[Test]
    public function get_uses_the_expected_query_string(): void
    {
        $history = [];
        $service = new DirectDebitService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->get('mandate-123');

        $this->assertSame('/api/v1/direct-debit/mandate/', $history[0]['request']->getUri()->getPath());
        $this->assertSame('mandateReferences=mandate-123', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function debit_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new DirectDebitService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = [
            'paymentReference' => 'payment-123',
            'mandateCode' => 'mandate-code',
            'debitAmount' => 5000,
            'narration' => 'Subscription charge',
            'customerEmail' => 'jane@example.com',
        ];

        $service->debit($payload);

        $this->assertSame('/api/v1/direct-debit/mandate/debit', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function debit_accepts_income_split_config_payloads(): void
    {
        $history = [];
        $service = new DirectDebitService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $payload = [
            'paymentReference' => 'payment-123',
            'mandateCode' => 'mandate-code',
            'debitAmount' => 5000,
            'narration' => 'Subscription charge',
            'customerEmail' => 'jane@example.com',
            'incomeSplitConfig' => [
                [
                    'subAccountCode' => 'SUB_123',
                    'feeBearer' => true,
                    'splitPercentage' => 20,
                ],
            ],
        ];

        $service->debit($payload);

        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function status_requires_a_payment_reference(): void
    {
        $service = new DirectDebitService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment Reference must be provided.');

        $service->status('');
    }

    #[Test]
    public function status_uses_the_expected_query_string(): void
    {
        $history = [];
        $service = new DirectDebitService($this->makeClient([
            new Response(200, [], json_encode(['responseBody' => []])),
        ], $history));

        $service->status('payment-123');

        $this->assertSame('/api/v1/direct-debit/mandate/debit-status', $history[0]['request']->getUri()->getPath());
        $this->assertSame('paymentReference=payment-123', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function cancel_requires_a_mandate_code(): void
    {
        $service = new DirectDebitService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mandate Code must be provided.');

        $service->cancel('');
    }

    #[Test]
    public function cancel_uses_the_expected_endpoint(): void
    {
        $history = [];
        $service = new DirectDebitService($this->makeClient([
            new Response(200, [], json_encode(['requestSuccessful' => true])),
        ], $history));

        $service->cancel('mandate-code');

        $this->assertSame('PATCH', $history[0]['request']->getMethod());
        $this->assertSame('/api/v1/direct-debit/mandate/cancel-mandate/mandate-code', $history[0]['request']->getUri()->getPath());
    }

    private function validMandatePayload(): array
    {
        return [
            'contractCode' => 'contract-123',
            'mandateReference' => 'mandate-123',
            'autoRenew' => true,
            'customerName' => 'Jane Doe',
            'customerEmailAddress' => 'jane@example.com',
            'customerPhoneNumber' => '08012345678',
            'customerAddress' => '12 Broad Street',
            'customerAccountNumber' => '0123456789',
            'customerAccountBankCode' => '058',
            'mandateDescription' => 'Monthly subscription',
            'mandateStartDate' => '2026-05-01',
            'mandateEndDate' => '2026-12-31',
            'mandateAmount' => 5000,
            'debitAmount' => 5000,
            'customerCancellation' => false,
        ];
    }
}
