<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Validators\CustomerReservedAccountValidator;

class CustomerReservedAccountService extends BaseService
{
    private CustomerReservedAccountValidator $validator;

    public function __construct(Client $client, ?CustomerReservedAccountValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new CustomerReservedAccountValidator();
    }

    public function createGeneralAccount(array $data): array
    {
        $this->validator->validateCreateGeneralAccount($data);
        return $this->requestPost('/api/v2/bank-transfer/reserved-accounts', $data);
    }

    public function createInvoiceAccount(array $data): array
    {
        $this->validator->validateCreateInvoiceAccount($data);
        return $this->requestPost('/api/v1/bank-transfer/reserved-accounts', $data);
    }

    public function get(string $accountReference): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->requestGet('/api/v2/bank-transfer/reserved-accounts/'. $accountReference);
    }

    public function addLinkedAccounts(string $accountReference, array $data = []): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateAddLinkedAccounts($data);
        return $this->requestPut('/api/v1/bank-transfer/reserved-accounts/add-linked-accounts/'. $accountReference, $data);
    }

    public function updateBVN(string $accountReference, string $bvn): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->requestPut('/api/v1/bank-transfer/reserved-accounts/update-customer-bvn/'. $accountReference, ['bvn' => $bvn]);
    }

    public function allowedPaymentSource(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateAllowedPaymentSource($data);
        return $this->requestPut('/api/v1/bank-transfer/reserved-accounts/update-payment-source-filter/'. $accountReference, $data);
    }

    public function updateSplitConfig(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateUpdateSplitConfig($data);
        return $this->requestPut('/api/v1/bank-transfer/reserved-accounts/update-income-split-config/'. $accountReference, $data);
    }

    public function deallocateAccount(string $accountReference): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->requestDelete('/api/v1/bank-transfer/reserved-accounts/reference/'. $accountReference);
    }

    public function transactions(string $accountReference, array $parameters = []): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateGetReservedAccountTransactions($parameters);
        return $this->requestGet('/api/v1/bank-transfer/reserved-accounts/transactions'. $accountReference, $parameters);
    }

    public function updateKYCInfo(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }
        
        $this->validator->validateUpdateKYCInfo($data);
        return $this->requestPut('/api/v1/bank-transfer/reserved-accounts/'.$accountReference.'/kyc-info', $data);
    }
}
