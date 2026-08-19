<?php

namespace App\Filament\Shared\Dashboard;

use App\Enums\Filament\WidgetSize;
use BackedEnum;
use Filament\Widgets\Widget;

final readonly class WidgetDefinition
{
    /**
     * @param class-string<Widget> $class
     * @param list<WidgetSize> $allowedSizes
     */
    public function __construct(
        public string $class,
        public string $name,
        public string $description,
        public BackedEnum|string $icon,
        public WidgetSize $defaultSize,
        public array $allowedSizes = [],
        public bool $defaultHidden = false,
    ) {}

    /** @return list<WidgetSize> */
    public function allowedSizes(): array
    {
        return $this->allowedSizes !== []
            ? $this->allowedSizes
            : WidgetSize::standardSizes();
    }
}
