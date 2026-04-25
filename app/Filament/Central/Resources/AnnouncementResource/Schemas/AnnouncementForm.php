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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->description('What bakers will see in the announcement banner')
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 400)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->required()
                            ->live(debounce: 400)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options(AnnouncementType::class)
                            ->required()
                            ->default(AnnouncementType::Info)
                            ->live()
                            ->helperText('Sets the banner color and icon.'),
                        View::make('filament.central.partials.announcement-preview')
                            ->columnSpanFull(),
                    ]),

                Section::make('Audience')
                    ->description('Which tenants should see this announcement')
                    ->icon(Heroicon::OutlinedUsers)
                    ->columnSpanFull()
                    ->schema([
                        CheckboxList::make('target_plans')
                            ->label('Target Plans')
                            ->options([
                                'all' => 'All Plans',
                                ...collect(SubscriptionTier::cases())
                                    ->mapWithKeys(fn (SubscriptionTier $tier) => [$tier->value => $tier->getLabel()])
                                    ->all(),
                            ])
                            ->columns(2)
                            ->required()
                            ->rule('min:1')
                            ->helperText('Checking "All Plans" shows the announcement to every tenant regardless of plan.'),
                    ]),

                Section::make('Schedule & Display')
                    ->description('When the banner is live and how bakers can interact with it')
                    ->icon(Heroicon::OutlinedClock)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('When off, the banner is hidden even if the schedule window is open.')
                                ->default(true),
                            Toggle::make('is_dismissable')
                                ->label('Dismissable')
                                ->helperText('Lets bakers close the banner (hidden per-user).')
                                ->live()
                                ->default(true),
                        ]),
                        Grid::make(2)->schema([
                            DateTimePicker::make('starts_at')
                                ->label('Starts At')
                                ->helperText('Leave blank to start immediately.'),
                            DateTimePicker::make('ends_at')
                                ->label('Ends At')
                                ->helperText('Leave blank for no end date.'),
                        ]),
                    ]),
            ]);
    }
}
