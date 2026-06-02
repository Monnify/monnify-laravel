<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;

class OtherService extends BaseService
{
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }
    
    public function banks(): array
    {
        return $this->requestGet('/api/v1/banks');
    }

    public function banksWithUSSD(): array
    {
        return $this->requestGet('/api/v1/sdk/transactions/banks');
    }
}
