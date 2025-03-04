<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\DisbursementValidationFailure;

class WalletValidator
{
    public function validateCreate(array $data): void
    {
        $validator = Validator::make($data, [
            'walletReference' => 'required|string',
            'walletName' => 'required|string',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'bvn' => 'required|string',
            'bvnDateOfBirth' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}