<?php

namespace Tests\Support;

trait InteractsWithWebsiteWebhookConfig
{
    protected const string WEBSITE_WEBHOOK_URL = 'https://website.example.test/webhooks/pizza-planet';

    protected function configureWebsiteWebhook(): void
    {
        config(['services.website.webhook_url' => self::WEBSITE_WEBHOOK_URL]);
    }

    protected function websiteWebhookUrl(): string
    {
        return self::WEBSITE_WEBHOOK_URL;
    }
}
