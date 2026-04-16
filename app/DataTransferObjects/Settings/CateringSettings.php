<?php

namespace App\DataTransferObjects\Settings;

final readonly class CateringSettings
{
    /** @param array<int, string> $eventTypes */
    public function __construct(
        public bool $enabled,
        public string $minimumGuests,
        public string $leadTimeDays,
        public array $eventTypes,
    ) {}
}
