<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
// use Monnify\MonnifyLaravel\Exceptions\PaymentException;

abstract class BaseService
{
    protected $client;
    private int $expiresIn;

    public function __construct(protected Client $httpClient)
    {
        $this->client = $httpClient;
    }

    public function getAccessToken(): array
    {
        if (config('accessToken') != 'null' && $this->expiresIn != null && ($this->expiresIn > floor(microtime(true)))) {
            $accessToken = config('accessToken');
            return [
                'status' => 200,
                'accessToken' => $accessToken
            ];
        }

        try {
            $response = $this->client->post('/api/v1/auth/login');
            $accessToken = $response->getBody()->getContents()->responseBody->accessToken;
            // store token
            $this->setAccessToken($accessToken, $response->getBody()->getContents()->responseBody->expiresIn + floor(microtime(true)));

            return [
                'status' => $response->getStatusCode(),
                'accessToken' => $accessToken
            ];
        } catch (GuzzleException $e) {
            // Handle the error appropriately
            // throw new MonnifyException($e->getMessage(), $e->getCode());
        }
    }

    public function setAccessToken($accessToken, $expiresIn)
    {
        Config::set('accessToken', $accessToken);
        $this->expiresIn = $expiresIn;
    }

    protected function makeRequest($method, $endpoint, array $data = [])
    {
        try {
            $options = !empty($data) ? ['json' => $data] : [];
            $response = $this->client->request($method, $endpoint, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            // throw new PaymentException($e->getMessage(), $e->getCode());
        }
    }
}