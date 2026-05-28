<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\BillsPaymentValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class BillsPaymentValidatorTest extends TestCase
{
    private BillsPaymentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new BillsPaymentValidator();
    }

    public function test_validate_pagination_accepts_default_parameters(): void
    {
        $this->validator->validatePagination(['size' => 10, 'page' => 0]);

        $this->assertTrue(true);
    }

    public function test_validate_pagination_accepts_empty_payload(): void
    {
        $this->validator->validatePagination([]);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidPaginationPayloads')]
    public function test_validate_pagination_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validatePagination($payload);
    }

    public static function provideInvalidPaginationPayloads(): array
    {
        return [
            'size below minimum' => [
                ['size' => 0, 'page' => 0],
                'The size field must be at least 1.',
            ],
            'page below minimum' => [
                ['size' => 10, 'page' => -1],
                'The page field must be at least 0.',
            ],
        ];
    }

    public function test_validate_billers_accepts_payload_without_category_code(): void
    {
        $this->validator->validateBillers(['size' => 10, 'page' => 0]);

        $this->assertTrue(true);
    }

    public function test_validate_billers_accepts_payload_with_category_code(): void
    {
        $this->validator->validateBillers(['size' => 10, 'page' => 0, 'category_code' => 'ELECTRICITY']);

        $this->assertTrue(true);
    }

    public function test_validate_products_accepts_a_valid_payload(): void
    {
        $this->validator->validateProducts(['biller_code' => 'BILLER-001', 'size' => 10, 'page' => 0]);

        $this->assertTrue(true);
    }

    public function test_validate_products_requires_biller_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The biller code field is required.');

        $this->validator->validateProducts(['size' => 10, 'page' => 0]);
    }

    public function test_validate_customer_accepts_a_valid_payload(): void
    {
        $this->validator->validateCustomer(['productCode' => 'PROD-001', 'customerId' => 'CUST-123']);

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidCustomerPayloads')]
    public function test_validate_customer_rejects_invalid_payloads(
        array $payload,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateCustomer($payload);
    }

    public static function provideInvalidCustomerPayloads(): array
    {
        return [
            'missing product code' => [
                ['customerId' => 'CUST-123'],
                'The product code field is required.',
            ],
            'missing customer id' => [
                ['productCode' => 'PROD-001'],
                'The customer id field is required.',
            ],
        ];
    }

    public function test_validate_vend_accepts_a_valid_payload(): void
    {
        $this->validator->validateVend($this->validVendPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidVendPayloads')]
    public function test_validate_vend_rejects_invalid_payloads(
        \Closure $mutate,
        string $expectedMessage
    ): void {
        $payload = $this->validVendPayload();
        $mutate($payload);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateVend($payload);
    }

    public static function provideInvalidVendPayloads(): array
    {
        return [
            'missing product code' => [
                static function (array &$payload): void { unset($payload['productCode']); },
                'The product code field is required.',
            ],
            'missing customer id' => [
                static function (array &$payload): void { unset($payload['customerId']); },
                'The customer id field is required.',
            ],
            'vend amount below minimum' => [
                static function (array &$payload): void { $payload['vendAmount'] = 0; },
                'The vend amount field must be at least 0.01.',
            ],
            'missing vend reference' => [
                static function (array &$payload): void { unset($payload['vendReference']); },
                'The vend reference field is required.',
            ],
        ];
    }

    public function test_validate_requery_accepts_a_valid_payload(): void
    {
        $this->validator->validateRequery(['vendReference' => 'vend-ref-123']);

        $this->assertTrue(true);
    }

    public function test_validate_requery_requires_vend_reference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The vend reference field is required.');

        $this->validator->validateRequery([]);
    }

    private function validVendPayload(): array
    {
        return [
            'productCode'   => 'PROD-001',
            'customerId'    => 'CUST-123',
            'vendAmount'    => 1000,
            'vendReference' => 'vend-ref-123',
        ];
    }
}
