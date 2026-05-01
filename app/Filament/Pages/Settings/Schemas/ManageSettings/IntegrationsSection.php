<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class IntegrationsSection
{
    public static function make(): Section
    {
        return Section::make('Integrations')
            ->description('Connect KneadIt to other tools via webhooks')
            ->schema([
                TextInput::make('webhook_url')
                    ->label('Webhook URL')
                    ->url()
                    ->placeholder('https://hooks.zapier.com/...')
                    ->helperText('We\'ll send order events (created, updated) to this URL')
                    ->columnSpanFull(),

                TextInput::make('webhook_secret')
                    ->label('Webhook Secret')
                    ->password()
                    ->placeholder('Optional signing secret for verification')
                    ->helperText('Used to sign webhook payloads (X-KneadIt-Signature header)')
                    ->columnSpanFull(),
            ]);
    }
}
