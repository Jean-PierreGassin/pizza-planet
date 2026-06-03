<?php

namespace Tests\Unit;

use App\Exceptions\WebsiteWebhookUrlNotConfigured;
use App\Services\WebsiteWebhookConfigurationService;
use Tests\Support\InteractsWithWebsiteWebhookConfig;
use Tests\TestCase;

class WebsiteWebhookConfigurationServiceTest extends TestCase
{
    use InteractsWithWebsiteWebhookConfig;

    public function testUrlThrowsCustomExceptionWhenWebsiteWebhookUrlIsMissing(): void
    {
        config(['services.website.webhook_url' => null]);

        $this->expectException(WebsiteWebhookUrlNotConfigured::class);

        app(WebsiteWebhookConfigurationService::class)->url();
    }

    public function testUrlReturnsConfiguredWebsiteWebhookUrl(): void
    {
        $this->configureWebsiteWebhook();

        $this->assertSame(
            $this->websiteWebhookUrl(),
            app(WebsiteWebhookConfigurationService::class)->url(),
        );
    }
}
