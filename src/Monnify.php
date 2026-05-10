<?php

namespace Monnify\MonnifyLaravel;

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
    public function __construct(
        private TransactionService $transactions,
        private BillsPaymentService $billsPayment,
        private CustomerReservedAccountService $customerReservedAccount,
        private InvoiceService $invoice,
        private RecurringPaymentService $recurringPayment,
        private DirectDebitService $directDebitMandate,
        private SubAccountService $subAccount,
        private DisbursementService $transfer,
        private WalletService $wallet,
        private LimitProfileService $limitProfile,
        private RefundService $refund,
        private SettlementService $settlements,
        private VerificationService $verificationAPI,
        private PayCodeService $payCodeAPI,
        private OtherService $helper
    ) {
    }

    public function transactions(): TransactionService
    {
        return $this->transactions;
    }

    public function customerReservedAccount(): CustomerReservedAccountService
    {
        return $this->customerReservedAccount;
    }

    public function invoice(): InvoiceService
    {
        return $this->invoice;
    }

    public function recurringPayment(): RecurringPaymentService
    {
        return $this->recurringPayment;
    }

    public function directDebitMandate(): DirectDebitService
    {
        return $this->directDebitMandate;
    }

    public function subAccount(): SubAccountService
    {
        return $this->subAccount;
    }

    public function transfer(): DisbursementService
    {
        return $this->transfer;
    }

    public function wallet(): WalletService
    {
        return $this->wallet;
    }

    public function limitProfile(): LimitProfileService
    {
        return $this->limitProfile;
    }

    public function refund(): RefundService
    {
        return $this->refund;
    }

    public function settlements(): SettlementService
    {
        return $this->settlements;
    }

    public function verificationAPI(): VerificationService
    {
        return $this->verificationAPI;
    }

    public function payCodeAPI(): PayCodeService
    {
        return $this->payCodeAPI;
    }

    public function helper(): OtherService
    {
        return $this->helper;
    }

    public function billsPayment(): BillsPaymentService
    {
        return $this->billsPayment;
    }
}
