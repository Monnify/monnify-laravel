<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use Closure;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\CustomerReservedAccountValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class CustomerReservedAccountValidatorTest extends TestCase
{
    private CustomerReservedAccountValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new CustomerReservedAccountValidator();
    }

    public function test_validate_create_general_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateCreateGeneralAccount($this->validGeneralAccountPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidCreateGeneralAccountPayloads')]
    public function test_validate_create_general_account_rejects_invalid_payloads(
        Closure $mutator,
        string $expectedMessage
    ): void {
        $payload = $this->validGeneralAccountPayload();
        $mutator($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateCreateGeneralAccount($payload);
    }

    public function test_validate_create_invoice_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateCreateInvoiceAccount($this->validInvoiceAccountPayload());

        $this->assertTrue(true);
    }

    public function test_validate_add_linked_accounts_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The preferred banks field must be an array.');

        $this->validator->validateAddLinkedAccounts([
            'preferredBanks' => '058',
        ]);
    }

    public function test_validate_allowed_payment_source_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The allowed payment source.bvns field must be an array.');

        $this->validator->validateAllowedPaymentSource([
            'allowedPaymentSource' => [
                'bvns' => '12345678901',
            ],
        ]);
    }

    public function test_validate_update_split_config_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The splits.0.splitPercentage field must be at least 0.');

        $this->validator->validateUpdateSplitConfig([
            [
                'splitPercentage' => -1,
            ],
        ]);
    }

    public function test_validate_get_reserved_account_transactions_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The page field must be an integer.');

        $this->validator->validateGetReservedAccountTransactions([
            'page' => 'one',
        ]);
    }

    public function test_validate_update_kyc_info_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The bvn field must be a string.');

        $this->validator->validateUpdateKYCInfo([
            'bvn' => ['invalid'],
        ]);
    }

    public static function provideInvalidCreateGeneralAccountPayloads(): array
    {
        return [
            'missing account reference' => [
                static function (array &$payload): void {
                    unset($payload['accountReference']);
                },
                'The account reference field is required.',
            ],
            'missing bvn' => [
                static function (array &$payload): void {
                    unset($payload['bvn']);
                },
                'The bvn field is required.',
            ],
        ];
    }

    private function validGeneralAccountPayload(): array
    {
        return [
            'accountReference' => 'acct-ref',
            'accountName' => 'Main account',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'customerEmail' => 'jane@example.com',
            'customerName' => 'Jane Doe',
            'getAllAvailableBanks' => true,
            'restrictPaymentSource' => false,
            'bvn' => '12345678901',
        ];
    }

    private function validInvoiceAccountPayload(): array
    {
        return [
            'contractCode' => 'contract-123',
            'accountName' => 'Invoice account',
            'currencyCode' => 'NGN',
            'accountReference' => 'acct-ref',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
        ];
    }
}
