<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\InvoiceValidator;

class InvoiceService extends BaseService
{
    private InvoiceValidator $validator;

    public function __construct($client)
    {
        parent::__construct($client);
        $this->validator = new InvoiceValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validateAccount($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/invoice/create',
            $data
        );
    }

    public function get(string $invoiceReference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/invoice/'.$invoiceReference.'/details'
        );
    }

    public function getAll(): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/invoice/all'
        );
    }

    public function cancel(string $invoiceReference): array
    {
        return $this->makeRequest(
            HttpMethod::DELETE,
            '/api/v1/invoice/'.$invoiceReference.'/cancel'
        );
    }

    public function attachReservedAccount(array $data): array
    {
        $this->validator->validateAccount($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/invoice/create',
            $data
        );
    }
}