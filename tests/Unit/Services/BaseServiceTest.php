<?php

namespace Monnify\MonnifyLaravel\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Tests\Support\CreatesMockClient;
use Monnify\MonnifyLaravel\Tests\Support\TestBaseService;
use Monnify\MonnifyLaravel\Tests\TestCase;

class BaseServiceTest extends TestCase
{
    use CreatesMockClient;

    protected function tearDown(): void
    {
        Config::set('accessToken', null);
        Config::set('expiresIn', null);

        parent::tearDown();
    }

    public function test_it_reuses_a_cached_access_token_when_it_is_not_expired(): void
    {
        Config::set('accessToken', 'cached-token');
        Config::set('expiresIn', time() + 300);

        $history = [];
        $service = new TestBaseService($this->makeClient([
            new Response(200, [], json_encode(['message' => 'ok'])),
        ], $history));

        $result = $service->send(HttpMethod::GET, '/api/v1/banks', [], ['page' => 1]);

        $this->assertSame(200, $result['status']);
        $this->assertSame(['message' => 'ok'], $result['body']);
        $this->assertCount(1, $history);
        $this->assertSame('Bearer cached-token', $history[0]['request']->getHeaderLine('Authorization'));
        $this->assertSame('page=1', $history[0]['request']->getUri()->getQuery());
    }

    public function test_it_fetches_and_caches_an_access_token_before_making_requests(): void
    {
        $history = [];
        $service = new TestBaseService($this->makeClient([
            new Response(200, [], json_encode([
                'responseBody' => [
                    'accessToken' => 'fresh-token',
                    'expiresIn' => 300,
                ],
            ])),
            new Response(200, [], json_encode(['message' => 'ok'])),
        ], $history));

        $result = $service->send(
            HttpMethod::POST,
            '/api/v1/test',
            ['amount' => 5000],
            ['reference' => 'abc123']
        );

        $this->assertSame(200, $result['status']);
        $this->assertSame(['message' => 'ok'], $result['body']);
        $this->assertCount(2, $history);
        $this->assertSame('/api/v1/auth/login', $history[0]['request']->getUri()->getPath());
        $this->assertSame('/api/v1/test', $history[1]['request']->getUri()->getPath());
        $this->assertSame('Bearer fresh-token', $history[1]['request']->getHeaderLine('Authorization'));
        $this->assertSame('reference=abc123', $history[1]['request']->getUri()->getQuery());
        $this->assertSame(json_encode(['amount' => 5000]), (string) $history[1]['request']->getBody());
        $this->assertSame('fresh-token', Config::get('accessToken'));
        $this->assertGreaterThan(time(), Config::get('expiresIn'));
    }

    public function test_it_returns_the_api_error_payload_for_request_exceptions_with_a_response(): void
    {
        Config::set('accessToken', 'cached-token');
        Config::set('expiresIn', time() + 300);

        $service = new TestBaseService($this->makeClient([
            new RequestException(
                'Validation failed',
                new Request('POST', '/api/v1/test'),
                new Response(422, [], json_encode(['message' => 'Invalid payload']))
            ),
        ]));

        $result = $service->send(HttpMethod::POST, '/api/v1/test', ['amount' => 0]);

        $this->assertSame(422, $result['status']);
        $this->assertSame('Invalid payload', $result['error']->message);
    }

    public function test_it_bubbles_transport_failures_in_the_current_implementation(): void
    {
        Config::set('accessToken', 'cached-token');
        Config::set('expiresIn', time() + 300);

        $service = new TestBaseService($this->makeClient([
            new ConnectException(
                'cURL error 6: Could not resolve host',
                new Request('GET', '/api/v1/test')
            ),
        ]));

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('Could not resolve host');

        $service->send(HttpMethod::GET, '/api/v1/test');
    }

    public function test_it_sets_access_token_values_in_config(): void
    {
        $service = new TestBaseService(new Client(['base_uri' => 'https://example.com']));

        $service->setAccessToken('manual-token', 1234567890);

        $this->assertSame('manual-token', Config::get('accessToken'));
        $this->assertSame(1234567890, Config::get('expiresIn'));
    }
}
