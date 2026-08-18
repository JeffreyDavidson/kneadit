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
     * When true, the catalog renders each widget once per allowed size
     * (sm/md/lg) so design variants can be reviewed side-by-side.
     */
    public bool $showAllSizes = false;

    public function toggleAllSizes(): void
    {
        $this->showAllSizes = ! $this->showAllSizes;
    }

    /**
     * Catalog widget list. When showAllSizes is on, each widget's entry
     * carries an `allowedSizes` array (the sizes the dashboard config
     * allows for it); the blade renders one mini-tile per size. Otherwise
     * each entry has a single `size` field with the widget's default.
     *
     * No Livewire mounting here — would leak Livewire's "current
     * component" context and crash the parent page render.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCatalogWidgetsProperty(): array
    {
        $catalog = [];

        foreach (WidgetMeta::all() as $key => $meta) {
            $entry = [
                'key' => $key,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'size' => ($meta['defaultSize'] ?? \App\Enums\Filament\WidgetSize::Small)->value,
                'visible' => true,
            ];

            if ($this->showAllSizes) {
                $entry['allowedSizes'] = array_map(
                    fn (\App\Enums\Filament\WidgetSize $s): string => $s->value,
                    WidgetMeta::allowedSizesFor($key),
                );
            }

            $catalog[] = $entry;
        }

        return $catalog;
    }
}
