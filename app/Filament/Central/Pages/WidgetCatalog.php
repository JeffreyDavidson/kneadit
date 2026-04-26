<?php

namespace App\Filament\Central\Pages;

use App\Filament\Shared\Dashboard\WidgetMeta;
use App\Services\Filament\WidgetPreviewRenderer;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
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
     * Render every tenant widget against the demo tenant DB. Returns an
     * ordered list of [key, name, description, icon, html] for the view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRenderedWidgetsProperty(): array
    {
        $renderer = resolve(WidgetPreviewRenderer::class);

        $rendered = [];
        foreach (WidgetMeta::all() as $key => $meta) {
            $rendered[] = [
                'key' => $key,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'html' => $meta['class']
                    ? $renderer->render($meta['class'])
                    : new HtmlString('<em class="text-gray-500">No class registered for this widget.</em>'),
            ];
        }

        return $rendered;
    }
}
