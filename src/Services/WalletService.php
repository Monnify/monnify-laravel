<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\WalletValidator;

class WalletService extends BaseService
{
    private WalletValidator $validator;

    public function __construct()
    {
        parent::__construct();
        $this->validator = new WalletValidator();
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

    public function get(string $customerEmail, int $pageSize = 10, int $pageNumber = 1): array
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

    public function balance(string $walletReference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/disbursements/wallet/balance?walletReference='. $walletReference
        );
    }

    public function transactions(string $accountNumber, int $pageSize = 10, int $pageNumber = 1): array
    {
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