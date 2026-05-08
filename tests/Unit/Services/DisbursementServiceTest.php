<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\DisbursementValidationFailure;
use Monnify\MonnifyLaravel\Services\DisbursementService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;

class DisbursementServiceTest extends TestCase
{
    use CreatesMockClient;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('accessToken', 'cached-token');
        Config::set('expiresIn', time() + 300);
    }

    protected function tearDown(): void
    {
        Config::set('accessToken', null);
        Config::set('expiresIn', null);

        parent::tearDown();
    }

    public function test_single_posts_the_expected_payload(): void
    {
        $payload = $this->validSingleTransferPayload();
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->single($payload);

        $this->assertSame('/api/v2/disbursements/single', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    public function test_single_can_mark_requests_as_async(): void
    {
        $payload = $this->validSingleTransferPayload();
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->single($payload, true);

        $body = json_decode((string) $history[0]['request']->getBody(), true);

        $this->assertTrue($body['async']);
    }

    public function test_bulk_posts_the_expected_payload(): void
    {
        $payload = $this->validBulkTransferPayload();
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->bulk($payload);

        $this->assertSame('/api/v2/disbursements/batch', $history[0]['request']->getUri()->getPath());
    }

    public function test_authorise_single_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->authoriseSingle([
            'reference' => 'ref-123',
            'authorizationCode' => '123456',
        ]);

        $this->assertSame('/api/v2/disbursements/single/validate-otp', $history[0]['request']->getUri()->getPath());
    }

    public function test_authorise_bulk_posts_the_expected_payload(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->authoriseBulk([
            'reference' => 'ref-123',
            'authorizationCode' => '123456',
        ]);

        $this->assertSame('/api/v2/disbursements/batch/validate-otp', $history[0]['request']->getUri()->getPath());
    }

    public function test_resend_otp_requires_a_reference(): void
    {
        $service = new DisbursementService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reference must be provided');

        $service->resendOTP('');
    }

    public function test_resend_otp_posts_the_reference_payload(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->resendOTP('ref-123');

        $this->assertSame('/api/v2/disbursements/single/resend-otp', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode(['reference' => 'ref-123']), (string) $history[0]['request']->getBody());
    }

    public function test_single_status_uses_the_summary_endpoint(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->singleStatus('ref-123');

        $this->assertSame('/api/v2/disbursements/single/summary', $history[0]['request']->getUri()->getPath());
        $this->assertSame('reference=ref-123', $history[0]['request']->getUri()->getQuery());
    }

    public function test_bulk_status_adds_pagination_parameters(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->bulkStatus('batch-123', 25, 2);

        $this->assertSame('/api/v2/disbursements/bulk/batch-123/transactions', $history[0]['request']->getUri()->getPath());
        $this->assertSame('pageSize=25&pageNo=2', $history[0]['request']->getUri()->getQuery());
    }

    public function test_all_uses_the_single_transactions_endpoint_by_default(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->all();

        $this->assertSame('/api/v2/disbursements/single/transactions', $history[0]['request']->getUri()->getPath());
    }

    public function test_all_can_switch_to_the_bulk_transactions_endpoint(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->all('bulk', 15, 1);

        $this->assertSame('/api/v2/disbursements/bulk/transactions', $history[0]['request']->getUri()->getPath());
        $this->assertSame('pageSize=15&pageNo=1', $history[0]['request']->getUri()->getQuery());
    }

    public function test_bulk_transaction_delegates_to_bulk_status(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->bulkTransaction('batch-123', 30, 4);

        $this->assertSame('/api/v2/disbursements/bulk/batch-123/transactions', $history[0]['request']->getUri()->getPath());
        $this->assertSame('pageSize=30&pageNo=4', $history[0]['request']->getUri()->getQuery());
    }

    public function test_search_adds_the_expected_query_parameters(): void
    {
        $history = [];
        $service = new DisbursementService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->search('1234567890', 50, 3);

        $this->assertSame('/api/v2/disbursements/search-transactions', $history[0]['request']->getUri()->getPath());
        $this->assertSame(
            'sourceAccountNumber=1234567890&pageSize=50&pageNo=3',
            $history[0]['request']->getUri()->getQuery()
        );
    }

    private function validSingleTransferPayload(): array
    {
        return [
            'amount' => 5000,
            'reference' => 'ref-123',
            'narration' => 'Vendor payout',
            'destinationBankCode' => '058',
            'destinationAccountNumber' => '0123456789',
            'currency' => 'NGN',
            'sourceAccountNumber' => '1234567890',
        ];
    }

    private function validBulkTransferPayload(): array
    {
        return [
            'title' => 'April payroll',
            'batchReference' => 'batch-123',
            'narration' => 'Salary',
            'sourceAccountNumber' => '1234567890',
            'notificationInterval' => 10,
            'onValidationFailure' => DisbursementValidationFailure::CONTINUE,
            'transactionList' => [
                [
                    'amount' => 5000,
                    'reference' => 'ref-123',
                    'narration' => 'Salary payment',
                    'destinationBankCode' => '058',
                    'destinationAccountNumber' => '0123456789',
                    'currency' => 'NGN',
                ],
            ],
        ];
    }
}
