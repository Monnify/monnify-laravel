<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\RefundValidator;

class RefundService extends BaseService
{
    private RefundValidator $validator;

    public function __construct($client)
    {
        parent::__construct($client);
        $this->validator = new RefundValidator();
    }

    public function initialise(array $data): array
    {
        $this->validator->validateRefund($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/refunds/initiate-refund',
            $data
        );
    }

    public function all(int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber
        ];

        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/refunds',
            [],
            $parameters
        );
    }

    public function status(string $refundReference): array
    {
        return $this->makeRequest(
            HttpMethod::GET,
            '/api/v1/refunds/'.$refundReference
        );
    }
}