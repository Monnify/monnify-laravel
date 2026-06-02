<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Validators\PayCodeValidator;

class PayCodeService extends BaseService
{
    private PayCodeValidator $validator;

    public function __construct(Client $client, ?PayCodeValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new PayCodeValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validate($data);
        return $this->requestPost('/api/v1/paycode', $data);
    }

    public function get(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->requestGet('/api/v1/paycode/'. $payCodeReference);
    }

    public function getUnMasked(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->requestGet('/api/v1/paycode/'. $payCodeReference . '/authorize');
    }

    public function history(array $parameters): array
    {
        $this->validator->validateHistoryParameters($parameters);
        return $this->requestGet('/api/v1/paycode', $parameters);
    }

    public function delete(string $payCodeReference): array
    {
        if (empty($payCodeReference)) {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }

        return $this->requestDelete('/api/v1/paycode/'. $payCodeReference);
    }
}
