<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\LimitProfileValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class LimitProfileValidatorTest extends TestCase
{
    private LimitProfileValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new LimitProfileValidator();
    }

    #[Test]
    public function validate_limit_profile_accepts_a_valid_payload(): void
    {
        $this->validator->validateLimitProfile($this->validLimitProfilePayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidLimitProfilePayloads')]
    #[Test]
    public function validate_limit_profile_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace($this->validLimitProfilePayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateLimitProfile($payload);
    }

    #[Test]
    public function validate_reserve_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateReserveAccount($this->validReserveAccountPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidReserveAccountPayloads')]
    #[Test]
    public function validate_reserve_account_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace($this->validReserveAccountPayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateReserveAccount($payload);
    }

    public static function provideInvalidLimitProfilePayloads(): array
    {
        return [
            'missing limit profile name' => [
                [],
                ['limitProfileName'],
                'The limit profile name field is required.',
            ],
            'invalid daily transaction volume type' => [
                ['dailyTransactionVolume' => 'ten'],
                [],
                'The daily transaction volume field must be a number.',
            ],
        ];
    }

    public static function provideInvalidReserveAccountPayloads(): array
    {
        return [
            'missing account reference' => [
                [],
                ['accountReference'],
                'The account reference field is required.',
            ],
            'missing contract code' => [
                [],
                ['contractCode'],
                'The contract code field is required.',
            ],
        ];
    }

    private function validLimitProfilePayload(): array
    {
        return [
            'limitProfileName' => 'Tier 1',
            'singleTransactionValue' => 50000,
            'dailyTransactionValue' => 250000,
            'dailyTransactionVolume' => 10,
        ];
    }

    private function validReserveAccountPayload(): array
    {
        return [
            'accountReference' => 'acct-ref',
            'limitProfileCode' => 'limit-123',
            'accountName' => 'Reserved account',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'customerEmail' => 'jane@example.com',
            'incomeSplitConfig' => [],
        ];
    }
}
