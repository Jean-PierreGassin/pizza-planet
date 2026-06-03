<?php

namespace App\Exceptions;

use RuntimeException;

class WebsiteWebhookSecretNotConfigured extends RuntimeException
{
    public static function create(): self
    {
        return new self(message: 'Website webhook signing secret is not configured.');
    }
}
