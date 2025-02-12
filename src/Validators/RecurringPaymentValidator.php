<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class RecurringPaymentValidator
{
    public function validateChargeCardToken(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/',
            'cardToken' => 'required|string',
            'customerName' => 'string',
            'customerEmail' => 'required|email',
            'paymentReference' => 'required|string',
            'paymentDescription' => 'string',
            'currencyCode' => 'string',
            'contractCode' => 'required|string',
            'apiKey' => 'required|string',
            'incomeSplitConfig' => 'array',
            'metaData.ipAddress' => 'string',
            'metaData.deviceType' => 'string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}