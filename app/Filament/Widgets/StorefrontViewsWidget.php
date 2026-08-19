<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Analytics\StorefrontAnalytics;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Queries\Analytics\StorefrontViewsQuery;
use Filament\Widgets\Widget;

class StorefrontViewsWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.storefront-views';

    /** @return array<string, mixed> */
    public function getCardData(): array
    {
        return $this->cached('main', [60, 120], fn (): array => resolve(StorefrontViewsQuery::class)->get());
    }

    public function getViewAllUrl(): string
    {
        return StorefrontAnalytics::getUrl();
    }

    protected function cachePrefix(): string
    {
        return 'storefront_views';
    }
}
