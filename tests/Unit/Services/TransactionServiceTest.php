<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Services\TransactionService;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TransactionServiceTest extends TestCase
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
    public function initialise_posts_the_expected_payload(): void
    {
        $payload = $this->validInitializePayload();
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $result = $service->initialise($payload);

        $this->assertSame(200, $result['status']);
        $this->assertSame('/api/v1/merchant/transactions/init-transaction', $history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $history[0]['request']->getMethod());

        $sentPayload = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('plugin-laravel', $sentPayload['metaData']['referrer']);
    }

    #[Test]
    public function initialise_automatically_injects_plugin_referrer(): void
    {
        $payload = $this->validInitializePayload();
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->initialise($payload);

        $sentPayload = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertArrayHasKey('metaData', $sentPayload);
        $this->assertSame('plugin-laravel', $sentPayload['metaData']['referrer']);
    }

    #[Test]
    public function initialise_merges_user_metadata_with_referrer(): void
    {
        $payload = $this->validInitializePayload();
        $payload['metaData'] = ['userId' => 'user-123', 'source' => 'mobile-app'];
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->initialise($payload);

        $sentPayload = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('plugin-laravel', $sentPayload['metaData']['referrer']);
        $this->assertSame('user-123', $sentPayload['metaData']['userId']);
        $this->assertSame('mobile-app', $sentPayload['metaData']['source']);
    }

    #[Test]
    public function pay_with_bank_transfer_posts_the_expected_payload(): void
    {
        $payload = ['transactionReference' => 'txn-ref', 'bankCode' => '058'];
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->payWithBankTransfer($payload);

        $this->assertSame('/api/v1/merchant/bank-transfer/init-payment', $history[0]['request']->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function charge_card_posts_the_expected_payload(): void
    {
        $payload = [
            'transactionReference' => 'txn-ref',
            'collectionChannel' => 'API_NOTIFICATION',
            'card' => [
                'number' => '4242424242424242',
                'pin' => '1234',
                'expiryMonth' => '09',
                'expiryYear' => '29',
                'cvv' => '123',
            ],
        ];
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->chargeCard($payload);

        $this->assertSame('/api/v1/merchant/cards/charge', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function authorize_otp_posts_the_expected_payload(): void
    {
        $payload = [
            'transactionReference' => 'txn-ref',
            'collectionChannel' => 'API_NOTIFICATION',
            'tokenId' => 'token-id',
            'token' => '123456',
        ];
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->authorizeOTP($payload);

        $this->assertSame('/api/v1/merchant/cards/otp/authorize', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function authorize_three_ds_card_posts_the_expected_payload(): void
    {
        $payload = [
            'transactionReference' => 'txn-ref',
            'collectionChannel' => 'API_NOTIFICATION',
            'card' => [
                'number' => '4242424242424242',
                'pin' => '1234',
                'expiryMonth' => '09',
                'expiryYear' => '29',
                'cvv' => '123',
            ],
            'apiKey' => 'api-key',
            'deviceInformation' => [
                'httpBrowserLanguage' => 'en-US',
                'httpBrowserJavaEnabled' => false,
                'httpBrowserJavaScriptEnabled' => true,
                'httpBrowserColorDepth' => '24',
                'httpBrowserScreenHeight' => '1080',
                'httpBrowserScreenWidth' => '1920',
                'httpBrowserTimeDifference' => '-60',
                'userAgentBrowserValue' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ],
        ];
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->authorizeThreeDSCard($payload);

        $this->assertSame('/api/v1/sdk/cards/secure-3d/authorize', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function all_adds_query_parameters_to_the_search_request(): void
    {
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->all([
            'page' => 2,
            'size' => 20,
            'paymentReference' => 'pay-ref',
        ]);

        $this->assertSame('/api/v1/transactions/search', $history[0]['request']->getUri()->getPath());
        $this->assertSame('page=2&size=20&paymentReference=pay-ref', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function status_requires_a_transaction_reference(): void
    {
        $service = new TransactionService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction Reference must be provided');

        $service->status('');
    }

    #[Test]
    public function status_uses_the_transaction_status_endpoint(): void
    {
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->status('txn-ref');

        $this->assertSame('/api/v2/transactions/txn-ref', $history[0]['request']->getUri()->getPath());
    }

    #[Test]
    public function status_by_reference_supports_transaction_references(): void
    {
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->statusByReference('txn-ref');

        $this->assertSame('/api/v2/merchant/transactions/query', $history[0]['request']->getUri()->getPath());
        $this->assertSame('transactionReference=txn-ref', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function status_by_reference_supports_payment_references(): void
    {
        $history = [];
        $service = new TransactionService($this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ], $history));

        $service->statusByReference('pay-ref', 'payment');

        $this->assertSame('paymentReference=pay-ref', $history[0]['request']->getUri()->getQuery());
    }

    #[Test]
    public function status_by_reference_rejects_unknown_reference_types(): void
    {
        $service = new TransactionService($this->makeClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either transaction or payment must be provided as referenceType');

        $service->statusByReference('txn-ref', 'invoice');
    }

    private function validInitializePayload(): array
    {
        return [
            'amount' => 5000,
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'paymentReference' => 'pay-ref',
            'paymentDescription' => 'Invoice payment',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'redirectUrl' => 'https://example.com/return',
        ];
    }
}
