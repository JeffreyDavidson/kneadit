<?php

namespace App\DataTransferObjects\Settings;

final readonly class OrderSettings
{
    /**
     * @param array<int, array<string, mixed>> $deliveryFeeTiers
     */
    public function __construct(
        public int $leadTimeHours,
        public bool $deliveryEnabled,
        public string $freeDeliveryMinimum,
        public array $deliveryFeeTiers,
    ) {}

    public function leadTimeDays(): int
    {
        return (int) ceil($this->leadTimeHours / 24);
    }
}
