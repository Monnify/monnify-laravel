<?php

namespace Monnify\MonnifyLaravel;

use Error;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

use Monnify\MonnifyLaravel\Services\{TransactionService};

class Monnify
{
    public TransactionService $transactions;

    public function __construct(
        private string $apiKey,
        private string $secretKey,
        private string $environment
    ) {
        if ($environment !== 'SANDBOX' || $environment !== 'LIVE') {
            throw new Error("Unknown environment passed: $environment, Please specify between SANDBOX and LIVE");
        }
        
        $key = base64_encode("$apiKey:$secretKey");
        Config::set('basic_key', 'Basic '.$key);

        $client = new Client([
            'base_uri' => $environment === 'SANDBOX' ? 'https://sandbox.monnify.com' : 'https://api.monnify.com',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->transactions = new TransactionService($client);
    }
}