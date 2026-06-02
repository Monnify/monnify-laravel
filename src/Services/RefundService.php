<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Validators\RefundValidator;

class RefundService extends BaseService
{
    private RefundValidator $validator;

    public function __construct(Client $client, ?RefundValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new RefundValidator();
    }

    public function initialise(array $data): array
    {
        $this->validator->validateRefund($data);
        return $this->requestPost('/api/v1/refunds/initiate-refund', $data);
    }

    public function all(int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber
        ];

        return $this->requestGet('/api/v1/refunds', $parameters);
    }

    public function status(string $refundReference): array
    {
        if (empty($refundReference)) {
            throw new InvalidArgumentException('Refund Reference must be provided.');
        }

        return $this->requestGet('/api/v1/refunds/'. $refundReference);
    }
}
