<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

final class ContactStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'contact';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $tenant = tenant();
        $manager = app(SettingsManager::class);

        return [
            'email' => $manager->get('store_email') ?: ($tenant->email ?? ''),
            'phone' => $manager->get('store_phone', ''),
            'address' => $manager->get('store_address', ''),
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Contact Info')
            ->icon(Heroicon::OutlinedEnvelope)
            ->description('How customers can reach you')
            ->schema([
                Section::make('Contact Information')
                    ->description('This information will be displayed on your storefront.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact.email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->placeholder('hello@yourbakery.com'),
                            TextInput::make('contact.phone')
                                ->label('Phone Number')
                                ->tel()
                                ->placeholder('+1 (555) 123-4567'),
                        ]),
                        TextInput::make('contact.address')
                            ->label('Address')
                            ->placeholder('123 Baker Street, City, State 12345')
                            ->columnSpanFull(),
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->contact));
    }

    public static function save(array $data): void
    {
        app(SettingsManager::class)->setMany([
            'store_email' => $data['email'],
            'store_phone' => $data['phone'] ?? '',
            'store_address' => $data['address'] ?? '',
        ]);
    }
}
