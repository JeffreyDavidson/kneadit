<?php

declare(strict_types=1);

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
    #[\Override]
    protected static ?string $model = PlatformSetting::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Platform Settings';

    #[\Override]
    protected static ?string $modelLabel = 'Setting';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return PlatformSettingForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return PlatformSettingsTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPlatformSettings::route('/'),
        ];
    }
}
