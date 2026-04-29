<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Platform\Messages;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class InboxWidget extends Widget
{
    use HasDashboardSize;

    protected static ?int $sort = -5;

    protected string $view = 'filament.widgets.inbox-widget';

    /**
     * Hide entirely when there are no unread admin messages — was
     * previously rendering an empty <div></div> that still took a
     * dashboard grid cell, leaving a visible gap between siblings.
     */
    public static function canView(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            && PlatformMessage::query()
                ->where('tenant_id', $tenant->id)
                ->fromAdmin()
                ->topLevel()
                ->unread()
                ->exists();
    }

    public function getUnreadCount(): int
    {
        /** @var Tenant|null $tenant */
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return 0;
        }

        return PlatformMessage::query()->where('tenant_id', $tenant->id)
            ->fromAdmin()
            ->topLevel()
            ->unread()
            ->count();
    }

    public function getMessagesUrl(): string
    {
        return Messages::getUrl();
    }
}
