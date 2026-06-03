<?php

namespace Tests\Support;

trait InteractsWithWebsiteWebhookConfig
{
    protected const string WEBSITE_WEBHOOK_URL = 'https://website.example.test/webhooks/pizza-planet';
    protected const string WEBSITE_WEBHOOK_SECRET = 'test-signing-secret';

    protected function configureWebsiteWebhook(): void
    {
        config([
            'services.website.webhook_url' => self::WEBSITE_WEBHOOK_URL,
            'services.website.webhook_secret' => self::WEBSITE_WEBHOOK_SECRET,
        ]);
    }

    protected function websiteWebhookUrl(): string
    {
        return self::WEBSITE_WEBHOOK_URL;
    }

    protected function websiteWebhookSecret(): string
    {
        return self::WEBSITE_WEBHOOK_SECRET;
    }
}
