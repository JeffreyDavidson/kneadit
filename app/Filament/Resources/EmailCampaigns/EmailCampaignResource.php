<?php

namespace App\Filament\Resources\EmailCampaigns;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Resources\EmailCampaigns\Tables\EmailCampaignsTable;
use App\Models\Engagement\EmailCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class EmailCampaignResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = EmailCampaign::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 6;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return EmailCampaignForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return EmailCampaignsTable::configure($table);
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['subject'];
    }

    /** @param EmailCampaign $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->subject;
    }

    /** @param EmailCampaign $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status' => $record->status->getLabel(),
            'Recipients' => (string) ($record->recipient_count ?? 0),
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
        ];
    }
}
