<?php

namespace App\Filament\Widgets;

use App\Models\PlatformMessage;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class InboxWidget extends Widget
{
    protected static ?int $sort = -5;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.inbox-widget';

    public function getUnreadCount(): int
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return 0;
        }

        return PlatformMessage::where('tenant_id', $tenant->id)
            ->fromAdmin()
            ->topLevel()
            ->unread()
            ->count();
    }

    public function getMessagesUrl(): string
    {
        return \App\Filament\Pages\Messages::getUrl();
    }
}
