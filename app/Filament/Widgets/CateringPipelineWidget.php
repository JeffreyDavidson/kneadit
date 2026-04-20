<?php

namespace App\Filament\Widgets;

use App\Models\Customers\CateringInquiry;
use Filament\Widgets\Widget;

class CateringPipelineWidget extends Widget
{
    protected static ?int $sort = 18;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.catering-pipeline-widget';

    public function getOpenInquiriesCount(): int
    {
        return CateringInquiry::query()->openFunnel()->count();
    }

    public function getPendingQuotesCount(): int
    {
        return CateringInquiry::query()->quoted()->count();
    }

    public function getTotalPipelineValue(): float
    {
        return (float) CateringInquiry::query()->inPipeline()->sum('quoted_amount');
    }

    public function getLatestInquiry(): ?CateringInquiry
    {
        return CateringInquiry::query()->latest()->first();
    }
}
