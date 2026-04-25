<?php

namespace App\Filament\Central\Widgets;

use App\Models\Platform\AdminAuditLog;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class RecentAuditLog extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.recent-audit';

    /** @return Collection<int, AdminAuditLog> */
    public function getRecentLogsProperty(): Collection
    {
        return AdminAuditLog::query()->latest()
            ->limit(5)
            ->get();
    }
}
