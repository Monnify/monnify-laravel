<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Validators\InvoiceValidator;

class InvoiceService extends BaseService
{
    private InvoiceValidator $validator;

    public function __construct(Client $client, ?InvoiceValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new InvoiceValidator();
    }

    public function create(array $data): array
    {
        $this->validator->validateAccount($data);
        return $this->requestPost('/api/v1/invoice/create', $data);
    }

    public function get(string $invoiceReference): array
    {
        if (empty($invoiceReference)) {
            throw new InvalidArgumentException('Invoice Reference must be provided.');
        }

        return $this->requestGet('/api/v1/invoice/'.$invoiceReference.'/details');
    }

    public function all(): array
    {
        return $this->requestGet('/api/v1/invoice/all');
    }

    public function cancel(string $invoiceReference): array
    {
        if (empty($invoiceReference)) {
            throw new InvalidArgumentException('Invoice Reference must be provided.');
        }

        return $this->requestDelete('/api/v1/invoice/'.$invoiceReference.'/cancel');
    }

    public function attachReservedAccount(array $data): array
    {
        $this->validator->validateAccount($data);
        return $this->requestPost('/api/v1/invoice/create', $data);
    }
}
