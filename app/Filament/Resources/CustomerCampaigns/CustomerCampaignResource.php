<?php

namespace App\Filament\Resources\CustomerCampaigns;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Resources\CustomerCampaigns\Pages\ListCustomerCampaigns;
use App\Filament\Resources\CustomerCampaigns\Schemas\CustomerCampaignForm;
use App\Filament\Resources\CustomerCampaigns\Tables\CustomerCampaignsTable;
use App\Models\Engagement\CustomerCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CustomerCampaignResource extends Resource
{
    use RequiresManagerRole;

    #[\Override]
    protected static ?string $model = CustomerCampaign::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Engagement';

    #[\Override]
    protected static ?string $navigationLabel = 'Customer Campaigns';

    #[\Override]
    protected static ?int $navigationSort = 12;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess();
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return CustomerCampaignForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return CustomerCampaignsTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    /** @param CustomerCampaign $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCustomerCampaigns::route('/'),
        ];
    }
}
