<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

readonly class TransactionValidator
{
    public function validateInitializeTransaction(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:20',
            'customerName' => 'string|nullable',
            'customerEmail' => 'required|email',
            'paymentReference' => 'required|string',
            'paymentDescription' => 'string|nullable',
            'currencyCode' => 'required|string|size:3',
            'contractCode' => 'required|string',
            'redirectUrl' =>'string|nullable',
            'paymentMethods' => 'string|nullable',
            'metaData' => 'array|nullable',
            'incomeSplitConfig' => 'array|nullable',
            'incomeSplitConfig.subAccountCode' => 'required|string',
            'incomeSplitConfig.feeBearer' => 'boolean|nullable',
            'incomeSplitConfig.feePercentage' => 'numeric|nullable',
            'incomeSplitConfig.splitPercentage' => 'numeric|nullable',
            'incomeSplitConfig.splitAmount' => 'numeric|nullable'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validatePayWithBankTransfer(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'bankCode' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateChargeCard(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'collectionChannel' => 'required|string',
            'card.number' => 'required|string',
            'card.pin' => 'required|string',
            'card.expiryMonth' => 'required|string',
            'card.expiryYear' => 'required|string',
            'card.cvv' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateAuthorizeOTP(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'collectionChannel' => 'required|string',
            'tokenId' => 'required|string',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateRefund(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|gt:0',
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}