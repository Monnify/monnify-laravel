<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class TransactionValidator
{
    public function validateInitialize(array $data): void
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:20|regex:/^\d*\.?\d*$/',
            'customerName' => 'string',
            'customerEmail' => 'required|email',
            'paymentReference' => 'required|string',
            'paymentDescription' => 'string',
            'currencyCode' => 'required|string',
            'contractCode' => 'required|string',
            'redirectUrl' =>'string',
            'paymentMethods' => 'array',
            'incomeSplitConfig' => 'array',
            'incomeSplitConfig.subAccountCode' => 'sometimes|string',
            'incomeSplitConfig.feeBearer' => 'boolean',
            'incomeSplitConfig.feePercentage' => 'numeric',
            'incomeSplitConfig.splitPercentage' => 'numeric',
            'incomeSplitConfig.splitAmount' => 'numeric'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validatePayWithBankTransfer(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'bankCode' => 'string'
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

    public function validateAuthorizeThreeDSCard(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'collectionChannel' => 'string',
            'card.number' => 'required|string',
            'card.pin' => 'required|string',
            'card.expiryMonth' => 'required|string',
            'card.expiryYear' => 'required|string',
            'card.cvv' => 'required|string',
            'apiKey' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateGetAllTransactions(array $data): void
    {
        $validator = Validator::make($data, [
            'page' => 'integer',
            'size' => 'integer',
            'paymentReference' => 'string',
            'transactionReference' => 'string',
            'fromAmount' => 'numeric|regex:/^\d*\.?\d*$/',
            'toAmount' => 'numeric|regex:/^\d*\.?\d*$/',
            'amount' => 'numeric|min:20|regex:/^\d*\.?\d*$/',
            'customerName' => 'string',
            'customerEmail' => 'email',
            'paymentStatus' => 'string',
            'from' => 'numeric|digits:13',
            'to' => 'numeric|digits:13',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}