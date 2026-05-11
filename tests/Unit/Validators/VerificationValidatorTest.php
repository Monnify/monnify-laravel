<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Validators;

use InvalidArgumentException;
use Monnify\MonnifyLaravel\Tests\TestCase;
use Monnify\MonnifyLaravel\Validators\VerificationValidator;
use PHPUnit\Framework\Attributes\DataProvider;

class VerificationValidatorTest extends TestCase
{
    private VerificationValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new VerificationValidator();
    }

    public function test_validate_bvn_information_accepts_a_valid_payload(): void
    {
        $this->validator->validateBVNInformation($this->validBvnInformationPayload());

        $this->assertTrue(true);
    }

    #[DataProvider('provideInvalidBvnInformationPayloads')]
    public function test_validate_bvn_information_rejects_invalid_payloads(
        array $overrides,
        array $missingKeys,
        string $expectedMessage
    ): void {
        $payload = array_replace($this->validBvnInformationPayload(), $overrides);

        foreach ($missingKeys as $key) {
            unset($payload[$key]);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->validator->validateBVNInformation($payload);
    }

    public static function provideInvalidBvnInformationPayloads(): array
    {
        return [
            'missing bvn' => [
                [],
                ['bvn'],
                'The bvn field is required.',
            ],
            'missing mobile number' => [
                [],
                ['mobileNo'],
                'The mobile no field is required.',
            ],
        ];
    }

    private function validBvnInformationPayload(): array
    {
        return [
            'bvn' => '12345678901',
            'name' => 'Jane Doe',
            'dateOfBirth' => '1990-01-01',
            'mobileNo' => '08012345678',
        ];
    }
}
