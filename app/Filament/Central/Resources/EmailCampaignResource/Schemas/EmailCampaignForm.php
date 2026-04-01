<?php

namespace App\Filament\Central\Resources\EmailCampaignResource\Schemas;

use App\Enums\EmailCampaignSegment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Select::make('target_segment')
                    ->options(EmailCampaignSegment::class)
                    ->required()
                    ->default(EmailCampaignSegment::All),
            ]);
    }
}
