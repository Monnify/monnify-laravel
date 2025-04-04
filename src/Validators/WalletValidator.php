<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class WalletValidator
{
    public function validateCreate(array $data): void
    {
        $validator = Validator::make($data, [
            'walletReference' => 'string',
            'walletName' => 'string',
            'customerName' => 'string',
            'customerEmail' => 'email',
            'bvnDetails' => 'array',
            'bvnDetails.bvn' => 'string',
            'bvnDetails.bvnDateOfBirth' => 'string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}