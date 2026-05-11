<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\SubAccountValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class SubAccountValidatorTest extends TestCase
{
    private SubAccountValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new SubAccountValidator();
    }

    public function test_validate_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateAccount($this->validPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidAccountPayloads')]
    public function test_validate_account_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateAccount($payload);
    }

    public static function provideInvalidAccountPayloads(): array
    {
        return [
            'missing currency code' => [
                [[
                    'accountNumber' => '0123456789',
                    'bankCode' => '058',
                    'email' => 'jane@example.com',
                    'defaultSplitPercentage' => 20,
                ]],
                'The sub.0.currencyCode field is required.',
            ],
            'invalid split percentage' => [
                [[
                    'currencyCode' => 'NGN',
                    'accountNumber' => '0123456789',
                    'bankCode' => '058',
                    'email' => 'jane@example.com',
                    'defaultSplitPercentage' => 10,
                ]],
                'The sub.0.defaultSplitPercentage field must be at least 20.',
            ],
        ];
    }

    private function validPayload(): array
    {
        return [[
            'currencyCode' => 'NGN',
            'accountNumber' => '0123456789',
            'bankCode' => '058',
            'email' => 'jane@example.com',
            'defaultSplitPercentage' => 20,
        ]];
    }
}
