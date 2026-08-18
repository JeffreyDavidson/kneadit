<?php

namespace App\DataTransferObjects\Settings;

use Illuminate\Support\Collection;

final readonly class HomepageSettings
{
    /**
     * @param array<string, string> $socialMediaLinks
     * @param array<string, mixed> $operatingHours
     * @param array<int, array<string, mixed>> $faqItems
     * @param array<string, array<string, mixed>> $sections
     */
    public function __construct(
        public array $socialMediaLinks,
        public array $operatingHours,
        public array $faqItems,
        public array $sections,
    ) {}

    public static function resolve(): self
    {
        return new self(
            socialMediaLinks: self::resolveSocialMediaLinks(),
            operatingHours: SettingValue::decodedMap(settings('operating_hours')),
            faqItems: self::resolveFaqItems(),
            sections: self::resolveSections(),
        );
    }

    /** @return Collection<string, array<string, mixed>> */
    public function visibleSections(): Collection
    {
        return collect($this->sections)
            ->filter(fn (array $section): bool => ($section['visible'] ?? true) !== false)
            ->sortBy('order');
    }

    /** @return array<string, string> */
    private static function resolveSocialMediaLinks(): array
    {
        return array_filter(
            SettingValue::decodedMap(settings('social_media_links')),
            fn (mixed $url): bool => is_string($url),
        );
    }

    /** @return array<int, array<string, mixed>> */
    private static function resolveFaqItems(): array
    {
        $items = [];

        foreach (SettingValue::decodedList(settings('faq_items')) as $item) {
            if (is_array($item)) {
                $items[] = SettingValue::map($item);
            }
        }

        return $items;
    }

    /** @return array<string, array<string, mixed>> */
    private static function resolveSections(): array
    {
        $sections = [];

        foreach (SettingValue::decodedMap(settings('homepage_sections')) as $key => $section) {
            if (is_array($section)) {
                $sections[$key] = SettingValue::map($section);
            }
        }

        return $sections;
    }
}
