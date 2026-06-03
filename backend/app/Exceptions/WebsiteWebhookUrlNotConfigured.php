<?php

namespace App\Exceptions;

use RuntimeException;

class WebsiteWebhookUrlNotConfigured extends RuntimeException
{
    public static function create(): self
    {
        return new self('Website webhook URL is not configured.');
    }
}
