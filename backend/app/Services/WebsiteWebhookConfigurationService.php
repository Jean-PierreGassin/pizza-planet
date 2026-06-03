<?php

namespace App\Services;

use App\Exceptions\WebsiteWebhookUrlNotConfigured;
use App\Exceptions\WebsiteWebhookSecretNotConfigured;
use Spatie\WebhookServer\CallWebhookJob;
use Spatie\WebhookServer\Exceptions\InvalidBackoffStrategy;
use Spatie\WebhookServer\Exceptions\InvalidSigner;
use Spatie\WebhookServer\Exceptions\InvalidWebhookJob;
use Spatie\WebhookServer\WebhookCall;

class WebsiteWebhookConfigurationService
{
    public function assertConfigured(): void
    {
        $this->url();
        $this->secret();
    }

    public function url(): string
    {
        $url = config('services.website.webhook_url');

        if (!is_string($url) || $url === '') {
            throw WebsiteWebhookUrlNotConfigured::create();
        }

        return $url;
    }

    public function secret(): string
    {
        $secret = config('services.website.webhook_secret');

        if (!is_string($secret) || $secret === '') {
            throw WebsiteWebhookSecretNotConfigured::create();
        }

        return $secret;
    }

    /**
     * @param class-string<CallWebhookJob> $jobClass
     * @return WebhookCall
     * @throws InvalidBackoffStrategy
     * @throws InvalidSigner
     * @throws InvalidWebhookJob
     */
    public function webhookCall(string $jobClass): WebhookCall
    {
        return WebhookCall::create()
            ->useJob(webhookJobClass: $jobClass)
            ->onQueue(queue: config('webhook-server.queue'))
            ->onConnection(connection: config('webhook-server.connection'))
            ->useHttpVerb(verb: config('webhook-server.http_verb'))
            ->maximumTries(tries: config('webhook-server.tries'))
            ->useBackoffStrategy(backoffStrategyClass: config('webhook-server.backoff_strategy'))
            ->timeoutInSeconds(timeoutInSeconds: config('webhook-server.timeout_in_seconds'))
            ->signUsing(signerClass: config('webhook-server.signer'))
            ->withHeaders(headers: config('webhook-server.headers'))
            ->verifySsl(verifySsl: config('webhook-server.verify_ssl'))
            ->throwExceptionOnFailure(
                throwExceptionOnFailure: config('webhook-server.throw_exception_on_failure'),
            )
            ->useProxy(proxy: config('webhook-server.proxy'))
            ->useSecret(secret: $this->secret())
            ->useTimestamp();
    }
}
