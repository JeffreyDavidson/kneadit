<?php

namespace App\Filament\Pages\Platform\OnboardingSteps;

use App\Enums\Orders\PaymentMethod;
use App\Filament\Pages\Platform\Onboarding;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

final class PaymentsStep extends OnboardingStep
{
    public static function key(): string
    {
        return 'payments';
    }

    public static function defaults(TenantSettings $settings): array
    {
        $manager = resolve(SettingsManager::class);
        $methods = $manager->get('payment_methods');
        $decodedMethods = is_string($methods) ? json_decode($methods, true) : null;
        $selectedMethods = collect(is_array($decodedMethods) ? $decodedMethods : [])
            ->filter(fn (mixed $method): bool => is_string($method))
            ->values()
            ->all();

        $canManagePayments = Gate::allows('manage-payments');

        // The PayPal secret is write-only: it is never loaded into the page
        // state, which Livewire sends to the browser.
        return [
            'payment_methods' => $selectedMethods !== [] ? $selectedMethods : [PaymentMethod::Cash->value],
            'paypal_client_id' => $canManagePayments ? $manager->get('paypal_client_id', '') : '',
            'paypal_client_secret' => '',
            'paypal_sandbox' => $canManagePayments ? $manager->get('paypal_sandbox', '1') === '1' : true,
        ];
    }

    public static function make(Onboarding $page): Step
    {
        return Step::make('Payments')
            ->icon(Heroicon::OutlinedCreditCard)
            ->description('How you get paid')
            ->schema([
                Section::make('Payment Collection')
                    ->description('Choose how you want to collect payments from customers.')
                    ->schema([
                        CheckboxList::make('payments.payment_methods')
                            ->label('Payment Methods')
                            ->helperText('Select all that apply — offer your customers multiple ways to pay.')
                            ->options([
                                PaymentMethod::Stripe->value => 'Stripe — Credit cards, Apple Pay, Google Pay',
                                PaymentMethod::PayPal->value => 'PayPal — Accept payments through PayPal Business',
                                PaymentMethod::Cash->value => 'Cash / Manual — In person (cash, Venmo, Zelle, etc.)',
                            ])
                            ->descriptions([
                                PaymentMethod::Stripe->value => 'Connect your own Stripe account. Payments go directly to you.',
                                PaymentMethod::PayPal->value => 'Invoices sent automatically. Requires a PayPal Business account.',
                                PaymentMethod::Cash->value => 'You handle payment collection outside the platform.',
                            ])
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        Section::make('Stripe Connection')
                            ->description('Connect your own Stripe account — payments go directly to you, not us.')
                            ->schema([
                                View::make('filament.pages.shared.stripe-connect-status'),
                            ])
                            ->visible(fn (Get $get): bool => Gate::allows('manage-payments') && in_array(PaymentMethod::Stripe->value, self::selectedMethods($get), true)),

                        Section::make('PayPal Connection')
                            ->description('Connect your PayPal Business account.')
                            ->schema([
                                TextInput::make('payments.paypal_client_id')
                                    ->label('PayPal Client ID')
                                    ->placeholder('Your PayPal Client ID')
                                    ->maxLength(255)
                                    ->helperText('Find this in your PayPal Developer Dashboard under Apps & Credentials.')
                                    ->required(fn (Get $get): bool => in_array(PaymentMethod::PayPal->value, self::selectedMethods($get), true)),
                                TextInput::make('payments.paypal_client_secret')
                                    ->label('PayPal Client Secret')
                                    ->password()
                                    ->placeholder(fn (): string => self::hasStoredSecret() ? 'Set — enter a new value to replace it' : 'Your PayPal Client Secret')
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => ! self::hasStoredSecret() && in_array(PaymentMethod::PayPal->value, self::selectedMethods($get), true)),
                                Toggle::make('payments.paypal_sandbox')
                                    ->label('Sandbox Mode (Testing)')
                                    ->helperText('Enable this to test payments without real money. Disable when you\'re ready to go live.')
                                    ->default(true),
                            ])
                            ->visible(fn (Get $get): bool => Gate::allows('manage-payments') && in_array(PaymentMethod::PayPal->value, self::selectedMethods($get), true)),
                    ])
                    ->footerActions([])
                    ->footerActionsAlignment(null),
            ])
            ->afterValidation(fn () => self::save($page->payments));
    }

    public static function save(array $data): void
    {
        $methods = self::normalizePaymentMethods($data['payment_methods'] ?? []);
        $methods = $methods !== [] ? $methods : [PaymentMethod::Cash->value];

        $settings = [
            'payment_methods' => json_encode($methods),
            'payment_method' => $methods[0],
        ];

        // Only the owner may change the PayPal credentials, and the secret is
        // only replaced when a new one is entered.
        if (Gate::allows('manage-payments') && in_array(PaymentMethod::PayPal->value, $methods, true)) {
            $settings['paypal_client_id'] = $data['paypal_client_id'];
            $settings['paypal_sandbox'] = $data['paypal_sandbox'] ? '1' : '0';

            if (filled($data['paypal_client_secret'] ?? null)) {
                $settings['paypal_client_secret'] = $data['paypal_client_secret'];
            }
        }

        resolve(SettingsManager::class)->setMany($settings);
    }

    private static function hasStoredSecret(): bool
    {
        return filled(resolve(SettingsManager::class)->get('paypal_client_secret'));
    }

    /** @return list<string> */
    private static function selectedMethods(Get $get): array
    {
        return self::normalizePaymentMethods($get('payments.payment_methods'));
    }

    /** @return list<string> */
    private static function normalizePaymentMethods(mixed $state): array
    {
        $methods = [];

        foreach (Arr::wrap($state) as $method) {
            if (is_string($method)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
