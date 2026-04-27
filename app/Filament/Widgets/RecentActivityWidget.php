<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Operations\ActivityLog;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    use HasDashboardSize;

    protected static ?int $sort = 13;

    protected string $view = 'filament.widgets.recent-activity';

    /** @return array<int, array<string, mixed>> */
    public function getRows(): array
    {
        return ActivityLog::query()
            ->latest()
            ->limit($this->rowLimit())
            ->get()
            ->map(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'when' => $log->created_at?->diffForHumans() ?? '—',
                'user_name' => $log->user_name,
                'action' => $log->action,
                'description' => $log->description,
            ])
            ->all();
    }

    public function getViewAllUrl(): string
    {
        return route('filament.admin.resources.activity-logs.index');
    }

    private function rowLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            default => 10,
        };
    }
}
