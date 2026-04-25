<?php

namespace App\Filament\Central\Resources\EmailCampaignResource\Schemas;

use App\Enums\Marketing\EmailCampaignSegment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audience')
                    ->description('Who this campaign goes to')
                    ->icon(Heroicon::OutlinedUsers)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->helperText('Internal label. Not visible to bakers.')
                            ->required()
                            ->maxLength(255),
                        Select::make('target_segment')
                            ->label('Segment')
                            ->options(EmailCampaignSegment::class)
                            ->required()
                            ->live()
                            ->default(EmailCampaignSegment::All),
                    ]),

                Section::make('Email Content')
                    ->description('What bakers will receive')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 400)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->required()
                            ->rows(8)
                            ->live(debounce: 400)
                            ->columnSpanFull(),
                        View::make('filament.central.partials.email-campaign-form-preview')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
