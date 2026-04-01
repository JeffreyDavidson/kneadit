<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\EmailCampaignResource\Pages;
use App\Filament\Central\Resources\EmailCampaignResource\Schemas\EmailCampaignForm;
use App\Filament\Central\Resources\EmailCampaignResource\Tables\EmailCampaignsTable;
use App\Models\Engagement\EmailCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Email Campaigns';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return EmailCampaignForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return EmailCampaignsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
        ];
    }
}
