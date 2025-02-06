<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\TransactionValidator;

class TransactionService extends BaseService
{
    private TransactionValidator $validator;
    
    public function __construct()
    {
        parent::__construct();
        $this->validator = new TransactionValidator();
    }

    public function initializeTransaction(array $data)
    {
        $this->validator->validateInitializeTransaction($data);
        return $this->makeRequest(HttpMethod::POST, '/api/v1/merchant/transactions/init-transaction', $data);
    }
    public function create(array $data)
    {
        return $this->makeRequest(HttpMethod::POST, '/transactions', $data);
    }

    public function get(string $id)
    {
        return $this->makeRequest(HttpMethod::GET, "/transactions/{$id}");
    }

    public function list(array $params = [])
    {
        return $this->makeRequest(HttpMethod::GET, '/transactions', $params);
    }

    public function refund(string $id, array $data)
    {
        return $this->makeRequest(HttpMethod::POST, "/transactions/{$id}/refund", $data);
    }
}