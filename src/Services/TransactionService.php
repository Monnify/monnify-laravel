<?php

namespace Monnify\MonnifyLaravel\Services;

class TransactionService extends BaseService
{
    public function initializeTransaction()
    {
        
    }
    public function create(array $data)
    {
        return $this->makeRequest('POST', '/transactions', $data);
    }

    public function get(string $id)
    {
        return $this->makeRequest('GET', "/transactions/{$id}");
    }

    public function list(array $params = [])
    {
        return $this->makeRequest('GET', '/transactions', $params);
    }

    public function refund(string $id, array $data)
    {
        return $this->makeRequest('POST', "/transactions/{$id}/refund", $data);
    }
}