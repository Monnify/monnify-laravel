<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\DisbursementValidator;

class DisbursementService extends BaseService
{
    private DisbursementValidator $validator;

    public function __construct($client)
    {
        parent::__construct($client);
        $this->validator = new DisbursementValidator();
    }

    public function single(array $data, bool $asynchronous = false): array
    {
        $this->validator->validateSingleTransfer($data);
        if ($asynchronous) {
            $data['async'] = true;
        }
        
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v2/disbursements/single',
            $data
        );
    }

    public function bulk(array $data): array
    {
        $this->validator->validateBulkTransfer($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v2/disbursements/batch',
            $data
        );
    }

    public function authoriseSingle(array $data): array
    {
        $this->validator->validateAuthorization($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v2/disbursements/single/validate-otp',
            $data
        );
    }

    public function authoriseBulk(array $data): array
    {
        $this->validator->validateAuthorization($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v2/disbursements/batch/validate-otp',
            $data
        );
    }

    public function resendOTP(string $reference): array
    {
        $data['reference'] = $reference;
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v2/disbursements/single/resend-otp',
            $data
        );
    }

    public function singleStatus(string $reference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/disbursements/single/summary?reference='.$reference
        );
    }

    public function bulkStatus(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/disbursements/bulk/'.$batchReference.'/transactions',
            [],
            $parameters
        );
    }
    /**
     * @param string $type Type only have two correct value which is 'single' or 'bulk'
     */
    public function getAll(string $type = 'single', int $pageSize = 10, int $pageNumber = 0): array
    {
        $url = $type == 'single' ? '/api/v2/disbursements/single/transactions' : '/api/v2/disbursements/bulk/transactions';
        $parameters = [
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber
        ];
        
        return $this->makeRequest(
            HttpMethod::GET,
            $url,
            [],
            $parameters
        );
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

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v2/disbursements/search-transactions',
            [],
            $parameters
        );
    }
}