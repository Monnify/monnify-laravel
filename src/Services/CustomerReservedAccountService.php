<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\CustomerReservedAccountValidator;

class CustomerReservedAccountService extends BaseService
{
    private CustomerReservedAccountValidator $validator;

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->validator = new CustomerReservedAccountValidator();
    }

    public function createGeneralAccount(array $data): array
    {
        $this->validator->validateCreateGeneralAccount($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v2/bank-transfer/reserved-accounts',
            $data
        );
    }

    public function createInvoiceAccount(array $data): array
    {
        $this->validator->validateCreateInvoiceAccount($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/bank-transfer/reserved-accounts',
            $data
        );
    }

    public function get(string $accountReference): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/bank-transfer/reserved-accounts/'. $accountReference
        );
    }

    public function addLinkedAccounts(string $accountReference, array $data = []): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateAddLinkedAccounts($data);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/bank-transfer/reserved-accounts/add-linked-accounts/'. $accountReference,
            $data
        );
    }

    public function updateBVN(string $accountReference, string $bvn): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/bank-transfer/reserved-accounts/update-customer-bvn/'. $accountReference,
            [
                'bvn' => $bvn
            ]
        );
    }

    public function allowedPaymentSource(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateAllowedPaymentSource($data);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/bank-transfer/reserved-accounts/update-payment-source-filter/'. $accountReference,
            $data
        );
    }

    public function updateSplitConfig(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateUpdateSplitConfig($data);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/bank-transfer/reserved-accounts/update-income-split-config/'. $accountReference,
            $data
        );
    }

    public function deallocateAccount(string $accountReference): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        return $this->makeRequest(
            HttpMethod::DELETE,
            '/api/v1/bank-transfer/reserved-accounts/reference/'. $accountReference
        );
    }

    public function transactions(string $accountReference, array $parameters = []): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }

        $this->validator->validateGetReservedAccountTransactions($parameters);
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/bank-transfer/reserved-accounts/'. $accountReference,
            [],
            $parameters
        );
    }

    public function updateKYCInfo(string $accountReference, array $data): array
    {
        if (empty($accountReference)) {
            throw new InvalidArgumentException('Account Reference must be provided');
        }
        
        $this->validator->validateUpdateKYCInfo($data);
        return $this->makeRequest(
            HttpMethod::PUT,
            '/api/v1/bank-transfer/reserved-accounts/'.$accountReference.'/kyc-info',
            $data
        );
    }
}