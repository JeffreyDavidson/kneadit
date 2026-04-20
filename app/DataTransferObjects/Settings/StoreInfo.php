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

    public static function resolve(): self
    {
        return new self(
            name: (string) settings('store_name', 'Our Bakery'),
            email: settings('store_email'),
            phone: settings('store_phone'),
            address: settings('store_address'),
            website: settings('store_website'),
            photo: settings('store_photo'),
            logo: settings('store_logo'),
            tagline: settings('store_tagline'),
        );
    }

    public function logoUrl(): ?string
    {
        return $this->logo
            ? asset("storage/{$this->logo}")
            : null;
    }

    public function defaultTagline(): string
    {
        return $this->tagline ?? "{$this->name} — Fresh baked goods made with love";
    }
}
