<?php

namespace Monnify\MonnifyLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Monnify\MonnifyLaravel\Services\TransactionService transactions()
 * @method static \Monnify\MonnifyLaravel\Services\CustomerReservedAccountService customerReservedAccount()
 * @method static \Monnify\MonnifyLaravel\Services\InvoiceService invoice()
 * @method static \Monnify\MonnifyLaravel\Services\RecurringPaymentService recurringPayment()
 * @method static \Monnify\MonnifyLaravel\Services\DirectDebitService directDebitMandate()
 * @method static \Monnify\MonnifyLaravel\Services\SubAccountService subAccount()
 * @method static \Monnify\MonnifyLaravel\Services\DisbursementService transfer()
 * @method static \Monnify\MonnifyLaravel\Services\WalletService wallet()
 * @method static \Monnify\MonnifyLaravel\Services\LimitProfileService limitProfile()
 * @method static \Monnify\MonnifyLaravel\Services\RefundService refund()
 * @method static \Monnify\MonnifyLaravel\Services\SettlementService settlements()
 * @method static \Monnify\MonnifyLaravel\Services\VerificationService verificationAPI()
 * @method static \Monnify\MonnifyLaravel\Services\PayCodeService payCodeAPI()
 * @method static \Monnify\MonnifyLaravel\Services\OtherService helper()
 * @method static \Monnify\MonnifyLaravel\Services\BillsPaymentService billsPayment()
 *
 * @see \Monnify\MonnifyLaravel\Monnify
 */
class Monnify extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'monnify';
    }
} 