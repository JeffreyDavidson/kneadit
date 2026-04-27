<?php

namespace App\Enums\Filament;

/**
 * T-shirt sizing for dashboard widgets. Replaces the previous numeric
 * span (1/2/3). Each size maps to a number of grid columns at xl
 * breakpoint (3-column grid).
 */
enum WidgetSize: string
{
    case Small = 'sm';
    case Medium = 'md';
    case Large = 'lg';

    public function columns(): int
    {
        return match ($this) {
            self::Small => 1,
            self::Medium => 2,
            self::Large => 3,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Small => 'Small',
            self::Medium => 'Medium',
            self::Large => 'Large',
        };
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
