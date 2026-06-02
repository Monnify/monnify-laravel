<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\RecurringPaymentValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class RecurringPaymentValidatorTest extends TestCase
{
    private RecurringPaymentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new RecurringPaymentValidator();
    }

    #[Test]
    public function validate_charge_card_token_accepts_a_valid_payload(): void
    {
        $this->validator->validateChargeCardToken($this->validPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidChargeCardTokenPayloads')]
    #[Test]
    public function validate_charge_card_token_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace_recursive($this->validPayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateChargeCardToken($payload);
    }

    public static function provideInvalidChargeCardTokenPayloads(): array
    {
        return [
            'missing card token' => [
                [],
                ['cardToken'],
                'The card token field is required.',
            ],
            'invalid customer email' => [
                ['customerEmail' => 'not-an-email'],
                [],
                'The customer email field must be a valid email address.',
            ],
            'invalid income split config type' => [
                ['incomeSplitConfig' => 'invalid'],
                [],
                'The income split config field must be an array.',
            ],
        ];
    }

    private function validPayload(): array
    {
        return [
            'amount' => 5000,
            'cardToken' => 'card-token',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'paymentReference' => 'payment-123',
            'paymentDescription' => 'Recurring charge',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-123',
            'apiKey' => 'api-key',
            'incomeSplitConfig' => [],
            'metaData' => [
                'ipAddress' => '127.0.0.1',
                'deviceType' => 'WEB',
            ],
        ];
    }
}
