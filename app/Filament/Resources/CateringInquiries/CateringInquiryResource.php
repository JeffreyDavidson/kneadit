<?php

namespace App\Filament\Resources\CateringInquiries;

use App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries;
use App\Filament\Resources\CateringInquiries\Pages\ViewCateringInquiry;
use App\Filament\Resources\CateringInquiries\Schemas\CateringInquiryForm;
use App\Filament\Resources\CateringInquiries\Tables\CateringInquiriesTable;
use App\Models\Customers\CateringInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CateringInquiryResource extends Resource
{
    #[\Override]
    protected static ?string $model = CateringInquiry::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 8;

    #[\Override]
    protected static ?string $navigationLabel = 'Catering';

    #[\Override]
    protected static ?string $modelLabel = 'Catering Inquiry';

    #[\Override]
    protected static ?string $pluralModelLabel = 'Catering Inquiries';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return CateringInquiryForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return CateringInquiriesTable::configure($table);
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_name', 'customer_email'];
    }

    /** @param CateringInquiry $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->customer_name;
    }

    /** @param CateringInquiry $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->customer_email ?? 'N/A',
            'Event' => $record->event_type,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCateringInquiries::route('/'),
            'view' => ViewCateringInquiry::route('/{record}'),
        ];
    }
}
