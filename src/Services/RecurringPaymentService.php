<?php

namespace Monnify\MonnifyLaravel\Services;

use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Enums\HttpMethod;
use Monnify\MonnifyLaravel\Validators\RecurringPaymentValidator;

class RecurringPaymentService extends BaseService
{
    private RecurringPaymentValidator $validator;

    public function __construct(Client $client, ?RecurringPaymentValidator $validator = null)
    {
        parent::__construct($client);
        $this->validator = $validator ?? new RecurringPaymentValidator();
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
