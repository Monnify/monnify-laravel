<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\DirectDebitValidator;

class DirectDebitService extends BaseService 
{
    private DirectDebitValidator $validator;

    public function __construct()
    {
        parent::__construct();
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
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/direct-debit/mandate/'
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

    public function getStatus(string $paymentReference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/direct-debit/mandate/debit-status',
            [],
            ['paymentReference' => $paymentReference]
        );
    }

    public function cancel(string $mandateCode): array
    {
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/direct-debit/mandate/cancel-mandate/'.$mandateCode
        );
    }
}