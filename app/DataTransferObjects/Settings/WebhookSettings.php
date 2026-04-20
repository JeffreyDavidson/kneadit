<?php

namespace App\DataTransferObjects\Settings;

final readonly class WebhookSettings
{
    public function __construct(
        public ?string $url = null,
        public string $secret = '',
    ) {}

    public static function resolve(): self
    {
        return new self(
            url: settings('webhook_url') ?: null,
            secret: (string) settings('webhook_secret', ''),
        );
    }

    public function isConfigured(): bool
    {
        return $this->url !== null && $this->url !== '';
    }
}
