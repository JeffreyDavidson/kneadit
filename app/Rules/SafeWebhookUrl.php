<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Uri;
use Symfony\Component\HttpFoundation\IpUtils;
use Throwable;

class SafeWebhookUrl implements ValidationRule
{
    /**
     * Ranges PHP's FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE let through.
     *
     * @var list<string>
     */
    private const array BLOCKED_RANGES = [
        '0.0.0.0/8',
        '100.64.0.0/10',
        '192.0.0.0/24',
        '192.0.2.0/24',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '224.0.0.0/4',
        '240.0.0.0/4',
        '64:ff9b::/96',
        '64:ff9b:1::/48',
        '2002::/16',
        '2001:db8::/32',
        'fc00::/7',
        'fe80::/10',
        'ff00::/8',
    ];

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
        if (filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) === false) {
            return false;
        }

        if (IpUtils::checkIp($address, self::BLOCKED_RANGES)) {
            return false;
        }

        $embedded = $this->embeddedIpv4Address($address);

        return $embedded === null || $this->isPublicIpAddress($embedded);
    }

    /** The IPv4 address inside an IPv4-mapped (::ffff:a.b.c.d) or IPv4-compatible (::a.b.c.d) IPv6 address. */
    private function embeddedIpv4Address(string $address): ?string
    {
        $packed = inet_pton($address);

        if ($packed === false || strlen($packed) !== 16) {
            return null;
        }

        $prefix = substr($packed, 0, 12);

        if ($prefix !== str_repeat("\0", 12) && $prefix !== str_repeat("\0", 10)."\xff\xff") {
            return null;
        }

        $embedded = inet_ntop(substr($packed, 12));

        return $embedded === false ? null : $embedded;
    }
}
