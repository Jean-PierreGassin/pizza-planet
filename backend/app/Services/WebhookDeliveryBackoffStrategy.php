<?php

namespace App\Services;

use Spatie\WebhookServer\BackoffStrategy\BackoffStrategy;

class WebhookDeliveryBackoffStrategy implements BackoffStrategy
{
    /**
     * Give burst failures breathing room before retrying the receiver.
     */
    public function waitInSecondsAfterAttempt(int $attempt): int
    {
        return match (true) {
            $attempt <= 1 => 60,
            $attempt === 2 => 120,
            $attempt === 3 => 240,
            $attempt === 4 => 480,
            default => 960,
        };
    }
}
