<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\HttpMethod;

class SettlementService extends BaseService
{
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }
    
    public function transactions(string $settlementReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'reference' => $settlementReference,
            'size' => $pageSize,
            'page' => $pageNumber
        ];

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/transactions/find-by-settlement-reference',
            [],
            $parameters
        );
    }

    public function getByTransaction(string $transactionReference): array
    {
        if (empty($transactionReference)) {
            throw new InvalidArgumentException('Transaction Reference must be provided.');
        }
        
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/settlement-detail',
            [],
            ['transactionReference' => $transactionReference]
        );
    }
}