<?php

namespace Monnify\MonnifyLaravel\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Monnify\MonnifyLaravel\Enums\HttpMethod;

abstract class BaseService
{
    public function __construct(protected Client $client)
    {
        $this->client = $client;
    }

    protected function requestGet(string $endpoint, array $parameters = []): array
    {
        return $this->makeRequest(HttpMethod::GET, $endpoint, [], $parameters);
    }

    protected function requestPost(string $endpoint, array $data = [], array $parameters = []): array
    {
        return $this->makeRequest(HttpMethod::POST, $endpoint, $data, $parameters);
    }

    protected function requestPut(string $endpoint, array $data = [], array $parameters = []): array
    {
        return $this->makeRequest(HttpMethod::PUT, $endpoint, $data, $parameters);
    }

    protected function requestPatch(string $endpoint, array $data = [], array $parameters = []): array
    {
        return $this->makeRequest(HttpMethod::PATCH, $endpoint, $data, $parameters);
    }

    protected function requestDelete(string $endpoint, array $data = [], array $parameters = []): array
    {
        return $this->makeRequest(HttpMethod::DELETE, $endpoint, $data, $parameters);
    }

    private function makeRequest(
        HttpMethod $method,
        string $endpoint,
        array $data = [],
        array $parameters = []
    ): array {
        try {
            $accessToken = $this->getAccessToken();
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            if (!empty($parameters)) {
                $options['query'] = $parameters;
            }

            $response = $this->client->request($method->value, $endpoint, $options);

            return [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();

            return [
                'status' => $response?->getStatusCode(),
                'error' => $response !== null
                    ? json_decode($response->getBody()->getContents())
                    : (object) ['message' => $e->getMessage()],
            ];
        } catch (ConnectException $e) {
            return [
                'status' => null,
                'error' => (object) [
                    'type'    => 'network_error',
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    public function getAccessToken(): string
    {
        $cachedToken = Cache::get('monnify_access_token');
        if ($cachedToken !== null) {
            return $cachedToken;
        }

        try {
            $response = $this->client->post('/api/v1/auth/login', [
                'auth' => [
                    config('monnify.api_key'),
                    config('monnify.secret_key'),
                ]
            ]);

            $response = (object) json_decode($response->getBody()->getContents(), true);
            $content = (object) $response->responseBody;
            $accessToken = $content->accessToken;
            // store token with its TTL so it is persisted across requests
            $this->setAccessToken($accessToken, max(1, (int) $content->expiresIn - 60));

            return $accessToken;
        } catch (Exception $e) {
            throw new Exception(
                message: $e->getMessage(),
                code: (int) $e->getCode()
            );
        }
    }

    public function setAccessToken(
        string $accessToken,
        int $expiresIn
    ): void {
        Cache::put('monnify_access_token', $accessToken, $expiresIn);
    }
}
