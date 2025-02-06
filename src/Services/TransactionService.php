<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;

class TransactionService extends BaseService
{
    public function initializeTransaction()
    {

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