<?php

namespace App\Filament\Pages\Settings\Schemas;

use App\Filament\Pages\Settings\Schemas\ManageSettings\CateringSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\ComplianceSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\GiftCardsSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\IntegrationsSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\NotificationSettingsSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\OrderEmailsSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\OrderJourneySection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\OrderSettingsSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\PaymentMethodsSection;
use App\Filament\Pages\Settings\Schemas\ManageSettings\StoreInformationSection;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;

class ManageSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            StoreInformationSection::make(),
            OrderSettingsSection::make(),
            OrderJourneySection::make(),
            CateringSection::make(),
            NotificationSettingsSection::make(),
            OrderEmailsSection::make(),
            PaymentMethodsSection::make(),
            ComplianceSection::make(),
            GiftCardsSection::make(),
            IntegrationsSection::make(),

            // Actions block stays inline because the callbacks ('save',
            // 'resetToDefaults') resolve against ManageSettings's page methods
            // — extracting to a sibling class would require passing the page
            // reference around just to keep the action callable.
            Actions::make([
                Action::make('save')
                    ->label('Save Settings')
                    ->color('primary')
                    ->action('save'),

                Action::make('reset')
                    ->label('Reset to Defaults')
                    ->color('gray')
                    ->action('resetToDefaults')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Settings')
                    ->modalDescription('Are you sure you want to reset all settings to their default values?'),
            ])
                ->alignEnd()
                ->columnSpanFull(),
        ]);
    }
}
