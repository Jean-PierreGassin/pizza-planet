<?php

namespace App\Services;

use App\Exceptions\WebsiteWebhookUrlNotConfigured;

class WebsiteWebhookConfigurationService
{
    public function url(): string
    {
        $url = config('services.website.webhook_url');

        if (!is_string($url) || $url === '') {
            throw WebsiteWebhookUrlNotConfigured::create();
        }

        return $url;
    }
}
