<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
// use Monnify\MonnifyLaravel\Exceptions\PaymentException;

abstract class BaseService
{
    private int $expiresIn;

    public function __construct(protected Client $client)
    {
        $this->client = $client;
    }

    protected function makeRequest(
        HttpMethod $method,
        string $endpoint,
        array $data = []
    ): array {
        try {
            $accessToken = $this->getAccessToken()['accessToken'];
            $options = ['headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]];
            
            if (!empty($data)) {
                $options['json'] = $data;
            }

            $response = $this->client->request($method->value, $endpoint, $options);

            return [
                $response->getStatusCode(),
                $response->getBody()->getContents(),
            ];
        } catch (\Throwable $e) {
            // throw new  MonnifyException(
            //     message: $e->getMessage(),
            //     code: (int) $e->getCode(),
            //     previous: $e
            // );
        }
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
            $headers = [
                'Authorization' => config('basic_key'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ];

            $response = $this->client->post('/api/v1/auth/login',[ 'headers' => $headers] );
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

    public function setAccessToken(
        string $accessToken,
        int $expiresIn
    ): void {
        Config::set('accessToken', $accessToken);
        $this->expiresIn = $expiresIn;
    }
}