<?php

namespace App\Filament\Resources\Surveys;

use App\Filament\Resources\Surveys\Pages\ListSurveys;
use App\Filament\Resources\Surveys\Pages\ViewSurvey;
use App\Filament\Resources\Surveys\Schemas\SurveyForm;
use App\Filament\Resources\Surveys\Tables\SurveysTable;
use App\Models\Engagement\Survey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SurveyResource extends Resource
{
    #[\Override]
    protected static ?string $model = Survey::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 5;

    #[\Override]
    protected static ?string $navigationLabel = 'Surveys';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return SurveyForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return SurveysTable::configure($table);
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    /** @param Survey $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    /** @param Survey $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status' => $record->is_active ? 'Active' : 'Inactive',
            'Responses' => (string) ($record->responses_count ?? 0),
        ];
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSurveys::route('/'),
            'view' => ViewSurvey::route('/{record}'),
        ];
    }
}
