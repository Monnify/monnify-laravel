<?php

namespace Monnify\MonnifyLaravel\Enums;

enum DisbursementValidationFailure: string
{
    case BREAK = 'BREAK';
    case CONTINUE = 'CONTINUE';
}