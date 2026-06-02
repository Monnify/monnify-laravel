<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\InvoiceValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class InvoiceValidatorTest extends TestCase
{
    private InvoiceValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new InvoiceValidator();
    }

    #[Test]
    public function validate_account_accepts_a_valid_payload(): void
    {
        $this->validator->validateAccount($this->validPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidInvoicePayloads')]
    #[Test]
    public function validate_account_rejects_invalid_payloads(
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

        $this->validator->validateAccount($payload);
    }

    public static function provideInvalidInvoicePayloads(): array
    {
        return [
            'missing invoice reference' => [
                [],
                ['invoiceReference'],
                'The invoice reference field is required.',
            ],
            'amount below minimum' => [
                ['amount' => 10],
                [],
                'The amount field must be at least 20.',
            ],
            'invalid customer email' => [
                ['customerEmail' => 'not-an-email'],
                [],
                'The customer email field must be a valid email address.',
            ],
        ];
    }

    private function validPayload(): array
    {
        return [
            'amount' => 5000,
            'currencyCode' => 'NGN',
            'invoiceReference' => 'inv-123',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'contractCode' => 'contract-123',
            'description' => 'Invoice payment',
            'expiryDate' => '2026-12-31',
        ];
    }
}
