<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Actions\Platform\AddCustomDomain;
use App\Actions\Platform\RemoveCustomDomain;
use App\Actions\Platform\VerifyCustomDomain;
use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\Tenant;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Tenant $record
 */
class EditTenant extends EditRecord
{
    #[\Override]
    protected static string $resource = TenantResource::class;

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return TenantResource::getUrl('view', ['record' => $this->record]);
    }

    /** Notes are surfaced on the View page's Notes tab; don't duplicate them here. */
    #[\Override]
    public function getAllRelationManagers(): array
    {
        return [];
    }

    /**
     * The custom domain is changed only through these actions, so every change goes through
     * the same validation, domain record, Forge alias and verification reset as the bakery's
     * own Settings page.
     *
     * @return array<int, Action>
     */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('setCustomDomain')
                ->label('Set custom domain')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('gray')
                ->authorize('platform-admin')
                ->modalWidth('md')
                ->fillForm(fn (): array => ['custom_domain' => $this->record->custom_domain])
                ->schema([
                    TextInput::make('custom_domain')
                        ->label('Custom Domain')
                        ->placeholder('sweetbakes.com')
                        ->helperText('Enter the domain without http:// or www. A new domain starts unverified.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        resolve(AddCustomDomain::class)($this->record, Arr::string($data, 'custom_domain'));
                    } catch (ValidationException $exception) {
                        // Show the message on the field inside the open modal.
                        throw ValidationException::withMessages([
                            'mountedActions.0.data.custom_domain' => $exception->errors()['custom_domain'],
                        ]);
                    }

                    $this->refreshFormData(['custom_domain']);

                    Notification::make()
                        ->title('Custom domain saved')
                        ->success()
                        ->send();
                }),
            Action::make('removeCustomDomain')
                ->label('Remove custom domain')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->authorize('platform-admin')
                ->visible(fn (): bool => filled($this->record->custom_domain))
                ->requiresConfirmation()
                ->action(function (): void {
                    resolve(RemoveCustomDomain::class)($this->record);

                    $this->refreshFormData(['custom_domain']);

                    Notification::make()
                        ->title('Custom domain removed')
                        ->success()
                        ->send();
                }),
            Action::make('verifyCustomDomain')
                ->label('Verify DNS')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('gray')
                ->authorize('platform-admin')
                ->visible(fn (): bool => filled($this->record->custom_domain))
                ->action(function (): void {
                    $verified = resolve(VerifyCustomDomain::class)($this->record);

                    if (! $verified) {
                        Notification::make()
                            ->title('DNS not configured')
                            ->body("{$this->record->custom_domain} is not pointing at the server yet.")
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('DNS verified')
                        ->success()
                        ->send();
                }),
        ];
    }
}
