<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Platform\Messages;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Platform\PlatformMessage;
use Filament\Widgets\Widget;

class InboxWidget extends Widget
{
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = -5;

    #[\Override]
    protected string $view = 'filament.widgets.inbox-widget';

    /**
     * Hide entirely when there are no unread admin messages — was
     * previously rendering an empty <div></div> that still took a
     * dashboard grid cell, leaving a visible gap between siblings.
     */
    #[\Override]
    public static function canView(): bool
    {
        $tenantId = tenant('id');

        return is_string($tenantId)
            && Messages::canAccess()
            && PlatformMessage::query()
                ->forTenant($tenantId)
                ->fromAdmin()
                ->topLevel()
                ->unread()
                ->exists();
    }

    public function getUnreadCount(): int
    {
        $tenantId = tenant('id');

        if (! is_string($tenantId)) {
            return 0;
        }

        return PlatformMessage::query()
            ->forTenant($tenantId)
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
