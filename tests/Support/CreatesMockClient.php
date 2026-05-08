<?php

namespace Monnify\MonnifyLaravel\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

trait CreatesMockClient
{
    /**
     * @param array<int, mixed> $queue
     * @param array<int, array<string, mixed>> $history
     */
    protected function makeClient(array $queue, array &$history = []): Client
    {
        $mock = new MockHandler($queue);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        return new Client([
            'base_uri' => 'https://example.com',
            'handler' => $stack,
        ]);
    }
}
