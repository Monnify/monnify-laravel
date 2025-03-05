<?php

namespace Monnify\MonnifyLaravel\Validators;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class VerificationValidator
{
    public function validateBVNInformation(array $data): void
    {
        $validator = Validator::make($data, [
            'bvn' => 'required|string',
            'name' => 'required|string',
            'dateOfBirth' => 'required|string',
            'mobileNo' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }
}