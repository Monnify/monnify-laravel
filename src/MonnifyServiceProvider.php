<?php

namespace Monnify\MonnifyLaravel;

use Error;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
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
 * Class MonnifyServiceProvider
 */
class MonnifyServiceProvider extends ServiceProvider
{
    public const BINDING = 'monnify';
    public const HTTP_CLIENT_BINDING = 'monnify.http_client';
    private const SERVICE_CLASSES = [
        TransactionService::class,
        BillsPaymentService::class,
        CustomerReservedAccountService::class,
        InvoiceService::class,
        RecurringPaymentService::class,
        DirectDebitService::class,
        SubAccountService::class,
        DisbursementService::class,
        WalletService::class,
        LimitProfileService::class,
        RefundService::class,
        SettlementService::class,
        VerificationService::class,
        PayCodeService::class,
        OtherService::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/monnify.php',
            'monnify'
        );

        $this->app->singleton(self::HTTP_CLIENT_BINDING, function () {
            return new Client([
                'base_uri' => $this->resolveBaseUri(),
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        });

        $this->registerServiceContextualBindings();
        $this->registerServiceSingletons();

        $this->app->singleton(self::BINDING, function ($app) {
            return $app->make(Monnify::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/monnify.php' => config_path('monnify.php'),
        ], 'config');
    }

    protected function registerServiceContextualBindings(): void
    {
        $this->app->when(self::SERVICE_CLASSES)
            ->needs(Client::class)
            ->give(fn ($app) => $app->make(self::HTTP_CLIENT_BINDING));
    }

    protected function registerServiceSingletons(): void
    {
        foreach (self::SERVICE_CLASSES as $serviceClass) {
            $this->app->singleton($serviceClass);
        }
    }

    protected function resolveBaseUri(): string
    {
        $environment = config('monnify.environment');

        return match ($environment) {
            'SANDBOX' => config('monnify.sandbox_url'),
            'LIVE' => config('monnify.live_url'),
            default => throw new Error(
                "Unknown environment passed: $environment, Please specify between SANDBOX or LIVE"
            ),
        };
    }
}
