<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Filament\Central\Resources\TenantResource\Pages\ViewTenant;
use App\Filament\Central\Resources\TenantResource\RelationManagers\NotesRelationManager;
use App\Filament\Central\Resources\TenantResource\Schemas\TenantForm;
use App\Filament\Central\Resources\TenantResource\Schemas\TenantInfolist;
use App\Filament\Central\Resources\TenantResource\Tables\TenantsTable;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $recordTitleAttribute = 'store_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['store_name', 'name', 'email', 'id'];
    }

    /** @param Tenant $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Owner' => $record->name,
            'Plan' => ucfirst($record->plan),
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Bakeries';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return TenantForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return TenantInfolist::configure($infolist);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'view' => ViewTenant::route('/{record}'),
        ];
    }
}
