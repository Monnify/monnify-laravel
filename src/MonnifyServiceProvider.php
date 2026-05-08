<?php

namespace Monnify\MonnifyLaravel;

use Illuminate\Support\ServiceProvider;
/**
 * Class MonnifyServiceProvider
 */
class MonnifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/monnify.php', 'monnify'
        );

        $this->app->singleton('monnify', function ($app) {
            return new Monnify(
                config('monnify.api_key'),
                config('monnify.secret_key'),
                config('monnify.environment')
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/monnify.php' => config_path('monnify.php'),
        ], 'config');
    }
}