<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Actions\Platform\AddCustomDomain;
use App\Actions\Platform\PauseTenant;
use App\Actions\Platform\RemoveCustomDomain;
use App\Actions\Platform\ResumeTenant;
use App\Actions\Platform\VerifyCustomDomain;
use App\Actions\Tenants\ChangeTenantSubdomain;
use App\Enums\Platform\DomainCheck;
use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\Tenant;
use App\Services\Platform\CustomDomainService;
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
            Action::make('pause')
                ->label('Pause bakery')
                ->icon(Heroicon::OutlinedPauseCircle)
                ->color('danger')
                ->authorize('platform-admin')
                ->visible(fn (): bool => ! $this->record->is_paused)
                ->requiresConfirmation()
                ->modalDescription('The bakery stops taking orders: its API and KneadIt storefront are closed and customers get no scheduled emails. The owner can still sign in to subscribe.')
                ->action(function (PauseTenant $pauseTenant): void {
                    $pauseTenant($this->record);

                    Notification::make()
                        ->title('Bakery paused')
                        ->success()
                        ->send();
                }),
            Action::make('resume')
                ->label('Resume bakery')
                ->icon(Heroicon::OutlinedPlayCircle)
                ->color('success')
                ->authorize('platform-admin')
                ->visible(fn (): bool => $this->record->is_paused)
                ->requiresConfirmation()
                ->action(function (ResumeTenant $resumeTenant): void {
                    $resumeTenant($this->record);

                    Notification::make()
                        ->title('Bakery resumed')
                        ->success()
                        ->send();
                }),
            Action::make('changeSubdomain')
                ->label('Change subdomain')
                ->icon(Heroicon::OutlinedLink)
                ->color('gray')
                ->authorize('platform-admin')
                ->modalWidth('md')
                ->modalDescription('The old subdomain keeps working and redirects visitors to the new one.')
                ->fillForm(fn (): array => ['subdomain' => $this->record->subdomain])
                ->schema([
                    TextInput::make('subdomain')
                        ->label('Subdomain')
                        ->placeholder('sweetbakes')
                        ->helperText('Lowercase letters, numbers and hyphens. The bakery id does not change.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    try {
                        resolve(ChangeTenantSubdomain::class)($this->record, Arr::string($data, 'subdomain'));
                    } catch (ValidationException $exception) {
                        // Show the message on the field inside the open modal.
                        throw ValidationException::withMessages([
                            'mountedActions.0.data.subdomain' => $exception->errors()['subdomain'],
                        ]);
                    }

                    Notification::make()
                        ->title('Subdomain changed')
                        ->success()
                        ->send();
                }),
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
                ->label('Verify domain')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('gray')
                ->authorize('platform-admin')
                ->visible(fn (): bool => filled($this->record->custom_domain))
                ->action(function (): void {
                    $check = resolve(VerifyCustomDomain::class)($this->record);

                    $notification = match ($check) {
                        DomainCheck::Verified => Notification::make()
                            ->title('Domain verified')
                            ->body('DNS: OK. HTTPS: OK.')
                            ->success(),
                        DomainCheck::VerifiedThroughProxy => Notification::make()
                            ->title('Domain verified')
                            ->body('DNS: served through a proxy (for example Cloudflare). HTTPS: OK.')
                            ->success(),
                        DomainCheck::HttpsUnavailable => Notification::make()
                            ->title('DNS OK, no HTTPS certificate yet')
                            ->body("DNS: OK. HTTPS: {$this->record->custom_domain} has no valid certificate yet. Links use the subdomain until it does.")
                            ->warning(),
                        DomainCheck::DnsMissing => Notification::make()
                            ->title('DNS not configured')
                            ->body("DNS: {$this->record->custom_domain} is not pointing at the server yet.")
                            ->warning(),
                    };

                    $notification->send();
                }),
            Action::make('requestSslCertificate')
                ->label('Request SSL certificate')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('gray')
                ->authorize('platform-admin')
                ->visible(fn (): bool => filled($this->record->custom_domain))
                ->action(function (): void {
                    $requested = resolve(CustomDomainService::class)->provisionSsl((string) $this->record->custom_domain);

                    $notification = match ($requested) {
                        true => Notification::make()
                            ->title('SSL certificate requested')
                            ->body('Let\'s Encrypt is issuing the certificate. Run Verify domain again in a minute or two.')
                            ->success(),
                        false => Notification::make()
                            ->title('SSL certificate request failed')
                            ->body('Forge did not accept the request. Check the domain in Forge.')
                            ->danger(),
                        null => Notification::make()
                            ->title('Forge is not configured')
                            ->body('Issue the certificate in Forge directly.')
                            ->warning(),
                    };

                    $notification->send();
                }),
        ];
    }
}
