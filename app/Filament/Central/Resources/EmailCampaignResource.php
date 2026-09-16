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
    #[\Override]
    protected static ?string $model = EmailCampaign::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?string $navigationLabel = 'Email Campaigns';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    public static function form(Schema $form): Schema
    {
        return EmailCampaignForm::configure($form);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return EmailCampaignsTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
        ];
    }
}
