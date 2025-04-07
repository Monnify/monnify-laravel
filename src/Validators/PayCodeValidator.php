<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class PayCodeValidator
{
    public function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'beneficiaryName' => 'required|string',
            'amount' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/',
            'paycodeReference' => 'required|string',
            'expiryDate' => 'required|string',
            'clientId' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateHistoryParameters(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'sometimes|string',
            'beneficiaryName' => 'sometimes|string',
            'transactionStatus' => 'sometimes|string',
            'from' => 'sometimes|integer',
            'to' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}