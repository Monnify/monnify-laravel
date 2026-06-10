<?php

use Illuminate\Support\Facades\Route;
use Monnify\MonnifyLaravel\Http\Controllers\MonnifyWebhookController;

Route::post(config('monnify.webhooks.route_path', 'monnify/webhook'), MonnifyWebhookController::class)
    ->name('monnify.webhook');
