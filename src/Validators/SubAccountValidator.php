<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class SubAccountValidator
{
    public function validateAccount(array $data): void
    {
        $validator = Validator::make($data, [
            'subAccountCode' => 'sometimes|required|string',
            'currencyCode' => 'required|string',
            'accountNumber' => 'required|string',
            'bankCode' => 'required|string',
            'email' => 'required|string',
            'defaultSplitPercentage' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}