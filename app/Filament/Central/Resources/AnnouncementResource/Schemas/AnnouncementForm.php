<?php

namespace App\Filament\Central\Resources\AnnouncementResource\Schemas;

use App\Enums\Platform\AnnouncementType;
use App\Enums\Platform\SubscriptionTier;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Announcement Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('body')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options(AnnouncementType::class)
                            ->required()
                            ->default('info'),
                        CheckboxList::make('target_plans')
                            ->label('Target Plans')
                            ->options([
                                'all' => 'All Plans',
                                ...collect(SubscriptionTier::cases())
                                    ->mapWithKeys(fn (SubscriptionTier $tier) => [$tier->value => $tier->getLabel()])
                                    ->all(),
                            ])
                            ->required()
                            ->rule('min:1')
                            ->helperText('Select which plans should see this announcement'),
                    ]),
                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_dismissable')
                            ->label('Dismissable')
                            ->default(true),
                        DateTimePicker::make('starts_at')
                            ->label('Starts At'),
                        DateTimePicker::make('ends_at')
                            ->label('Ends At'),
                    ])->columns(2),
            ]);
    }
}
