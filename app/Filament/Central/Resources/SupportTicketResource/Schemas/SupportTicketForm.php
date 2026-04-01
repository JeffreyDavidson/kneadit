<?php

namespace App\Filament\Central\Resources\SupportTicketResource\Schemas;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
use App\Models\Tenant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject')->required()->maxLength(255),
            Select::make('tenant_id')
                ->label('Bakery')
                ->relationship('tenant', 'store_name')
                ->getOptionLabelFromRecordUsing(fn (Tenant $record) => $record->store_name ?: $record->name)
                ->required()
                ->searchable(),
            Grid::make(2)->schema([
                Select::make('status')
                    ->options(SupportTicketStatus::class)
                    ->default(SupportTicketStatus::Open)
                    ->required(),
                Select::make('priority')
                    ->options(SupportTicketPriority::class)
                    ->default(SupportTicketPriority::Normal)
                    ->required(),
            ]),
            Textarea::make('body')->required()->rows(5),
            Textarea::make('admin_notes')->rows(3),
        ]);
    }
}
