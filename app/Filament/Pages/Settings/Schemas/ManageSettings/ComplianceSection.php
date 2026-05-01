<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class ComplianceSection
{
    public static function make(): Section
    {
        return Section::make('Compliance & Legal')
            ->description('Legal disclaimers and business compliance settings')
            ->schema([
                Textarea::make('allergy_disclaimer')
                    ->label('Allergy Disclaimer')
                    ->placeholder('Please inform us of any allergies or dietary restrictions...')
                    ->rows(3)
                    ->columnSpanFull(),

                MoneyInput::make('revenue_cap')
                    ->label('Annual Revenue Cap')
                    ->default('250000')
                    ->helperText('Annual revenue limit for compliance')
                    ->columnSpanFull(),

                Textarea::make('cancellation_policy')
                    ->label('Cancellation Policy')
                    ->placeholder('Orders cancelled within 48 hours of pickup are non-refundable.')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('deposit_policy')
                    ->label('Deposit Policy')
                    ->placeholder('A 50% deposit is required to secure your order. Remaining balance due at pickup.')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('refund_policy')
                    ->label('Refund Policy')
                    ->placeholder('No refunds on custom orders. Store credit may be offered at our discretion.')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('pickup_policy')
                    ->label('Pickup Policy')
                    ->placeholder('Orders not picked up within 2 hours of scheduled time will be forfeited.')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('additional_terms')
                    ->label('Additional Terms')
                    ->placeholder('Any other terms or conditions...')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('show_policies_on_storefront')
                    ->label('Show Policies on Storefront')
                    ->helperText('Display your policies in the storefront footer'),
            ]);
    }
}
