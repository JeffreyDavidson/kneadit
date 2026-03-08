<?php

namespace App\Filament\Resources\EmailCampaigns;

use App\Filament\Resources\EmailCampaigns\Pages\CreateEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\EditEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Resources\EmailCampaigns\Tables\EmailCampaignsTable;
use App\Models\EmailCampaign;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return EmailCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
            'create' => CreateEmailCampaign::route('/create'),
            'edit' => EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
