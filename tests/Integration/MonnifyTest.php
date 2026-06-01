<?php

namespace Monnify\MonnifyLaravel\Tests\Integration;

use Error;
use GuzzleHttp\Client;
use Monnify\MonnifyLaravel\Facades\Monnify as MonnifyFacade;
use Monnify\MonnifyLaravel\Monnify;
use Monnify\MonnifyLaravel\MonnifyServiceProvider;
use Monnify\MonnifyLaravel\Services\BillsPaymentService;
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
use PHPUnit\Framework\Attributes\Test;

class MonnifyTest extends TestCase
{
    #[Test]
    public function it_registers_the_package_singleton(): void
    {
        $first = $this->app->make(MonnifyServiceProvider::BINDING);
        $second = $this->app->make(MonnifyServiceProvider::BINDING);

        $this->assertInstanceOf(Monnify::class, $first);
        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_resolves_the_monnify_facade_root(): void
    {
        $service = $this->app->make(MonnifyServiceProvider::BINDING);

        $this->assertSame($service, MonnifyFacade::getFacadeRoot());
    }

    #[Test]
    public function it_uses_a_package_specific_http_client_binding(): void
    {
        $client = $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING);
        $appClient = new Client(['base_uri' => 'https://app.example.com']);

        $this->assertInstanceOf(Client::class, $client);

        $this->app->instance(Client::class, $appClient);

        $packageClient = $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING);
        $monnify = $this->app->make(MonnifyServiceProvider::BINDING);

        $this->assertNotSame($appClient, $packageClient);
        $this->assertSame($packageClient, $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING));
        $this->assertSame($this->app->make(TransactionService::class), $monnify->transactions());
    }

    #[Test]
    public function it_uses_the_correct_base_uri_for_sandbox(): void
    {
        $this->app['config']->set('monnify.environment', 'SANDBOX');
        $client = $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING);

        $this->assertSame(
            'https://sandbox.monnify.com',
            (string) $client->getConfig('base_uri')
        );
    }

    #[Test]
    public function it_uses_the_correct_base_uri_for_live(): void
    {
        $this->app['config']->set('monnify.environment', 'LIVE');
        $client = $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING);

        $this->assertSame(
            'https://api.monnify.com',
            (string) $client->getConfig('base_uri')
        );
    }

    #[Test]
    public function it_configures_default_http_headers_on_the_client(): void
    {
        $client = $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING);

        $this->assertSame('application/json', $client->getConfig('headers')['Accept']);
        $this->assertSame('application/json', $client->getConfig('headers')['Content-Type']);
    }

    #[Test]
    public function it_rejects_unknown_environments(): void
    {
        $this->app['config']->set('monnify.environment', 'STAGING');

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Unknown environment passed: STAGING');

        $this->app->make(MonnifyServiceProvider::HTTP_CLIENT_BINDING);
    }

    #[Test]
    public function it_resolves_service_accessors_via_property_access(): void
    {
        $monnify = $this->app->make(MonnifyServiceProvider::BINDING);

        $this->assertSame($this->app->make(TransactionService::class), $monnify->transactions);
        $this->assertSame($this->app->make(BillsPaymentService::class), $monnify->billsPayment);
        $this->assertSame($this->app->make(CustomerReservedAccountService::class), $monnify->customerReservedAccount);
        $this->assertSame($this->app->make(InvoiceService::class), $monnify->invoice);
        $this->assertSame($this->app->make(RecurringPaymentService::class), $monnify->recurringPayment);
        $this->assertSame($this->app->make(DirectDebitService::class), $monnify->directDebitMandate);
        $this->assertSame($this->app->make(SubAccountService::class), $monnify->subAccount);
        $this->assertSame($this->app->make(DisbursementService::class), $monnify->transfer);
        $this->assertSame($this->app->make(WalletService::class), $monnify->wallet);
        $this->assertSame($this->app->make(LimitProfileService::class), $monnify->limitProfile);
        $this->assertSame($this->app->make(RefundService::class), $monnify->refund);
        $this->assertSame($this->app->make(SettlementService::class), $monnify->settlements);
        $this->assertSame($this->app->make(VerificationService::class), $monnify->verificationAPI);
        $this->assertSame($this->app->make(PayCodeService::class), $monnify->payCodeAPI);
        $this->assertSame($this->app->make(OtherService::class), $monnify->helper);
    }

    #[Test]
    public function it_resolves_service_accessors_from_container_singletons(): void
    {
        $monnify = $this->app->make(MonnifyServiceProvider::BINDING);

        $this->assertSame($this->app->make(TransactionService::class), $monnify->transactions());
        $this->assertSame($this->app->make(BillsPaymentService::class), $monnify->billsPayment());
        $this->assertSame($this->app->make(CustomerReservedAccountService::class), $monnify->customerReservedAccount());
        $this->assertSame($this->app->make(InvoiceService::class), $monnify->invoice());
        $this->assertSame($this->app->make(RecurringPaymentService::class), $monnify->recurringPayment());
        $this->assertSame($this->app->make(DirectDebitService::class), $monnify->directDebitMandate());
        $this->assertSame($this->app->make(SubAccountService::class), $monnify->subAccount());
        $this->assertSame($this->app->make(DisbursementService::class), $monnify->transfer());
        $this->assertSame($this->app->make(WalletService::class), $monnify->wallet());
        $this->assertSame($this->app->make(LimitProfileService::class), $monnify->limitProfile());
        $this->assertSame($this->app->make(RefundService::class), $monnify->refund());
        $this->assertSame($this->app->make(SettlementService::class), $monnify->settlements());
        $this->assertSame($this->app->make(VerificationService::class), $monnify->verificationAPI());
        $this->assertSame($this->app->make(PayCodeService::class), $monnify->payCodeAPI());
        $this->assertSame($this->app->make(OtherService::class), $monnify->helper());
    }
}
