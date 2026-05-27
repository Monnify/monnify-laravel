<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class BillsPaymentValidator
{
    public function validatePagination(array $data): void
    {
        $validator = Validator::make($data, [
            'size' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateBillers(array $data): void
    {
        $validator = Validator::make($data, [
            'size'          => 'sometimes|integer|min:1',
            'page'          => 'sometimes|integer|min:0',
            'category_code' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateProducts(array $data): void
    {
        $validator = Validator::make($data, [
            'biller_code' => 'required|string',
            'size'        => 'sometimes|integer|min:1',
            'page'        => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateCustomer(array $data): void
    {
        $validator = Validator::make($data, [
            'productCode' => 'required|string',
            'customerId'  => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateVend(array $data): void
    {
        $validator = Validator::make($data, [
            'productCode'         => 'required|string',
            'customerId'          => 'required|string',
            'vendAmount'          => 'required|numeric|min:0.01',
            'vendReference'       => 'required|string',
            'validationReference' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function validateRequery(array $data): void
    {
        $validator = Validator::make($data, [
            'vendReference' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}
