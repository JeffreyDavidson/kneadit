<?php

namespace App\DataTransferObjects\Customers;

final readonly class CateringEventDetails
{
    public function __construct(
        public string $eventType,
        public string $eventDate,
        public int $guestCount,
        public ?float $budget,
        public string $details,
        public ?string $dietaryRequirements,
        public ?string $venueAddress,
    ) {}
}
