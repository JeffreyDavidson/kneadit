<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Platform\Messages;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class InboxWidget extends Widget
{
    protected static ?int $sort = -5;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.inbox-widget';

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
