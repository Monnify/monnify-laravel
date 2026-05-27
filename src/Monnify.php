<?php

namespace Monnify\MonnifyLaravel;

use GuzzleHttp\Client;
use InvalidArgumentException;

use Monnify\MonnifyLaravel\Services\{
    TransactionService,
    CustomerReservedAccountService,
    InvoiceService,
    RecurringPaymentService,
    DirectDebitService,
    SubAccountService,
    DisbursementService,
    WalletService,
    LimitProfileService,
    RefundService,
    SettlementService,
    VerificationService,
    PayCodeService,
    OtherService,
    BillsPaymentService
};

class Monnify
{
    protected Client $client;
    public TransactionService $transactions;
    public CustomerReservedAccountService $customerReservedAccount;
    public InvoiceService $invoice;
    public RecurringPaymentService $recurringPayment;
    public DirectDebitService $directDebitMandate;
    public SubAccountService $subAccount;
    public DisbursementService $transfer;
    public WalletService $wallet;
    public LimitProfileService $limitProfile;
    public RefundService $refund;
    public SettlementService $settlements;
    public VerificationService $verificationAPI;
    public PayCodeService $payCodeAPI;
    public OtherService $helper;
    public BillsPaymentService $billsPayment;

    public function __construct(
        private string $apiKey,
        private string $secretKey,
        private string $environment
    ) {
        if ($environment !== 'SANDBOX' && $environment !== 'LIVE') {
            throw new InvalidArgumentException("Unknown environment passed: $environment, Please specify between SANDBOX or LIVE");
        }

        // Create single client instance
        $this->client = new Client([
            'base_uri' => $environment === 'SANDBOX' ? 'https://sandbox.monnify.com' : 'https://api.monnify.com',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        // Initialize services with the same client instance
        $this->initializeServices();
    }

    protected function initializeServices(): void
    {
        $this->transactions = new TransactionService($this->client);
        $this->customerReservedAccount = new CustomerReservedAccountService($this->client);
        $this->invoice = new InvoiceService($this->client);
        $this->recurringPayment = new RecurringPaymentService($this->client);
        $this->directDebitMandate = new DirectDebitService($this->client);
        $this->subAccount = new SubAccountService($this->client);
        $this->transfer = new DisbursementService($this->client);
        $this->wallet = new WalletService($this->client);
        $this->limitProfile = new LimitProfileService($this->client);
        $this->refund = new RefundService($this->client);
        $this->settlements = new SettlementService($this->client);
        $this->verificationAPI = new VerificationService($this->client);
        $this->payCodeAPI = new PayCodeService($this->client);
        $this->helper = new OtherService($this->client);
        $this->billsPayment = new BillsPaymentService($this->client);
    }

    // Add getter for testing purposes
    public function getClient(): Client
    {
        return $this->client;
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