<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\SubAccountValidator;

class SubAccountService extends BaseService
{
    private SubAccountValidator $validator;

    public function __construct()
    {
        parent::__construct();
        $this->validator = new SubAccountValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validateAccount($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/sub-accounts',
            $data
        );
    }

    public function getAll(): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/sub-accounts'
        );
    }

    public function update(array $data): array
    {
        $this->validator->validateAccount($data);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/sub-accounts',
            $data
        );
    }

    public function delete(string $subAccountCode): array
    {
        return $this->makeRequest(
            HttpMethod::DELETE,
            '/api/v1/sub-accounts/'. $subAccountCode
        );
    }
}