<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class InvoiceValidator
{
    public function validateCreateInvoice(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/',
            'currencyCode' => 'required|string',
            'invoiceReference' => 'required|string',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'contractCode' => 'required|string',
            'description' => 'required|string',
            'expiryDate' => 'required|string',
            'incomeSplitConfig' => 'array',
            'redirectUrl' => 'string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateReservedAccount(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/',
            'invoiceReference' => 'required|string',
            'accountReference' => 'required|string',
            'description' => 'required|string',
            'currencyCode' => 'required|string',
            'contractCode' => 'required|string',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'expiryDate' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}