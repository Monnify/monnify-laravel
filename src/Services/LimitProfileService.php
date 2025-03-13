<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\LimitProfileValidator;

class LimitProfileService extends BaseService
{
    private LimitProfileValidator $validator;

    public function __construct($client)
    {
        parent::__construct($client);
        $this->validator = new LimitProfileValidator();
    }

    public function getAll(): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/limit-profile/'
        );
    }

    public function create(array $data): array
    {
        $this->validator->validateLimitProfile($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/limit-profile/',
            $data
        );
    }

    public function update(string $limitProfileCode, array $data): array
    {
        $this->validator->validateLimitProfile($data);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/limit-profile/'.$limitProfileCode,
            $data
        );
    }

    public function reserveAccount(array $data): array
    {
        $this->validator->validateReserveAccount($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/bank-transfer/reserved-accounts/limit',
            $data
        );
    }

    public function updateReserveAccount(string $accountReference, string $limitProfileCode): array
    {
        $data = [
            'accountReference' => $accountReference,
            'limitProfileCode' => $limitProfileCode
        ];
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/bank-transfer/reserved-accounts/limit',
            $data
        );
    }
}