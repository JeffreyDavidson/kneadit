<?php

namespace App\Filament\Widgets;

use App\Models\CateringInquiry;
use Filament\Widgets\Widget;

class CateringPipelineWidget extends Widget
{
    protected static ?int $sort = 18;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.catering-pipeline-widget';

    public function getOpenInquiriesCount(): int
    {
        return CateringInquiry::whereIn('status', ['new', 'contacted'])->count();
    }

    public function getPendingQuotesCount(): int
    {
        return CateringInquiry::where('status', 'quoted')->count();
    }

    public function getTotalPipelineValue(): float
    {
        return (float) CateringInquiry::whereNotIn('status', ['declined', 'cancelled', 'completed'])
            ->sum('quoted_amount');
    }

    public function getLatestInquiry(): ?CateringInquiry
    {
        return CateringInquiry::orderByDesc('created_at')->first();
    }
}
