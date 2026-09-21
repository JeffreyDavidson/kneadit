<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageForm;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\Customers\ContactMessage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ContactMessageResource extends Resource
{
    #[\Override]
    protected static ?string $model = ContactMessage::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?string $navigationLabel = 'Messages';

    #[\Override]
    protected static ?int $navigationSort = 3;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ContactMessageForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'subject'];
    }

    /** @param ContactMessage $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->subject ?? 'Message from '.$record->name;
    }

    /** @param ContactMessage $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'From' => $record->name,
            'Email' => $record->email ?? 'N/A',
            'Read' => $record->is_read ? 'Yes' : 'No',
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
