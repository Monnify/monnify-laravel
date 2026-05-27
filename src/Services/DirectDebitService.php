<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\DirectDebitValidator;

class DirectDebitService extends BaseService 
{
    private DirectDebitValidator $validator;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->validator = new DirectDebitValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validateMandate($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/direct-debit/mandate/create',
            $data
        );
    }

    public function get(string $mandateReference): array
    {
        if (empty($mandateReference)) {
            throw new InvalidArgumentException('Mandate Reference must be provided.');
        }

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/direct-debit/mandate/',
            [],
            ['mandateReferences' => $mandateReference]
        );
    }

    public function debit(array $data): array
    {
        $this->validator->validateDebit($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/direct-debit/mandate/debit',
            $data
        );
    }

    public function status(string $paymentReference): array
    {
        if (empty($paymentReference)) {
            throw new InvalidArgumentException('Payment Reference must be provided.');
        }

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/direct-debit/mandate/debit-status',
            [],
            ['paymentReference' => $paymentReference]
        );
    }

    public function cancel(string $mandateCode): array
    {
        if (empty($mandateCode)) {
            throw new InvalidArgumentException('Mandate Code must be provided.');
        }

        return $this->makeRequest(
            HttpMethod::PATCH,
            '/api/v1/direct-debit/mandate/cancel-mandate/'. $mandateCode
        );
    }
}