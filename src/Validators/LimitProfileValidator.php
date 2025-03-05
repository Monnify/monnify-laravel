<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class LimitProfileValidator
{
    public function validateLimitProfile(array $data): void
    {
        $validator = Validator::make($data, [
            'limitProfileName' => 'required|string',
            'singleTransactionValue' => 'required|numeric',
            'dailyTransactionValue' => 'required|numeric',
            'dailyTransactionVolume' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateReserveAccount(array $data): void
    {
        $validator = Validator::make($data, [
            'accountReference' => 'required|string',
            'limitProfileCode' => 'required|string',
            'accountName' => 'required|string',
            'currencyCode' => 'sometimes|string',
            'contractCode' => 'required|string',
            'customerEmail' => 'sometimes|string',
            'incomeSplitConfig' => 'sometimes'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}