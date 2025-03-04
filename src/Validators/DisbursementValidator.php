<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Monnify\MonnifyLaravel\Enums\DisbursementValidationFailure;

class DisbursementValidator
{
    public function validateSingleTransfer(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'numeric|min:20|regex:/^\d*\.?\d*$/',
            'reference' => 'required|string',
            'narration' => 'required|string',
            'destinationBankCode' => 'required|string|max:3',
            'destinationAccountNumber' => 'required|string',
            'currency' => 'required|string',
            'sourceAccountNumber' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateBulkTransfer(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'batchReference' => 'required|string',
            'narration' => 'required|string',
            'sourceAccountNumber' => 'required|string',
            'notificationInterval' => 'required|integer',
            'onValidationFailure' => ['required', Rule::enum(DisbursementValidationFailure::class)],
            'transactionList' => 'required|array',
            'transactionList.*.amount' => 'numeric|min:20|regex:/^\d*\.?\d*$/',
            'transactionList.*.reference' => 'required|string',
            'transactionList.*.narration' => 'required|string',
            'transactionList.*.destinationBankCode' => 'required|string|max:3',
            'transactionList.*.destinationAccountNumber' => 'required|string',
            'transactionList.*.currency' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateAuthorization(array $data): void
    {
        $validator = Validator::make($data, [
            'reference' => 'required|string',
            'authorizationCode' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}