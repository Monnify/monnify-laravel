<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Validators\DisbursementValidator;

class DisbursementService extends BaseService
{
    private DisbursementValidator $validator;

    public function __construct(Client $client, ?DisbursementValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new DisbursementValidator();
    }

    public function single(array $data, bool $asynchronous = false): array
    {
        $this->validator->validateSingleTransfer($data);
        if ($asynchronous) {
            $data['async'] = true;
        }
        
        return $this->requestPost('/api/v2/disbursements/single', $data);
    }

    public function bulk(array $data): array
    {
        $this->validator->validateBulkTransfer($data);
        return $this->requestPost('/api/v2/disbursements/batch', $data);
    }

    public function authoriseSingle(array $data): array
    {
        $this->validator->validateAuthorization($data);
        return $this->requestPost('/api/v2/disbursements/single/validate-otp', $data);
    }

    public function authoriseBulk(array $data): array
    {
        $this->validator->validateAuthorization($data);
        return $this->requestPost('/api/v2/disbursements/batch/validate-otp', $data);
    }

    public function resendOTP(string $reference): array
    {
        if (empty($reference)) {
            throw new InvalidArgumentException("Reference must be provided");
        }
        
        $data = ['reference' => $reference];
        return $this->requestPost('/api/v2/disbursements/single/resend-otp', $data);
    }

    public function bulkResendOTP(string $reference): array
    {
        if (empty($reference)) {
            throw new InvalidArgumentException("Reference must be provided");
        }

        $data = ['reference' => $reference];
        return $this->requestPost('/api/v2/disbursements/batch/resend-otp', $data);
    }

    public function bulkBatchSummary(string $batchReference): array
    {
        if (empty($batchReference)) {
            throw new InvalidArgumentException("Batch Reference must be provided");
        }

        return $this->requestGet('/api/v2/disbursements/batch/summary', ['reference' => $batchReference]);
    }

    public function singleStatus(string $reference): array
    {
        return $this->requestGet('/api/v2/disbursements/single/summary', ['reference' => $reference]);
    }

    public function bulkStatus(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];

        return $this->requestGet('/api/v2/disbursements/bulk/'.$batchReference.'/transactions', $parameters);
    }
    /**
     * @param string $type Accepted values: single, bulk.
     *
     * Note: bulk listing requires the disbursement feature to be enabled on your Monnify merchant account.
     * Calling with $type = 'bulk' on an account without this feature returns 404.
     */
    public function all(string $type = 'single', int $pageSize = 10, int $pageNumber = 0): array
    {
        if (!in_array($type, ['single', 'bulk'], true)) {
            throw new InvalidArgumentException("Type must be 'single' or 'bulk'.");
        }

        $url = $type === 'single' ? '/api/v2/disbursements/single/transactions' : '/api/v2/disbursements/bulk/transactions';
        $parameters = [
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];

        return $this->requestGet($url, $parameters);
    }

    public function bulkTransaction(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->bulkStatus($batchReference, $pageSize, $pageNumber);
    }

    public function search(string $sourceAccountNumber,  int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'sourceAccountNumber' => $sourceAccountNumber,
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];

        return $this->requestGet('/api/v2/disbursements/search-transactions', $parameters);
    }

    public function walletBalance(string $accountNumber): array
    {
        if (empty($accountNumber)) {
            throw new InvalidArgumentException('Account Number must be provided.');
        }

        return $this->requestGet('/api/v2/disbursements/wallet-balance', ['accountNumber' => $accountNumber]);
    }
}
