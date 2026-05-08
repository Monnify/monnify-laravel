<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use Closure;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\TransactionValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class TransactionValidatorTest extends TestCase
{
    private TransactionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new TransactionValidator();
    }

    public function test_validate_initialize_accepts_a_valid_payload(): void
    {
        $this->validator->validateInitialize($this->validInitializePayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidInitializePayloads')]
    public function test_validate_initialize_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateInitialize($payload);
    }

    public function test_validate_pay_with_bank_transfer_accepts_a_valid_payload(): void
    {
        $this->validator->validatePayWithBankTransfer([
            'transactionReference' => 'txn-ref',
            'bankCode' => '058',
        ]);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidPayWithBankTransferPayloads')]
    public function test_validate_pay_with_bank_transfer_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validatePayWithBankTransfer($payload);
    }

    public function test_validate_charge_card_accepts_a_valid_payload(): void
    {
        $this->validator->validateChargeCard($this->validChargeCardPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidChargeCardPayloads')]
    public function test_validate_charge_card_rejects_invalid_payloads(
        Closure $mutator,
        string $expectedMessage
    ): void {
        $payload = $this->validChargeCardPayload();
        $mutator($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateChargeCard($payload);
    }

    public function test_validate_authorize_otp_accepts_a_valid_payload(): void
    {
        $this->validator->validateAuthorizeOTP([
            'transactionReference' => 'txn-ref',
            'collectionChannel' => 'API_NOTIFICATION',
            'tokenId' => 'token-id',
            'token' => '123456',
        ]);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidAuthorizeOtpPayloads')]
    public function test_validate_authorize_otp_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateAuthorizeOTP($payload);
    }

    public function test_validate_authorize_three_ds_card_accepts_a_valid_payload(): void
    {
        $payload = $this->validChargeCardPayload();
        $payload['apiKey'] = 'api-key';

        $this->validator->validateAuthorizeThreeDSCard($payload);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidAuthorizeThreeDsCardPayloads')]
    public function test_validate_authorize_three_ds_card_rejects_invalid_payloads(
        Closure $mutator,
        string $expectedMessage
    ): void {
        $payload = $this->validChargeCardPayload();
        $payload['apiKey'] = 'api-key';
        $mutator($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateAuthorizeThreeDSCard($payload);
    }

    public function test_validate_get_all_transactions_accepts_valid_search_parameters(): void
    {
        $this->validator->validateGetAllTransactions([
            'page' => 1,
            'size' => 20,
            'paymentReference' => 'pay-ref',
            'fromAmount' => 100,
            'toAmount' => 5000,
            'customerEmail' => 'jane@example.com',
            'from' => 1715068800000,
            'to' => 1715155200000,
        ]);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidGetAllTransactionsPayloads')]
    public function test_validate_get_all_transactions_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateGetAllTransactions($payload);
    }

    public static function provideInvalidInitializePayloads(): array
    {
        return [
            'missing amount' => [
                [],
                'The amount field is required.',
            ],
            'amount below minimum' => [
                [
                    ...self::baseInitializePayload(),
                    'amount' => 10,
                ],
                'The amount field must be at least 20.',
            ],
        ];
    }

    public static function provideInvalidPayWithBankTransferPayloads(): array
    {
        return [
            'missing transaction reference' => [
                [],
                'The transaction reference field is required.',
            ],
        ];
    }

    public static function provideInvalidChargeCardPayloads(): array
    {
        return [
            'missing cvv' => [
                static function (array &$payload): void {
                    unset($payload['card']['cvv']);
                },
                'The card.cvv field is required.',
            ],
            'missing collection channel' => [
                static function (array &$payload): void {
                    unset($payload['collectionChannel']);
                },
                'The collection channel field is required.',
            ],
        ];
    }

    public static function provideInvalidAuthorizeOtpPayloads(): array
    {
        return [
            'missing token' => [
                [
                    'transactionReference' => 'txn-ref',
                    'collectionChannel' => 'API_NOTIFICATION',
                    'tokenId' => 'token-id',
                ],
                'The token field is required.',
            ],
            'missing token id' => [
                [
                    'transactionReference' => 'txn-ref',
                    'collectionChannel' => 'API_NOTIFICATION',
                    'token' => '123456',
                ],
                'The token id field is required.',
            ],
        ];
    }

    public static function provideInvalidAuthorizeThreeDsCardPayloads(): array
    {
        return [
            'missing api key' => [
                static function (array &$payload): void {
                    unset($payload['apiKey']);
                },
                'The api key field is required.',
            ],
            'missing card number' => [
                static function (array &$payload): void {
                    unset($payload['card']['number']);
                },
                'The card.number field is required.',
            ],
        ];
    }

    public static function provideInvalidGetAllTransactionsPayloads(): array
    {
        return [
            'invalid from timestamp' => [
                ['from' => 12345],
                'The from field must be 13 digits.',
            ],
            'invalid customer email' => [
                ['customerEmail' => 'not-an-email'],
                'The customer email field must be a valid email address.',
            ],
        ];
    }

    private function validInitializePayload(): array
    {
        return self::baseInitializePayload();
    }

    private static function baseInitializePayload(): array
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

    private function validChargeCardPayload(): array
    {
        return [
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
    }
}
