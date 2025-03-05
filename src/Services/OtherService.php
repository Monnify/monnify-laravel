<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;

class OtherService extends BaseService
{
    public function banks(): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/banks'
        );
    }

    public function banksWithUSSD(): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/sdk/transactions/banks'
        );
    }
}