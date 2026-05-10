<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\SubAccountValidator;

class SubAccountService extends BaseService
{
    private SubAccountValidator $validator;

    public function __construct(Client $client, ?SubAccountValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new SubAccountValidator();
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

    public function all(): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/sub-accounts'
        );
    }

    public function update(array $data): array
    {
        $this->validator->validateAccount([$data]);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/sub-accounts',
            $data
        );
    }

    public function delete(string $subAccountCode): array
    {
        if (empty($subAccountCode)) {
            throw new InvalidArgumentException('Sub Account Code must be provided');
        }
        return $this->makeRequest(
            HttpMethod::DELETE,
            '/api/v1/sub-accounts/'. $subAccountCode
        );
    }
}
