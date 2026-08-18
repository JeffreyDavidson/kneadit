<?php

namespace App\DataTransferObjects\Settings;

use Illuminate\Support\Facades\Storage;

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
            name: SettingValue::string(settings('store_name'), 'Our Bakery'),
            email: SettingValue::nullableString(settings('store_email')),
            phone: SettingValue::nullableString(settings('store_phone')),
            address: SettingValue::nullableString(settings('store_address')),
            website: SettingValue::nullableString(settings('store_website')),
            photo: SettingValue::nullableString(settings('store_photo')),
            logo: SettingValue::nullableString(settings('store_logo')),
            tagline: SettingValue::nullableString(settings('store_tagline')),
        );
    }

    /**
     * Resolved URL to the store logo, or null if no logo is set OR the
     * stored path no longer points to a real file. The file-exists check
     * prevents broken `<img>` fallbacks (alt text rendering) when a tenant
     * has a logo column populated but the underlying file has been deleted
     * or never uploaded.
     */
    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        return asset("storage/{$this->logo}");
    }

    public function defaultTagline(): string
    {
        return $this->tagline ?? "{$this->name} — Fresh baked goods made with love";
    }
}
