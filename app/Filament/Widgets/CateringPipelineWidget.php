<?php

namespace App\Filament\Widgets;

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Filament\Widgets\Widget;

class CateringPipelineWidget extends Widget
{
    protected static ?int $sort = 18;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.catering-pipeline-widget';

    public function getOpenInquiriesCount(): int
    {
        return CateringInquiry::query()->whereIn('status', [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Quoted])->count();
    }

    public function getPendingQuotesCount(): int
    {
        return CateringInquiry::query()->where('status', CateringInquiryStatus::Quoted)->count();
    }

    public function getTotalPipelineValue(): float
    {
        return (float) CateringInquiry::query()->whereNotIn('status', [
            CateringInquiryStatus::Cancelled,
            CateringInquiryStatus::Completed,
        ])->sum('quoted_amount');
    }

    public function getLatestInquiry(): ?CateringInquiry
    {
        return CateringInquiry::query()->latest()->first();
    }
}
