<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeZone;

/**
 * The time zones a bakery can pick, shared by the setup wizard and Manage
 * Settings. Every IANA identifier is offered once, labelled with its city and
 * its UTC offset right now, for example "New York (Eastern Time, UTC-4)".
 */
final class TimezoneOptions
{
    /** Names people know the common North American zones by. */
    private const array FRIENDLY_NAMES = [
        'America/New_York' => 'Eastern Time',
        'America/Toronto' => 'Eastern Time',
        'America/Chicago' => 'Central Time',
        'America/Denver' => 'Mountain Time',
        'America/Phoenix' => 'Mountain Time',
        'America/Los_Angeles' => 'Pacific Time',
        'America/Vancouver' => 'Pacific Time',
        'America/Anchorage' => 'Alaska Time',
        'Pacific/Honolulu' => 'Hawaii Time',
    ];

    /**
     * @return array<string, array<string, string>> identifier => label, grouped by region (America first)
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (DateTimeZone::listIdentifiers() as $identifier) {
            $region = str_contains($identifier, '/') ? explode('/', $identifier)[0] : 'Other';
            $groups[$region][$identifier] = self::label($identifier);
        }

        ksort($groups);

        return ['America' => $groups['America'] ?? []] + $groups;
    }

    /**
     * @return array<string, string> identifier => label, without grouping
     */
    public static function options(): array
    {
        return array_merge(...array_values(self::grouped()));
    }

    public static function isValid(string $identifier): bool
    {
        return in_array($identifier, DateTimeZone::listIdentifiers(), true);
    }

    private static function label(string $identifier): string
    {
        $city = str_replace('_', ' ', basename($identifier));
        $friendly = self::FRIENDLY_NAMES[$identifier] ?? null;
        $offset = self::offset($identifier);

        return $friendly === null
            ? "{$city} ({$offset})"
            : "{$city} ({$friendly}, {$offset})";
    }

    private static function offset(string $identifier): string
    {
        $seconds = new DateTimeZone($identifier)->getOffset(now()->toDateTime());
        $sign = $seconds < 0 ? '-' : '+';
        $minutes = intdiv(abs($seconds), 60);
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return $remainder === 0
            ? "UTC{$sign}{$hours}"
            : sprintf('UTC%s%d:%02d', $sign, $hours, $remainder);
    }
}
