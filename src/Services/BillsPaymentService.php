<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Validators\BillsPaymentValidator;

class BillsPaymentService extends BaseService
{
    private BillsPaymentValidator $validator;

    public function __construct(Client $client, ?BillsPaymentValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new BillsPaymentValidator();
    }

    /**
     * Get all biller categories.
     */
    public function categories(int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber,
        ];

        $this->validator->validatePagination($parameters);

        return $this->requestGet('/api/v1/vas/bills-payment/biller-categories', $parameters);
    }

    /**
     * List billers, optionally filtered by category code.
     */
    public function billers(string $categoryCode = '', int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber,
        ];

        if (!empty($categoryCode)) {
            $parameters['category_code'] = $categoryCode;
        }

        $this->validator->validateBillers($parameters);

        return $this->requestGet('/api/v1/vas/bills-payment/billers', $parameters);
    }

    /**
     * Get products for a specific biller.
     */
    public function products(string $billerCode, int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'biller_code' => $billerCode,
            'size'        => $pageSize,
            'page'        => $pageNumber,
        ];

        $this->validator->validateProducts($parameters);

        return $this->requestGet('/api/v1/vas/bills-payment/biller-products', $parameters);
    }

    public function validateCustomer(array $data): array
    {
        $this->validator->validateCustomer($data);

        return $this->requestPost('/api/v1/vas/bills-payment/validate-customer', $data);
    }

    /**
     * Process (vend) a bill payment.
     */
    public function vend(array $data): array
    {
        $this->validator->validateVend($data);

        return $this->requestPost('/api/v1/vas/bills-payment/vend', $data);
    }

    /**
     * Check the status of a bill payment transaction.
     */
    public function requery(string $vendReference): array
    {
        $this->validator->validateRequery(['vendReference' => $vendReference]);

        return $this->requestGet('/api/v1/vas/bills-payment/requery', ['vendReference' => $vendReference]);
    }
}
