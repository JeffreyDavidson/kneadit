<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\AnnouncementResource\Pages;
use App\Filament\Central\Resources\AnnouncementResource\Schemas\AnnouncementForm;
use App\Filament\Central\Resources\AnnouncementResource\Tables\AnnouncementsTable;
use App\Models\Platform\PlatformAnnouncement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AnnouncementResource extends Resource
{
    #[\Override]
    protected static ?string $model = PlatformAnnouncement::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[\Override]
    protected static ?string $navigationLabel = 'Announcements';

    #[\Override]
    protected static ?int $navigationSort = 6;

    #[\Override]
    public static function form(Schema $form): Schema
    {
        return AnnouncementForm::configure($form);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return AnnouncementsTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
        ];
    }
}
