<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\MessageResource\Pages\ListMessages;
use App\Filament\Central\Resources\MessageResource\Pages\ViewMessage;
use App\Filament\Central\Resources\MessageResource\Tables\MessagesTable;
use App\Models\Platform\PlatformMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MessageResource extends Resource
{
    #[\Override]
    protected static ?string $model = PlatformMessage::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'subject';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    protected static ?string $navigationLabel = 'Messages';

    #[\Override]
    protected static ?string $modelLabel = 'Message';

    #[\Override]
    protected static ?string $pluralModelLabel = 'Messages';

    public static function getNavigationBadge(): ?string
    {
        $count = rescue(
            fn (): int => cache()->remember(
                'filament.central.messages.unread_count',
                now()->addMinute(),
                fn (): int => PlatformMessage::topLevel()->fromTenant()->unread()->count(),
            ),
            fn (): int => PlatformMessage::topLevel()->fromTenant()->unread()->count(),
            report: false,
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'view' => ViewMessage::route('/{record}'),
        ];
    }
}
