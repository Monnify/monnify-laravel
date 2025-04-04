<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\PayCodeValidator;

class PayCodeService extends BaseService
{
    private PayCodeValidator $validator;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->validator = new PayCodeValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validate($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/paycode',
            $data
        );
    }

    public function get(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/paycode/'. $payCodeReference
        );
    }

    public function getUnMasked(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/paycode/'. $payCodeReference . '/authorize'
        );
    }

    public function history(array $parameters): array
    {
        $this->validator->validateHistoryParameters($parameters);
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/paycode',
            [],
            $parameters
        );
    }

    public function delete(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->makeRequest(
            HttpMethod::DELETE,
            '/api/v1/paycode/'. $payCodeReference
        );
    }
}