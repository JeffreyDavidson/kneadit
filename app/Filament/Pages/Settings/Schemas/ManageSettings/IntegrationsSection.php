<?php

namespace App\Filament\Pages\Settings\Schemas\ManageSettings;

use App\Filament\Pages\Operations\WebhooksDocs;
use App\Filament\Pages\Settings\ManageSettings;
use App\Rules\SafeWebhookUrl;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class IntegrationsSection
{
    public static function make(): Section
    {
        return Section::make('Integrations')
            ->description('Push order events to Zapier, QuickBooks, your CRM — any HTTPS endpoint that can accept a signed POST.')
            ->afterHeader([
                Action::make('webhookDocs')
                    ->label('Docs')
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->color('gray')
                    ->url(fn (): string => WebhooksDocs::getUrl())
                    ->openUrlInNewTab(),

                Action::make('sendTestWebhook')
                    ->label('Send Test')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('gray')
                    ->visible(fn (Get $get): bool => filled($get('webhook_url')))
                    ->action(fn (ManageSettings $livewire) => $livewire->sendTestWebhook()),
            ])
            ->schema([
                TextInput::make('webhook_url')
                    ->label('Webhook URL')
                    ->url()
                    ->rule(resolve(SafeWebhookUrl::class))
                    ->placeholder('https://hooks.zapier.com/...')
                    ->helperText(new HtmlString(
                        'We POST a JSON body when these events fire: <code>order.created</code>, <code>order.updated</code>, '
                        . '<code>order.cancelled</code>, <code>order.delivered</code>. '
                        . 'Each request includes <code>X-KneadIt-Signature</code> (HMAC-SHA256 of the body, signed with the secret below). '
                        . 'The destination must be a public HTTPS endpoint.',
                    ))
                    ->columnSpanFull(),

                TextInput::make('webhook_secret')
                    ->label('Signing Secret')
                    ->disabled()
                    ->dehydrated()
                    ->copyable(copyMessage: 'Secret copied to clipboard')
                    ->placeholder('A 40-char secret will be generated when you save a URL.')
                    ->helperText('Auto-generated. Use this on your endpoint to verify the X-KneadIt-Signature header.')
                    ->suffixAction(
                        Action::make('regenerateWebhookSecret')
                            ->icon(Heroicon::OutlinedArrowPath)
                            ->color('gray')
                            ->tooltip('Regenerate — existing integrations will need the new value.')
                            ->visible(fn (Get $get): bool => filled($get('webhook_secret')))
                            ->requiresConfirmation()
                            ->modalHeading('Regenerate signing secret?')
                            ->modalDescription('Any integration relying on the current secret to verify signatures will start rejecting requests until you update it with the new value.')
                            ->action(fn (ManageSettings $livewire) => $livewire->regenerateWebhookSecret()),
                    )
                    ->columnSpanFull(),
            ]);
    }
}
