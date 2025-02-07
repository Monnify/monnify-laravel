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

    public function validateAuthorizeThreeDSCard(array $data): void
    {
        $validator = Validator::make($data, [
            'transactionReference' => 'required|string',
            'collectionChannel' => 'required|string',
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
            'page' => 'integer|nullable',
            'size' => 'integer|nullable',
            'paymentReference' => 'string|nullable',
            'transactionReference' => 'string|nullable',
            'fromAmount' => 'numeric|nullable',
            'toAmount' => 'numeric|nullable',
            'amount' => 'numeric|nullable',
            'customerName' => 'string|nullable',
            'customerEmail' => 'email|nullable',
            'paymentStatus' => 'string|nullable',
            'from' => 'string|nullable',
            'to' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateReference(array $data): void
    {
        $rules = [];
        
        // Set validation rules based on which reference is provided
        if (isset($data['transactionReference'])) {
            $rules['transactionReference'] = 'required|string';
        } elseif (isset($data['paymentReference'])) {
            $rules['paymentReference'] = 'required|string';
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateGetStatusByReference(array $data): void
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data array cannot be empty');
        }

        $hasTransaction = isset($data['transactionReference']);
        $hasPayment = isset($data['paymentReference']);

        if (!$hasTransaction && !$hasPayment) {
            throw new InvalidArgumentException('Either transactionReference or paymentReference must be provided');
        }

        if ($hasTransaction && $hasPayment) {
            throw new InvalidArgumentException('Only one reference type should be provided');
        }

        // Validate the provided reference
        $this->validateReference($data);
    }
}