<?php

namespace App\DataTransferObjects\Delivery;

use App\Enums\Orders\DeliveryDistanceTier;

final readonly class DeliveryEstimate
{
    public function __construct(public DeliveryDistanceTier $tier) {}

    public function label(): string
    {
        return $this->tier->getLabel();
    }

    public function color(): string
    {
        return $this->tier->getColor();
    }

    public function estimatedMinutes(): int
    {
        return $this->tier->estimatedMinutes();
    }

    /** @return array{tier: string, color: string, estimated_minutes: int} */
    public function toArray(): array
    {
        return [
            'tier' => $this->label(),
            'color' => $this->color(),
            'estimated_minutes' => $this->estimatedMinutes(),
        ];
    }
}
