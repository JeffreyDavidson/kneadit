<?php

namespace App\Filament\Central\Resources\PlatformSettings;

use App\Filament\Central\Resources\PlatformSettings\Pages\ListPlatformSettings;
use App\Filament\Central\Resources\PlatformSettings\Schemas\PlatformSettingForm;
use App\Filament\Central\Resources\PlatformSettings\Tables\PlatformSettingsTable;
use App\Models\Platform\PlatformSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PlatformSettingResource extends Resource
{
    protected static ?string $model = PlatformSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Platform Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PlatformSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlatformSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatformSettings::route('/'),
        ];
    }
}
