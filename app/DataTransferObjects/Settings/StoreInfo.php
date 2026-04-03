<?php

namespace App\DataTransferObjects\Settings;

final readonly class StoreInfo
{
    public function __construct(
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $website,
        public ?string $photo,
        public ?string $logo,
        public ?string $tagline,
    ) {}

    public function logoUrl(): ?string
    {
        return $this->logo
            ? asset("storage/{$this->logo}")
            : null;
    }
}
