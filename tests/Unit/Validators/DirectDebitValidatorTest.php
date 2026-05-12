<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\DirectDebitValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class DirectDebitValidatorTest extends TestCase
{
    private DirectDebitValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new DirectDebitValidator();
    }

    public function test_validate_mandate_accepts_a_valid_payload(): void
    {
        $this->validator->validateMandate($this->validMandatePayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidMandatePayloads')]
    public function test_validate_mandate_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace($this->validMandatePayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateMandate($payload);
    }

    public function test_validate_debit_accepts_a_valid_payload(): void
    {
        $this->validator->validateDebit($this->validDebitPayload());

        $this->assertTrue(true);
    }

    public function test_validate_debit_accepts_income_split_config(): void
    {
        $payload = $this->validDebitPayload();
        $payload['incomeSplitConfig'] = [
            [
                'subAccountCode' => 'SUB_123',
                'feeBearer' => true,
                'splitPercentage' => 20,
            ],
        ];

        $this->validator->validateDebit($payload);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidDebitPayloads')]
    public function test_validate_debit_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace($this->validDebitPayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateDebit($payload);
    }

    public static function provideInvalidMandatePayloads(): array
    {
        return [
            'missing mandate reference' => [
                [],
                ['mandateReference'],
                'The mandate reference field is required.',
            ],
            'invalid customer email address' => [
                ['customerEmailAddress' => 'not-an-email'],
                [],
                'The customer email address field must be a valid email address.',
            ],
        ];
    }

    public static function provideInvalidDebitPayloads(): array
    {
        return [
            'missing payment reference' => [
                [],
                ['paymentReference'],
                'The payment reference field is required.',
            ],
            'invalid customer email' => [
                ['customerEmail' => 'not-an-email'],
                [],
                'The customer email field must be a valid email address.',
            ],
            'missing income split sub account code' => [
                [
                    'incomeSplitConfig' => [
                        [
                            'splitPercentage' => 20,
                        ],
                    ],
                ],
                [],
                'The incomeSplitConfig.0.subAccountCode field is required when income split config is present.',
            ],
        ];
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

    private function validDebitPayload(): array
    {
        return [
            'paymentReference' => 'payment-123',
            'mandateCode' => 'mandate-code',
            'debitAmount' => 5000,
            'narration' => 'Subscription charge',
            'customerEmail' => 'jane@example.com',
        ];
    }
}
