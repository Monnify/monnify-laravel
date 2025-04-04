<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class SubAccountValidator
{
    public function validateAccount(array $data): void
    {
        $validator = Validator::make(['sub' => $data], [
            'sub.*.subAccountCode' => 'sometimes|string',
            'sub.*.currencyCode' => 'required|string',
            'sub.*.accountNumber' => 'required|string',
            'sub.*.bankCode' => 'required|string',
            'sub.*.email' => 'required|string',
            'sub.*.defaultSplitPercentage' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}