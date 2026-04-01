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
    protected static ?string $model = PlatformAnnouncement::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Announcements';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return AnnouncementForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return AnnouncementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
        ];
    }
}
