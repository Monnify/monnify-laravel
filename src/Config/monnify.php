<?php

/*
 * This file is part of the Laravel Monnify package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Monnify Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Monnify settings. Monnify is a payment gateway srevice
    | provider.
    |
    |
    */

    /**
     * Api key From Monnify
     */
    'api_key' => env('MONNIFY_API_KEY'),

    /**
     * Secret key From Monnify
     */
    'secret_key' => env('MONNIFY_SECRET_KEY'),

    /**
     * Api contract code From Monnify
     */
    // 'contract_code' => env('MONNIFY_CONTRACT'),

    /**
     * Monnify environment: SANDBOX or LIVE
     * default: 'SANDBOX'
     */
    'environment' => env('MONNIFY_ENVIRONMENT', 'SANDBOX'),
];