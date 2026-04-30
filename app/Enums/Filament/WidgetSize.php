<?php

namespace App\Enums\Filament;

use Filament\Support\Contracts\HasLabel;

/**
 * T-shirt sizing for dashboard widgets. Replaces the previous numeric
 * span (1/2/3). SM/MD/LG span 1/2/3 columns at one row tall;
 * XL spans 3 columns × 2 rows for hero widgets that benefit from
 * vertical space (charts with breakdown tables, the welcome banner).
 */
enum WidgetSize: string implements HasLabel
{
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';
    case ExtraLarge = 'xl';

    public function columns(): int
    {
        return match ($this) {
            self::Small => 1,
            self::Medium => 2,
            self::Large, self::ExtraLarge => 3,
        };
    }

    public function rows(): int
    {
        return $this === self::ExtraLarge ? 2 : 1;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Small => 'Small',
            self::Medium => 'Medium',
            self::Large => 'Large',
            self::ExtraLarge => 'Extra Large',
        };
    }

    /**
     * The "standard" sizes any widget can support (sm/md/lg). XL is
     * opt-in for hero widgets that benefit from doubled vertical space
     * (charts with breakdown tables, the welcome banner). When a
     * WidgetMeta entry omits allowedSizes, this is the default.
     *
     * @return list<self>
     */
    public static function standardSizes(): array
    {
        return [self::Small, self::Medium, self::Large];
    }

    /**
     * Translate a legacy integer span (1/2/3) to a WidgetSize.
     * Used for migrating saved dashboard_widgets configs.
     */
    public static function fromLegacySpan(int $span): self
    {
        return match (true) {
            $span <= 1 => self::Small,
            $span === 2 => self::Medium,
            default => self::Large,
        };
    }
}
