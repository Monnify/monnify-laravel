<?php

namespace Monnify\MonnifyLaravel\Tests;

use Monnify\MonnifyLaravel\MonnifyServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonnifyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('monnify.api_key', 'test-api-key');
        $app['config']->set('monnify.secret_key', 'test-secret-key');
        $app['config']->set('monnify.environment', 'SANDBOX');
        $app['config']->set('monnify.wallet_number', '1234567890');
        $app['config']->set('monnify.contract_code', '0987654321');
        $app['config']->set('monnify.sandbox_url', 'https://sandbox.monnify.com');
        $app['config']->set('monnify.live_url', 'https://api.monnify.com');
    }
}
