<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\CateringInquiry;
use Filament\Widgets\Widget;

class CateringPipelineWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 18;

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
        // catering_inquiries.quoted_amount is bigint cents (this PR's migration);
        // divide back to dollars for display.
        return $this->cached('value', [1800, 3600], fn (): float => (float) ((int) CateringInquiry::query()->inPipeline()->sum('quoted_amount') / 100));
    }

    public function getLatestInquiry(): ?CateringInquiry
    {
        // Cache the id, not the model. Cache stores hydrate as __PHP_Incomplete_Class
        // because config(cache.serializable_classes) is false. Same shape as #302.
        $id = $this->cached('latest_id', [900, 1800], fn (): ?int => CateringInquiry::query()->latest()->value('id'));

        return $id ? CateringInquiry::query()->whereKey($id)->first() : null;
    }

    protected function cachePrefix(): string
    {
        return 'catering_pipeline';
    }
}
