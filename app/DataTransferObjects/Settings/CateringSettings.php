<?php

namespace App\DataTransferObjects\Settings;

final readonly class CateringSettings
{
    public function __construct(
        public bool $enabled,
        public string $minimumGuests,
        public string $leadTimeDays,
    ) {}
}
