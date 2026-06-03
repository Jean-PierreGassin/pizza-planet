<?php

namespace Tests\Unit;

use App\Services\WebhookDeliveryBackoffStrategy;
use Tests\TestCase;

class WebhookDeliveryBackoffStrategyTest extends TestCase
{
    public function testWebhookDeliveryBackoffSpacesOutBurstyFailures(): void
    {
        $strategy = new WebhookDeliveryBackoffStrategy();

        $this->assertSame(60, $strategy->waitInSecondsAfterAttempt(1));
        $this->assertSame(120, $strategy->waitInSecondsAfterAttempt(2));
        $this->assertSame(240, $strategy->waitInSecondsAfterAttempt(3));
        $this->assertSame(480, $strategy->waitInSecondsAfterAttempt(4));
        $this->assertSame(960, $strategy->waitInSecondsAfterAttempt(5));
        $this->assertSame(960, $strategy->waitInSecondsAfterAttempt(20));
    }
}
