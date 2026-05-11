<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\WalletValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class WalletValidatorTest extends TestCase
{
    private WalletValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new WalletValidator();
    }

    public function test_validate_create_accepts_a_valid_payload(): void
    {
        $this->validator->validateCreate($this->validPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidCreatePayloads')]
    public function test_validate_create_rejects_invalid_payloads(
        array $overrides,
        string $expectedMessage
    ): void {
        $payload = array_replace_recursive($this->validPayload(), $overrides);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateCreate($payload);
    }

    public static function provideInvalidCreatePayloads(): array
    {
        return [
            'invalid customer email' => [
                ['customerEmail' => 'not-an-email'],
                'The customer email field must be a valid email address.',
            ],
            'invalid bvn details type' => [
                ['bvnDetails' => 'invalid'],
                'The bvn details field must be an array.',
            ],
        ];
    }

    private function validPayload(): array
    {
        return [
            'walletReference' => 'wallet-123',
            'walletName' => 'Main Wallet',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'bvnDetails' => [
                'bvn' => '12345678901',
                'bvnDateOfBirth' => '1990-01-01',
            ],
        ];
    }
}
