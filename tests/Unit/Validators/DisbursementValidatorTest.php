<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use Closure;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\DisbursementValidationFailure;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\DisbursementValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class DisbursementValidatorTest extends TestCase
{
    private DisbursementValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new DisbursementValidator();
    }

    public function test_validate_single_transfer_accepts_a_valid_payload(): void
    {
        $this->validator->validateSingleTransfer($this->validSingleTransferPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidSingleTransferPayloads')]
    public function test_validate_single_transfer_rejects_invalid_payloads(
        Closure $mutator,
        string $expectedMessage
    ): void {
        $payload = $this->validSingleTransferPayload();
        $mutator($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateSingleTransfer($payload);
    }

    public function test_validate_bulk_transfer_accepts_a_valid_payload(): void
    {
        $this->validator->validateBulkTransfer($this->validBulkTransferPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidBulkTransferPayloads')]
    public function test_validate_bulk_transfer_rejects_invalid_payloads(
        Closure $mutator,
        string $expectedMessage
    ): void {
        $payload = $this->validBulkTransferPayload();
        $mutator($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateBulkTransfer($payload);
    }

    public function test_validate_authorization_accepts_a_valid_payload(): void
    {
        $this->validator->validateAuthorization([
            'reference' => 'ref-123',
            'authorizationCode' => '123456',
        ]);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidAuthorizationPayloads')]
    public function test_validate_authorization_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateAuthorization($payload);
    }

    public static function provideInvalidSingleTransferPayloads(): array
    {
        return [
            'missing reference' => [
                static function (array &$payload): void {
                    unset($payload['reference']);
                },
                'The reference field is required.',
            ],
            'amount below minimum' => [
                static function (array &$payload): void {
                    $payload['amount'] = 10;
                },
                'The amount field must be at least 20.',
            ],
        ];
    }

    public static function provideInvalidBulkTransferPayloads(): array
    {
        return [
            'missing transaction list' => [
                static function (array &$payload): void {
                    unset($payload['transactionList']);
                },
                'The transaction list field is required.',
            ],
            'invalid enum value' => [
                static function (array &$payload): void {
                    $payload['onValidationFailure'] = 'IGNORE';
                },
                'The selected on validation failure is invalid.',
            ],
            'invalid nested transaction bank code' => [
                static function (array &$payload): void {
                    $payload['transactionList'][0]['destinationBankCode'] = '1234';
                },
                'The transactionList.0.destinationBankCode field must not be greater than 3 characters.',
            ],
        ];
    }

    public static function provideInvalidAuthorizationPayloads(): array
    {
        return [
            'missing authorization code' => [
                [
                    'reference' => 'ref-123',
                ],
                'The authorization code field is required.',
            ],
            'missing reference' => [
                [
                    'authorizationCode' => '123456',
                ],
                'The reference field is required.',
            ],
        ];
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
