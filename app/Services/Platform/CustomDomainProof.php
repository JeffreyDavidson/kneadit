<?php

declare(strict_types=1);

namespace App\Services\Platform;

use Illuminate\Support\Facades\Config;

/**
 * The ownership proof a custom domain serves to show that this application answers
 * for it: a keyed hash of the host, so a proof for one host never verifies another
 * and nothing about the key or any bakery is revealed.
 */
final readonly class CustomDomainProof
{
    public const string PATH = '.well-known/kneadit-domain-proof';

    public function for(string $host): string
    {
        return hash_hmac('sha256', strtolower($host), Config::string('app.key'));
    }

    public function matches(string $host, string $candidate): bool
    {
        return hash_equals($this->for($host), $candidate);
    }
}
