<?php

namespace Monnify\MonnifyLaravel\Services;

use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\RecurringPaymentValidator;

class RecurringPaymentService extends BaseService
{
    private RecurringPaymentValidator $validator;

    public function __construct($client)
    {
        parent::__construct($client);
        $this->validator = new RecurringPaymentValidator();
    }
    
    public function chargeCardToken(array $data): array
    {
        $this->validator->validateChargeCardToken($data);
        return $this->makeRequest(
            HttpMethod::POST,
            '/api/v1/merchant/cards/charge-card-token',
            $data
        );
    }
}