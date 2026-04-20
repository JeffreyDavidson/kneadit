<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Customers\CateringInquiry;
use Filament\Widgets\Widget;

class CateringPipelineWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 18;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.catering-pipeline-widget';

    public function getOpenInquiriesCount(): int
    {
        return $this->cached('open', [1800, 3600], fn (): int => CateringInquiry::query()->openFunnel()->count());
    }

    public function getPendingQuotesCount(): int
    {
        return $this->cached('pending', [1800, 3600], fn (): int => CateringInquiry::query()->quoted()->count());
    }

    public function getTotalPipelineValue(): float
    {
        return $this->cached('value', [1800, 3600], fn (): float => (float) CateringInquiry::query()->inPipeline()->sum('quoted_amount'));
    }

    public function getLatestInquiry(): ?CateringInquiry
    {
        return $this->cached('latest', [900, 1800], fn () => CateringInquiry::query()->latest()->first());
    }

    protected function cachePrefix(): string
    {
        return 'catering_pipeline';
    }
}
