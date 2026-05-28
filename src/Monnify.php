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

class Monnify
{
    public function __construct(private Application $app)
    {
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
