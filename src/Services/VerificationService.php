<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\VerificationValidator;

class VerificationService extends BaseService
{
    private VerificationValidator $validator;

    public function __construct()
    {
        parent::__construct();
        $this->validator = new VerificationValidator();
    }

    public function bankAccount(string $accountNumber, string $bankCode): array
    {
        $parameters = [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode
        ];

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/disbursements/account/validate',
            [],
            $parameters
        );
    }

    public function bvnInformation(array $data): array
    {
        $this->validator->validateBVNInformation($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/vas/bvn-details-match',
            $data
        );
    }

    public function matchBVNAndBankAccount(string $bvn, string $bankCode, string $accountNumber): array
    {
        $data = [
            'bvn' => $bvn,
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber
        ];

        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/vas/bvn-account-match',
            $data
        );
    }

    public function nin(string $nin): array
    {
        $data = [
            'nin' => $nin
        ];

        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/vas/nin-details',
            $data
        );
    }
}