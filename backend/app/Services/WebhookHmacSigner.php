<?php

namespace App\Services;

use Spatie\WebhookServer\Signer\Signer;

class WebhookHmacSigner implements Signer
{
    public function signatureHeaderName(): string
    {
        return config('webhook-server.signature_header_name');
    }

    public function calculateSignature(string $webhookUrl, array $payload, string $secret): string
    {
        return hash_hmac(
            algo: 'sha256',
            data: json_encode($payload) ?: '',
            key: $secret,
        );
    }
}
