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

    /** @return Collection<string, array<string, mixed>> */
    public function visibleSections(): Collection
    {
        return collect($this->sections)
            ->filter(fn (array $s) => $s['visible'] ?? true)
            ->sortBy('order');
    }
}
