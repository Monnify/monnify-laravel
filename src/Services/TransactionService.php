<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\TransactionValidator;
use InvalidArgumentException;

class TransactionService extends BaseService
{
    private TransactionValidator $validator;

    public function __construct($client)
    {
        parent::__construct($client);
        $this->validator = new TransactionValidator();
    }

    public function initialise(array $data): array
    {
        $this->validator->validateInitialize($data);
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
            HttpMethod::POST,
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

    public function transactions(array $parameters): array
    {
        $this->validator->validateGetAllTransactions($parameters);
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/transactions/search',
            [],
            $parameters
        );
    }

    public function status(string $transactionReference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/transactions/'. $transactionReference
        );
    }
    /**
     * @param string $referenceType referenceType have only two types which is 'payment' or 'transaction'
     */
    public function statusByReference(string $referenceType = 'transaction', string $reference, $parameters): array
    {
        if ($referenceType != 'transaction' || $referenceType != 'payment') {
            throw new InvalidArgumentException('Either transaction or payment must be provided as referenceType');
        } elseif ($referenceType == 'transaction') {
            $queryParam = 'transactionReference=' . $reference;
        } else {
            $queryParam = 'paymentReference=' . $reference;
        }
        
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/merchant/transactions/query?' . $queryParam
        );
    }
}