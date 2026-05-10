<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Validators\TransactionValidator;
use InvalidArgumentException;
use GuzzleHttp\Client;

class TransactionService extends BaseService
{
    private TransactionValidator $validator;

    public function __construct(Client $client, ?TransactionValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new TransactionValidator();
    }

    public function initialise(array $data): array
    {
        $this->validator->validateInitialize($data);
        return $this->requestPost('/api/v1/merchant/transactions/init-transaction', $data);
    }

    public function payWithBankTransfer(array $data): array
    {
        $this->validator->validatePayWithBankTransfer($data);
        return $this->requestPost('/api/v1/merchant/bank-transfer/init-payment', $data);
    }

    public function chargeCard(array $data): array
    {
        $this->validator->validateChargeCard($data);
        return $this->requestPost('/api/v1/merchant/cards/charge', $data);
    }

    public function authorizeOTP(array $data): array
    {
        $this->validator->validateAuthorizeOTP($data);
        return $this->requestPost('/api/v1/merchant/cards/otp/authorize', $data);
    }

    public function authorizeThreeDSCard(array $data): array
    {
        $this->validator->validateAuthorizeThreeDSCard($data);
        return $this->requestPost('/api/v1/sdk/cards/secure-3d/authorize', $data);
    }

    public function all(array $parameters = []): array
    {
        $this->validator->validateGetAllTransactions($parameters);
        return $this->requestGet('/api/v1/transactions/search', $parameters);
    }

    public function status(string $transactionReference): array
    {
        if (empty($transactionReference)) {
            throw new InvalidArgumentException('Transaction Reference must be provided');
        }
        return $this->requestGet('/api/v2/transactions/'. $transactionReference);
    }
    /**
     * @param string $referenceType referenceType have only two types which is 'payment' or 'transaction'
     */
    public function statusByReference(string $reference, string $referenceType = 'transaction'): array
    {
        if ($referenceType !== 'transaction' && $referenceType !== 'payment') {
            throw new InvalidArgumentException('Either transaction or payment must be provided as referenceType');
        }
        
        $paramKey = $referenceType === 'transaction' ? 'transactionReference' : 'paymentReference';
        return $this->requestGet('/api/v2/merchant/transactions/query', [$paramKey => $reference]);
    }
}
