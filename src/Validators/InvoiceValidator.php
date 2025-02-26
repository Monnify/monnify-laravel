<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class InvoiceValidator
{
    public function validateAccount(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/',
            'currencyCode' => 'required|string',
            'accountReference' => 'sometimes|required|string',
            'invoiceReference' => 'required|string',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'contractCode' => 'required|string',
            'description' => 'required|string',
            'expiryDate' => 'required|string',
            'incomeSplitConfig' => 'sometimes|nullable|array',
            'redirectUrl' => 'sometimes|nullable|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}