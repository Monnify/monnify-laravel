<?php

namespace Monnify\MonnifyLaravel\Tests\Integration;

use Error;
use Monnify\MonnifyLaravel\Facades\Monnify as MonnifyFacade;
use Monnify\MonnifyLaravel\Monnify;
use Monnify\MonnifyLaravel\Services\CustomerReservedAccountService;
use Monnify\MonnifyLaravel\Services\DirectDebitService;
use Monnify\MonnifyLaravel\Services\DisbursementService;
use Monnify\MonnifyLaravel\Services\InvoiceService;
use Monnify\MonnifyLaravel\Services\LimitProfileService;
use Monnify\MonnifyLaravel\Services\OtherService;
use Monnify\MonnifyLaravel\Services\PayCodeService;
use Monnify\MonnifyLaravel\Services\RecurringPaymentService;
use Monnify\MonnifyLaravel\Services\RefundService;
use Monnify\MonnifyLaravel\Services\SettlementService;
use Monnify\MonnifyLaravel\Services\SubAccountService;
use Monnify\MonnifyLaravel\Services\TransactionService;
use Monnify\MonnifyLaravel\Services\VerificationService;
use Monnify\MonnifyLaravel\Services\WalletService;
use Monnify\MonnifyLaravel\Tests\TestCase;

class MonnifyTest extends TestCase
{
    public function test_it_registers_the_package_singleton(): void
    {
        $first = $this->app->make('monnify');
        $second = $this->app->make('monnify');

        $this->assertInstanceOf(Monnify::class, $first);
        $this->assertSame($first, $second);
    }

    public function test_it_resolves_the_monnify_facade_root(): void
    {
        $service = $this->app->make('monnify');

        $this->assertSame($service, MonnifyFacade::getFacadeRoot());
    }

    public function test_it_uses_the_correct_base_uri_for_sandbox(): void
    {
        $monnify = new Monnify('api-key', 'secret-key', 'SANDBOX');

        $this->assertSame(
            'https://sandbox.monnify.com',
            (string) $monnify->getClient()->getConfig('base_uri')
        );
    }

    public function test_it_uses_the_correct_base_uri_for_live(): void
    {
        $monnify = new Monnify('api-key', 'secret-key', 'LIVE');

        $this->assertSame(
            'https://api.monnify.com',
            (string) $monnify->getClient()->getConfig('base_uri')
        );
    }

    public function test_it_rejects_unknown_environments(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Unknown environment passed: STAGING');

        new Monnify('api-key', 'secret-key', 'STAGING');
    }

    public function test_it_initializes_all_service_accessors(): void
    {
        $monnify = $this->app->make('monnify');

        $this->assertInstanceOf(TransactionService::class, $monnify->transactions());
        $this->assertInstanceOf(CustomerReservedAccountService::class, $monnify->customerReservedAccount());
        $this->assertInstanceOf(InvoiceService::class, $monnify->invoice());
        $this->assertInstanceOf(RecurringPaymentService::class, $monnify->recurringPayment());
        $this->assertInstanceOf(DirectDebitService::class, $monnify->directDebitMandate());
        $this->assertInstanceOf(SubAccountService::class, $monnify->subAccount());
        $this->assertInstanceOf(DisbursementService::class, $monnify->transfer());
        $this->assertInstanceOf(WalletService::class, $monnify->wallet());
        $this->assertInstanceOf(LimitProfileService::class, $monnify->limitProfile());
        $this->assertInstanceOf(RefundService::class, $monnify->refund());
        $this->assertInstanceOf(SettlementService::class, $monnify->settlements());
        $this->assertInstanceOf(VerificationService::class, $monnify->verificationAPI());
        $this->assertInstanceOf(PayCodeService::class, $monnify->payCodeAPI());
        $this->assertInstanceOf(OtherService::class, $monnify->helper());
    }
}
