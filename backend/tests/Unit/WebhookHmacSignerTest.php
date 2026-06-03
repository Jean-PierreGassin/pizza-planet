<?php

namespace Tests\Unit;

use App\Services\WebhookHmacSigner;
use Tests\TestCase;

class WebhookHmacSignerTest extends TestCase
{
    public function testWebhookHmacSignerUsesConfiguredHeaderAndSha256Hmac(): void
    {
        config(['webhook-server.signature_header_name' => 'X-Test-Signature']);

        $signer = new WebhookHmacSigner();
        $payload = [
            'event_type' => 'order.status_changed',
            'order_id' => 123,
        ];

        $this->assertSame('X-Test-Signature', $signer->signatureHeaderName());
        $this->assertSame(
            hash_hmac('sha256', json_encode($payload), 'test-secret'),
            $signer->calculateSignature(
                webhookUrl: 'https://website.example.test/webhooks/pizza-planet',
                payload: $payload,
                secret: 'test-secret',
            ),
        );
    }
}
