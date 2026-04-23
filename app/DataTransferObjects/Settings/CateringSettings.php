<?php

namespace App\DataTransferObjects\Settings;

use App\Enums\Customers\CateringEventType;

final readonly class CateringSettings
{
    /** @param array<int, string> $eventTypes */
    public function __construct(
        public bool $enabled,
        public string $minimumGuests,
        public string $leadTimeDays,
        public array $eventTypes,
        public int $depositPercent = 25,
    ) {}

    public static function resolve(): self
    {
        return new self(
            enabled: settings('catering_enabled', '0') === '1',
            minimumGuests: (string) settings('catering_minimum_guests', '10'),
            leadTimeDays: (string) settings('catering_lead_time_days', '14'),
            eventTypes: self::resolveEventTypes(),
            depositPercent: (int) settings('catering_deposit_percent', '25'),
        );
    }

    /** @return array<int, string> */
    private static function resolveEventTypes(): array
    {
        $stored = settings('catering_event_types');

        if (! $stored) {
            return CateringEventType::defaultLabels();
        }

        $decoded = json_decode((string) $stored, true);
        if (! is_array($decoded) || $decoded === []) {
            return CateringEventType::defaultLabels();
        }

        $values = array_values(array_filter(
            $decoded,
            fn (mixed $v) => is_string($v) && trim($v) !== '',
        ));

        return $values === [] ? CateringEventType::defaultLabels() : $values;
    }
}
