<?php

namespace Monnify\MonnifyLaravel;

use Illuminate\Contracts\Foundation\Application;
use Monnify\MonnifyLaravel\Services\{
    BillsPaymentService,
    CustomerReservedAccountService,
    DirectDebitService,
    DisbursementService,
    InvoiceService,
    LimitProfileService,
    OtherService,
    PayCodeService,
    RecurringPaymentService,
    RefundService,
    SettlementService,
    SubAccountService,
    TransactionService,
    VerificationService,
    WalletService
};

/**
 * @property-read BillsPaymentService $billsPayment
 * @property-read CustomerReservedAccountService $customerReservedAccount
 * @property-read DirectDebitService $directDebitMandate
 * @property-read DisbursementService $transfer;
 * @property-read InvoiceService $invoice
 * @property-read LimitProfileService $limitProfile
 * @property-read OtherService $helper
 * @property-read PayCodeService $payCodeAPI
 * @property-read RecurringPaymentService $recurringPayment
 * @property-read RefundService $refund
 * @property-read SettlementService $settlements
 * @property-read SubAccountService $subAccount
 * @property-read TransactionService $transactions
 * @property-read VerificationService $verificationAPI
 * @property-read WalletService $wallet
 */
class Monnify
{
    protected array $resolved = [];

    public function __construct(private Application $app)
    {
    }

    public function __get(string $name): mixed
    {
        if (! method_exists($this, $name)) {
            throw new \RuntimeException("Undefined property [$name]");
        }

        return $this->resolved[$name]
            ??= $this->{$name}();
    }

    public function __isset(string $name): bool
    {
        return method_exists($this, $name);
    }

    public function transactions(): TransactionService
    {
        return $this->app->make(TransactionService::class);
    }

    public function customerReservedAccount(): CustomerReservedAccountService
    {
        return $this->app->make(CustomerReservedAccountService::class);
    }

    public function invoice(): InvoiceService
    {
        return $this->app->make(InvoiceService::class);
    }

    public function recurringPayment(): RecurringPaymentService
    {
        return $this->app->make(RecurringPaymentService::class);
    }

    public function directDebitMandate(): DirectDebitService
    {
        return $this->app->make(DirectDebitService::class);
    }

    public function subAccount(): SubAccountService
    {
        return $this->app->make(SubAccountService::class);
    }

    public function transfer(): DisbursementService
    {
        return $this->app->make(DisbursementService::class);
    }

    public function wallet(): WalletService
    {
        return $this->app->make(WalletService::class);
    }

    public function limitProfile(): LimitProfileService
    {
        return $this->app->make(LimitProfileService::class);
    }

    public function refund(): RefundService
    {
        return $this->app->make(RefundService::class);
    }

    public function settlements(): SettlementService
    {
        return $this->app->make(SettlementService::class);
    }

    public function verificationAPI(): VerificationService
    {
        return $this->app->make(VerificationService::class);
    }

    public function payCodeAPI(): PayCodeService
    {
        return $this->app->make(PayCodeService::class);
    }

    public function helper(): OtherService
    {
        return $this->app->make(OtherService::class);
    }

    public function billsPayment(): BillsPaymentService
    {
        return $this->app->make(BillsPaymentService::class);
    }
}
