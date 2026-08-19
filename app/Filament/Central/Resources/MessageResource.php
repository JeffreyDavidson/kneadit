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
    protected static ?string $model = PlatformMessage::class;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'Message';

    protected static ?string $pluralModelLabel = 'Messages';

    public static function getNavigationBadge(): ?string
    {
        $count = cache()->remember(
            'navigation-badge:central-messages:unread-tenant',
            60,
            fn (): int => PlatformMessage::topLevel()->fromTenant()->unread()->count(),
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
            'view' => ViewMessage::route('/{record}'),
        ];
    }
}
