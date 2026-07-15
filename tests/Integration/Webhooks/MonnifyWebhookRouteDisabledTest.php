<?php

namespace Monnify\MonnifyLaravel\Tests\Integration\Webhooks;

use Illuminate\Support\Facades\Route;
use Monnify\MonnifyLaravel\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MonnifyWebhookRouteDisabledTest extends TestCase
{
    #[Test]
    public function does_not_register_the_webhook_route_by_default(): void
    {
        $this->assertFalse(Route::has('monnify.webhook'));
    }
}
