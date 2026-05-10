<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\WalletValidator;

class WalletService extends BaseService
{
    private WalletValidator $validator;

    public function __construct(Client $client, ?WalletValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new WalletValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validateCreate($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/disbursements/wallet',
            $data
        );
    }

    public function get(string $customerEmail, int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'customerEmail' => $customerEmail,
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/disbursements/wallet',
            [],
            $parameters
        );
    }

    public function balance(string $accountNumber): array
    {
        if (empty($accountNumber)) {
            throw new InvalidArgumentException('Account Number must provided.');
        }

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/disbursements/wallet/balance',
            [],
            ['accountNumber' => $accountNumber]
        );
    }

    public function transactions(string $accountNumber, int $pageSize = 10, int $pageNumber = 0): array
    {
        if (empty($accountNumber)) {
            throw new InvalidArgumentException('Account Number must provided.');
        }

        $parameters = [
            'accountNumber' => $accountNumber,
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/disbursements/wallet/transactions',
            [],
            $parameters
        );
    }
}
