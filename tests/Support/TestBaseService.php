<?php

namespace Monnify\MonnifyLaravel\Tests\Support;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Services\BaseService;

class TestBaseService extends BaseService
{
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    public function sendGet(string $endpoint, array $parameters = []): array
    {
        return $this->requestGet($endpoint, $parameters);
    }

    public function sendPost(string $endpoint, array $data = [], array $parameters = []): array
    {
        return $this->requestPost($endpoint, $data, $parameters);
    }
}
