<?php

namespace App\Filament\Central\Widgets;

use App\Models\AdminAuditLog;
use Filament\Widgets\Widget;
use BackedEnum;

class RecentAuditLog extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.recent-audit';

    public function getRecentLogsProperty()
    {
        return AdminAuditLog::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }
}
