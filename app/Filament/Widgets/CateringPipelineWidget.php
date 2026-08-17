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

    /**
     * Hide entirely when there's no active catering pipeline (no open
     * inquiries AND no quoted ones). The 0/0/$0 empty state was just
     * dead space for bakeries that don't do catering yet. Reappears
     * the moment any inquiry lands or moves to quoted.
     */
    public static function canView(): bool
    {
        return CateringInquiry::query()->openFunnel()->exists()
            || CateringInquiry::query()->quoted()->exists();
    }

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
        $id = $this->cached('latest_id', [900, 1800], function (): ?int {
            $id = CateringInquiry::query()->latest()->value('id');

            return is_numeric($id) ? (int) $id : null;
        });

        return $id ? CateringInquiry::query()->whereKey($id)->first() : null;
    }

    protected function cachePrefix(): string
    {
        return 'catering_pipeline';
    }
}
