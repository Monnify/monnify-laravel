<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class RefundValidator
{
    public function validateRefund(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'refundAmount' => 'required|string',
            'refundReference' => 'required|string',
            'refundReason' => 'required|string',
            'customerNote' => 'required|string',
            'destinationAccountNumber' => 'sometimes|string',
            'destnationAccountBankCode' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}