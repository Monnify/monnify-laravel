<?php

namespace Monnify\MonnifyLaravel\Tests\Support;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Services\BaseService;

class TestBaseService extends BaseService
{
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    public function send(
        HttpMethod $method,
        string $endpoint,
        array $data = [],
        array $parameters = []
    ): array {
        return $this->makeRequest($method, $endpoint, $data, $parameters);
    }
}
