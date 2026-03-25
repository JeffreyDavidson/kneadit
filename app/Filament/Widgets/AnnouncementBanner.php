<?php

namespace App\Filament\Widgets;

use App\Models\PlatformAnnouncement;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AnnouncementBanner extends Widget
{
    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.announcement-banner';

    /** @return array<string, mixed> */
    public function getAnnouncements(): array
    {
        /** @var Tenant|null $tenant */
        $tenant = Filament::getTenant();
        $plan = $tenant?->plan;

        return PlatformAnnouncement::active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function (PlatformAnnouncement $announcement) use ($plan) {
                $targets = $announcement->target_plans;

                return empty($targets) || in_array($plan, $targets);
            })
            ->values()
            ->toArray();
    }
}
