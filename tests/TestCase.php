<?php

namespace Monnify\MonnifyLaravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Monnify\MonnifyLaravel\MonnifyServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonnifyServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Load environment variables from .env file
        $this->loadEnvironmentVariables();
        
        // Set up configuration values from environment variables
        $app['config']->set('monnify.api_key', env('MONNIFY_API_KEY', 'test-api-key'));
        $app['config']->set('monnify.secret_key', env('MONNIFY_SECRET_KEY', 'test-secret-key'));
        $app['config']->set('monnify.environment', env('MONNIFY_ENVIRONMENT', 'SANDBOX'));
        $app['config']->set('monnify.wallet_number', env('MONNIFY_WALLET_ACCOUNT_NUMBER'));
        $app['config']->set('monnify.contract_code', env('MONNIFY_CONTRACT_CODE'));
    }
    
    /**
     * Load environment variables from .env file
     */
    protected function loadEnvironmentVariables(): void
    {
        // Check if .env file exists in the package root
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            // Load environment variables from .env file
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname($envFile));
            $dotenv->load();
        }
    }
}
