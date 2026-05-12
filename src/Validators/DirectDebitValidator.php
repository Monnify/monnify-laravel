<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class DirectDebitValidator
{
    public function validateMandate(array $data): void
    {
        $validator = Validator::make($data, [
            'contractCode' => 'required|string',
            'mandateReference' => 'required|string',
            'autoRenew' => 'boolean',
            'customerName' => 'required|string',
            'customerEmailAddress' => 'required|email',
            'customerPhoneNumber' => 'required|string',
            'customerAddress' => 'required|string',
            'customerAccountNumber' => 'required|string',
            'customerAccountBankCode' => 'required|string',
            'mandateDescription' => 'required|string',
            'mandateStartDate' => 'required|string',
            'mandateEndDate' => 'required|string',
            'mandateAmount' => 'numeric|min:20|regex:/^\d*\.?\d*$/',
            'debitAmount' => 'numeric|min:20|regex:/^\d*\.?\d*$/',
            'customerCancellation' => 'boolean'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateDebit(array $data): void
    {
        $validator = Validator::make($data, [
            'paymentReference' => 'required|string',
            'mandateCode' => 'required|string',
            'debitAmount' => 'numeric|min:20|regex:/^\d*\.?\d*$/',
            'narration' => 'required|string',
            'customerEmail' => 'required|email',
            'incomeSplit' => 'sometimes|array',
            'incomeSplit.*.subAccountCode' => 'required_with:incomeSplit|string',
            'incomeSplit.*.feeBearer' => 'sometimes|boolean',
            'incomeSplit.*.feePercentage' => 'sometimes|numeric',
            'incomeSplit.*.splitPercentage' => 'sometimes|numeric',
            'incomeSplit.*.splitAmount' => 'sometimes|numeric',
            'incomeSplitConfig' => 'sometimes|array',
            'incomeSplitConfig.*.subAccountCode' => 'required_with:incomeSplitConfig|string',
            'incomeSplitConfig.*.feeBearer' => 'sometimes|boolean',
            'incomeSplitConfig.*.feePercentage' => 'sometimes|numeric',
            'incomeSplitConfig.*.splitPercentage' => 'sometimes|numeric',
            'incomeSplitConfig.*.splitAmount' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}
