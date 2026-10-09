<?php

declare(strict_types=1);

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\DataTransferObjects\Settings\SettingValue;
use App\Filament\Forms\Components\AddressInput;
use App\Filament\Forms\Components\PhoneInput;
use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Support\PhoneNumber;
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

    public static function label(): string
    {
        return 'Contact info';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $tenant = self::tenant();
        $manager = resolve(SettingsManager::class);

        return [
            'email' => $manager->get('store_email') ?: ($tenant->email ?? ''),
            'phone' => $manager->get('store_phone', ''),
            'address' => $manager->get('store_address', ''),
            'city' => $manager->get('store_city', ''),
            'state' => $manager->get('store_state', ''),
            'zip' => $manager->get('store_zip', ''),
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make(self::label())
            ->icon(Heroicon::OutlinedEnvelope)
            ->description('How customers can reach you')
            ->schema([
                Section::make('Contact information')
                    ->contained(false)
                    ->description('This information will be displayed on your storefront.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact.email')
                                ->label('Email address')
                                ->email()
                                ->required()
                                ->placeholder('hello@yourbakery.com'),
                            PhoneInput::make('contact.phone')
                                ->label('Phone number'),
                        ]),
                        AddressInput::make('contact.address')
                            ->label('Street address')
                            ->placeholder('123 Baker Street')
                            ->fills(city: 'contact.city', state: 'contact.state', zip: 'contact.zip')
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('contact.city')
                                ->label('City'),
                            TextInput::make('contact.state')
                                ->label('State'),
                            TextInput::make('contact.zip')
                                ->label('ZIP code'),
                        ]),
                    ]),
            ])
            ->afterValidation(fn () => self::save($page->contact));
    }

    public static function save(array $data): void
    {
        resolve(SettingsManager::class)->setMany([
            'store_email' => $data['email'],
            'store_phone' => PhoneNumber::normalize(SettingValue::nullableString($data['phone'] ?? null)) ?? '',
            'store_address' => $data['address'] ?? '',
            'store_city' => $data['city'] ?? '',
            'store_state' => $data['state'] ?? '',
            'store_zip' => $data['zip'] ?? '',
        ]);
    }
}
