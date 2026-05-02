<?php

namespace App\Actions\Operations;

use App\Services\Settings\SettingsManager;
use Illuminate\Support\Str;

class RegenerateWebhookSecret
{
    public function __construct(
        private SettingsManager $settings,
    ) {}

    /**
     * Overwrite the current webhook secret with a fresh 40-char random value.
     * The new secret is returned so the caller can immediately surface it
     * (the form needs to refresh state to show the new value).
     */
    public function __invoke(): string
    {
        $secret = Str::random(40);

        $this->settings->setMany(['webhook_secret' => $secret]);

        return $secret;
    }
}
