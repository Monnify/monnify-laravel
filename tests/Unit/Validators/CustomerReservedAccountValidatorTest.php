<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use Closure;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\CustomerReservedAccountValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CustomerReservedAccountValidatorTest extends TestCase
{
    private CustomerReservedAccountValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new CustomerReservedAccountValidator();
    }

    #[Test]
    public function validate_create_general_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateCreateGeneralAccount($this->validGeneralAccountPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidCreateGeneralAccountPayloads')]
    #[Test]
    public function validate_create_general_account_rejects_invalid_payloads(
        Closure $mutator,
        string $expectedMessage
    ): void {
        $payload = $this->validGeneralAccountPayload();
        $mutator($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateCreateGeneralAccount($payload);
    }

    #[Test]
    public function validate_create_general_account_accepts_nin_without_bvn(): void
    {
        $payload = $this->validGeneralAccountPayload();
        unset($payload['bvn']);
        $payload['nin'] = '98765432101';

        $this->validator->validateCreateGeneralAccount($payload);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_create_invoice_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateCreateInvoiceAccount($this->validInvoiceAccountPayload());

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_add_linked_accounts_accepts_a_valid_payload(): void
    {
        $this->validator->validateAddLinkedAccounts([
            'getAllAvailableBanks' => false,
            'preferredBanks' => ['058', '011'],
        ]);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_add_linked_accounts_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The preferred banks field must be an array.');

        $this->validator->validateAddLinkedAccounts([
            'preferredBanks' => '058',
        ]);
    }

    #[Test]
    public function validate_allowed_payment_source_accepts_a_valid_payload(): void
    {
        $this->validator->validateAllowedPaymentSource([
            'restrictPaymentSource' => true,
            'allowedPaymentSource' => ['bvns' => ['12345678901']],
        ]);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_allowed_payment_source_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The allowed payment source.bvns field must be an array.');

        $this->validator->validateAllowedPaymentSource([
            'allowedPaymentSource' => [
                'bvns' => '12345678901',
            ],
        ]);
    }

    #[Test]
    public function validate_update_split_config_accepts_a_valid_payload(): void
    {
        $this->validator->validateUpdateSplitConfig([
            ['subAccountCode' => 'sub-123', 'feeBearer' => true, 'feePercentage' => 0.5, 'splitPercentage' => 20.0],
        ]);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_update_split_config_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The splits.0.splitPercentage field must be at least 0.');

        $this->validator->validateUpdateSplitConfig([
            [
                'splitPercentage' => -1,
            ],
        ]);
    }

    #[Test]
    public function validate_get_reserved_account_transactions_accepts_a_valid_payload(): void
    {
        $this->validator->validateGetReservedAccountTransactions(['page' => 2, 'size' => 25]);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_get_reserved_account_transactions_rejects_invalid_payloads(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The page field must be an integer.');

        $this->validator->validateGetReservedAccountTransactions([
            'page' => 'one',
        ]);
    }

    #[Test]
    public function validate_update_kyc_info_accepts_a_valid_payload(): void
    {
        $this->validator->validateUpdateKYCInfo(['bvn' => '12345678901']);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_update_kyc_info_rejects_invalid_payloads(): void
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
