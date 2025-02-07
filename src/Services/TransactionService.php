<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\TransactionValidator;

class TransactionService extends BaseService
{
    private TransactionValidator $validator;

    public function __construct()
    {
        parent::__construct();
        $this->validator = new TransactionValidator();
    }

    public function initializeTransaction(array $data): array
    {
        $this->validator->validateInitializeTransaction($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/merchant/transactions/init-transaction',
            $data
        );
    }

    public function payWithBankTransfer(array $data): array
    {
        $this->validator->validatePayWithBankTransfer($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/merchant/bank-transfer/init-payment',
            $data
        );
    }

    public function chargeCard(array $data): array
    {
        $this->validator->validateChargeCard($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/merchant/cards/charge',
            $data
        );
    }

    public function authorizeOTP(array $data): array
    {
        $this->validator->validateAuthorizeOTP($data);
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/merchant/cards/otp/authorize',
            $data
        );
    }

    public function authorizeThreeDSCard(array $data): array
    {
        $this->validator->validateAuthorizeThreeDSCard($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/sdk/cards/secure-3d/authorize',
            $data
        );
    }

    public function getAllTransactions(array $parameters): array
    {
        $this->validator->validateGetAllTransactions($parameters);
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/transactions/search',
            [],
            $parameters
        );
    }

    public function getTransactionStatus(string $transactionReference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/transactions/'. $transactionReference
        );
    }

    public function getStatusByReference(array $parameters): array
    {
        $this->validator->validateGetStatusByReference($parameters);

        if (isset($parameters['transactionReference'])) {
            $queryParam = 'transactionReference=' . urlencode($parameters['transactionReference']);
        } elseif (isset($parameters['paymentReference'])) {
            $queryParam = 'paymentReference=' . urlencode($parameters['paymentReference']);
        } else {
            throw new InvalidArgumentException('Either transactionReference or paymentReference must be provided');
        }
        
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/merchant/transactions/query?' . $queryParam
        );
    }
}