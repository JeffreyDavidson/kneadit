<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Platform\PlatformAnnouncement;
use App\Models\Platform\Tenant;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AnnouncementBanner extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.announcement-banner';

    /** @return list<mixed> */
    public function getAnnouncements(): array
    {
        /** @var Tenant|null $tenant */
        $tenant = Filament::getTenant();
        $plan = $tenant?->plan;

        $planKey = $plan ? $plan->value : 'none';

        $announcements = $this->cached('announcements_' . $planKey, [1800, 3600], fn (): array => PlatformAnnouncement::active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function (PlatformAnnouncement $announcement) use ($plan) {
                $targets = $announcement->target_plans;

                return empty($targets) || in_array($plan, $targets);
            })
            ->values()
            ->values()
            ->all());

        return array_values($announcements);
    }

    protected function cachePrefix(): string
    {
        return 'announcement_banner';
    }
}
