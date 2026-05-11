<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\PayCodeValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class PayCodeValidatorTest extends TestCase
{
    private PayCodeValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new PayCodeValidator();
    }

    public function test_validate_accepts_a_valid_payload(): void
    {
        $this->validator->validate($this->validPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidPayloads')]
    public function test_validate_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace($this->validPayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validate($payload);
    }

    public function test_validate_history_parameters_accepts_valid_input(): void
    {
        $this->validator->validateHistoryParameters([
            'transactionReference' => 'txn-123',
            'beneficiaryName' => 'Jane Doe',
            'transactionStatus' => 'SUCCESS',
            'from' => 1715068800000,
            'to' => 1715155200000,
        ]);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidHistoryPayloads')]
    public function test_validate_history_parameters_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateHistoryParameters($payload);
    }

    public static function provideInvalidPayloads(): array
    {
        return [
            'missing beneficiary name' => [
                [],
                ['beneficiaryName'],
                'The beneficiary name field is required.',
            ],
            'amount below minimum' => [
                ['amount' => 10],
                [],
                'The amount field must be at least 20.',
            ],
        ];
    }

    public static function provideInvalidHistoryPayloads(): array
    {
        return [
            'invalid from type' => [
                ['from' => 'yesterday'],
                'The from field must be an integer.',
            ],
        ];
    }

    private function validPayload(): array
    {
        return [
            'beneficiaryName' => 'Jane Doe',
            'amount' => 5000,
            'paycodeReference' => 'paycode-123',
            'expiryDate' => '2026-12-31',
            'clientId' => 'client-123',
        ];
    }
}
