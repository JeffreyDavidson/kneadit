<?php

namespace App\Filament\Central\Pages;

use App\Filament\Shared\Dashboard\WidgetMeta;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class WidgetCatalog extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Widget Catalog';

    protected static ?string $navigationLabel = 'Widget Catalog';

    protected static ?string $slug = 'widget-catalog';

    protected string $view = 'filament.central.pages.widget-catalog';

    /**
     * Catalog widget list. Each entry carries the key + meta so the
     * blade can include the shared widget-card partial. No Livewire
     * mounting here — would leak Livewire's "current component"
     * context and crash the parent page render.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCatalogWidgetsProperty(): array
    {
        $catalog = [];

        foreach (WidgetMeta::all() as $key => $meta) {
            $catalog[] = [
                'key' => $key,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'size' => ($meta['defaultSize'] ?? \App\Enums\Filament\WidgetSize::Small)->value,
                'visible' => true,
            ];
        }

        return $catalog;
    }
}
