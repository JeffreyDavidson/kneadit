<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Uri;
use Throwable;

class SafeWebhookUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->isSafe($value)) {
            $fail('The :attribute must be a public HTTPS URL.');
        }
    }

    public function isSafe(string $url): bool
    {
        return $this->publicAddressFor($url) !== null;
    }

    public function publicAddressFor(string $url): ?string
    {
        try {
            $uri = Uri::of($url);
        } catch (Throwable) {
            return null;
        }

        if ($uri->scheme() !== 'https' || $uri->user() !== null || $uri->password() !== null) {
            return null;
        }

        $host = $uri->host();

        if ($host === null || $host === '') {
            return null;
        }

        $ipHost = str_starts_with($host, '[') && str_ends_with($host, ']')
            ? substr($host, 1, -1)
            : $host;

        if (filter_var($ipHost, FILTER_VALIDATE_IP) !== false) {
            return $this->isPublicIpAddress($ipHost) ? $ipHost : null;
        }

        $records = dns_get_record($host, DNS_A | DNS_AAAA);

        if ($records === false || $records === []) {
            return null;
        }

        $addresses = [];

        foreach ($records as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;

            if (is_string($address)) {
                $addresses[] = $address;
            }
        }

        if ($addresses === [] || ! collect($addresses)->every($this->isPublicIpAddress(...))) {
            return null;
        }

        return $addresses[0];
    }

    private function isPublicIpAddress(string $address): bool
    {
        return filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }
}
