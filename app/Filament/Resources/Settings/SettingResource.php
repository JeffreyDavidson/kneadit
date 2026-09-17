<?php

declare(strict_types=1);

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Filament\Resources\Settings\Tables\SettingsTable;
use App\Models\Platform\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    #[\Override]
    protected static ?string $model = Setting::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'key';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    #[\Override]
    protected static bool $shouldRegisterNavigation = false;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return SettingsTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
        ];
    }
}
