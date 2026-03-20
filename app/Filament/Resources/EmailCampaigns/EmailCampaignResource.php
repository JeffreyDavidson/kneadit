<?php

namespace App\Filament\Resources\EmailCampaigns;

use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Resources\EmailCampaigns\Tables\EmailCampaignsTable;
use App\Filament\Traits\RequiresRole;
use App\Models\EmailCampaign;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmailCampaignResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = EmailCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 6;

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['subject'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->subject;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status' => ucfirst($record->status ?? 'draft'),
            'Recipients' => $record->recipient_count ?? 0,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
        ];
    }
}
