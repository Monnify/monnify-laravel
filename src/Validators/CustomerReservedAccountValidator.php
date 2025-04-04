<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class CustomerReservedAccountValidator
{
    public function validateCreateGeneralAccount(array $data): void
    {
        $validator = Validator::make($data, [
            'accountReference' => 'required|string',
            'accountName' => 'required|string',
            'currencyCode' => 'required|string',
            'contractCode' => 'required|string',
            'customerEmail' => 'required|email',
            'customerName' => 'string',
            'getAllAvailableBanks' => 'required|boolean',
            'restrictPaymentSource' => 'boolean',
            'incomeSplitConfig' => 'array',
            'allowedPaymentSource' => 'nullable',
            'bvn' => Rule::requiredIf(fn() => empty($data['nin'])),
            'nin' => Rule::requiredIf(fn() => empty($data['bvn']))
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateCreateInvoiceAccount(array $data): void
    {
        $validator = Validator::make($data, [
            'contractCode' => 'required|string',
            'accountName' => 'required|string',
            'currencyCode' => 'required|string',
            'accountReference' => 'required|string',
            'customerName' => 'string',
            'customerEmail' => 'required|email',
            'reservedAccountType' => 'string',
            'bvn' => 'sometimes',
            'nin' => 'sometimes'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateAddLinkedAccounts(array $data): void
    {
        $validator = Validator::make($data, [
            'getAllAvailableBanks' => 'boolean',
            'preferredBanks' => 'array'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateAllowedPaymentSource(array $data): void
    {
        $validator = Validator::make($data, [
            'restrictPaymentSource' => 'boolean',
            'allowedPaymentSource.bvns' => 'array',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateUpdateSplitConfig(array $data): void
    {
        $validator = Validator::make(['splits' => $data], [
            'splits' => 'required|array',
            'splits.*.subAccountCode' => 'string',
            'splits.*.feeBearer' => 'boolean',
            'splits.*.feePercentage' => 'numeric|min:0|regex:/^\d*\.?\d*$/',
            'splits.*.splitPercentage' => 'numeric|min:0|regex:/^\d*\.?\d*$/',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateGetReservedAccountTransactions(array $data): void
    {
        $validator = Validator::make($data, [
            'page' => 'integer',
            'size' => 'integer'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
    public function validateUpdateKYCInfo(array $data): void
    {
        $validator = Validator::make($data, [
            'bvn' => 'string',
            'nin' => 'string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}