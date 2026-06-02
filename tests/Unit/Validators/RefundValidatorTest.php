<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\RefundValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class RefundValidatorTest extends TestCase
{
    private RefundValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new RefundValidator();
    }

    #[Test]
    public function validate_refund_accepts_a_valid_payload(): void
    {
        $this->validator->validateRefund($this->validPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidRefundPayloads')]
    #[Test]
    public function validate_refund_rejects_invalid_payloads(
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

        $this->validator->validateRefund($payload);
    }

    public static function provideInvalidRefundPayloads(): array
    {
        return [
            'missing transaction reference' => [
                [],
                ['transactionReference'],
                'The transaction reference field is required.',
            ],
            'missing refund amount' => [
                [],
                ['refundAmount'],
                'The refund amount field is required.',
            ],
        ];
    }

    private function validPayload(): array
    {
        return [
            'transactionReference' => 'txn-123',
            'refundAmount' => 5000,
            'refundReference' => 'refund-123',
            'refundReason' => 'Customer request',
            'customerNote' => 'Refund approved',
            'destinationAccountNumber' => '0123456789',
            'destnationAccountBankCode' => '058',
        ];
    }
}
