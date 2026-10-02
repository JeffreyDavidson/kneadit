<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Platform\AddCustomDomain;
use App\Actions\Platform\RemoveCustomDomain;
use App\Actions\Platform\VerifyCustomDomain;
use App\Enums\Platform\DnsVerificationStatus;
use App\Enums\Platform\DomainCheck;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Platform\Tenant;
use App\Services\Platform\CustomDomainService;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenantUrlGenerator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;

class CustomDomain extends Page
{
    use InteractsWithFormActions;
    use RequiresManagerRole;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Custom Domain';

    #[\Override]
    protected static ?int $navigationSort = 4;

    #[\Override]
    protected string $view = 'filament.pages.settings.custom-domain';

    #[\Override]
    protected static ?string $title = 'Custom Domain';

    public ?string $custom_domain = '';

    public ?DnsVerificationStatus $dns_status = null;

    public ?bool $https_ok = null;

    public ?string $ssl_status = null;

    public function mount(): void
    {
        $this->custom_domain = $this->currentTenant()->custom_domain ?? '';
        if ($this->custom_domain) {
            $this->refreshDnsStatus();
        }
    }

    #[\Override]
    public function content(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Custom Domain')
                ->description('Use your own domain name for your bakery storefront. Available on Growth and Pro plans.')
                ->schema([
                    TextInput::make('custom_domain')
                        ->label('Your Domain')
                        ->placeholder('sweetdreamsbakery.com')
                        ->helperText('Enter your domain without http:// or www.'),
                ]),

            View::make('filament.pages.settings.custom-domain-info'),
        ]);
    }

    public function save(): void
    {
        $domainService = resolve(CustomDomainService::class);
        $tenant = $this->currentTenant();
        $plan = $tenant->plan;

        if (! $plan->meetsRequirement(SubscriptionTier::Growth)) {
            Notification::make()
                ->title('Custom domains are available on Growth and Pro plans')
                ->warning()
                ->send();

            return;
        }

        $domain = trim((string) $this->custom_domain);

        if ($domain === '' || $domain === '0') {
            resolve(RemoveCustomDomain::class)($tenant);
            $this->dns_status = null;
            $this->https_ok = null;
            $this->ssl_status = null;

            Notification::make()
                ->title('Custom domain removed')
                ->success()
                ->send();

            return;
        }

        $domain = resolve(AddCustomDomain::class)($tenant, $domain);
        $this->custom_domain = $domain;
        $check = $this->refreshDnsStatus();

        $serverIp = $domainService->serverIp();

        Notification::make()
            ->title('Custom domain saved')
            ->body($check?->dnsPointsHere()
                ? 'DNS is correctly configured!'
                : "Please configure your DNS records — point an A record to {$serverIp}")
            ->success()
            ->send();

        if ($check === DomainCheck::HttpsUnavailable) {
            $this->handleSslProvisioning($domain);
        }
    }

    public function verifyDns(): void
    {
        $check = $this->refreshDnsStatus();

        if ($check === DomainCheck::Verified) {
            Notification::make()
                ->title('Domain verified!')
                ->body('Your domain points to our servers and answers over HTTPS.')
                ->success()
                ->send();

            return;
        }

        if ($check === DomainCheck::HttpsUnavailable) {
            Notification::make()
                ->title('DNS OK, no HTTPS certificate yet')
                ->body('Your domain points to our servers, but it has no valid SSL certificate yet. Request one below. Links use your bakery subdomain until HTTPS works.')
                ->warning()
                ->send();

            return;
        }

        $serverIp = resolve(CustomDomainService::class)->serverIp();

        Notification::make()
            ->title('DNS Not Configured')
            ->body("Your domain is not yet pointing to {$serverIp}. DNS changes can take up to 48 hours.")
            ->warning()
            ->send();
    }

    public function requestSsl(): void
    {
        $this->handleSslProvisioning((string) $this->custom_domain);
    }

    /**
     * Whether the saved domain points at the server but has no working HTTPS yet,
     * which is when asking for a certificate makes sense.
     */
    public function canRequestSsl(): bool
    {
        return $this->dns_status === DnsVerificationStatus::Verified && $this->https_ok === false;
    }

    /**
     * Checks DNS and HTTPS for the saved domain and records the result, so links only
     * use the custom domain once it is verified.
     */
    private function refreshDnsStatus(): ?DomainCheck
    {
        if (in_array($this->custom_domain, [null, '', '0'], true)) {
            $this->dns_status = null;
            $this->https_ok = null;

            return null;
        }

        $check = resolve(VerifyCustomDomain::class)($this->currentTenant());

        $this->dns_status = $check->dnsPointsHere()
            ? DnsVerificationStatus::Verified
            : DnsVerificationStatus::Pending;
        $this->https_ok = $check->dnsPointsHere() ? $check->isVerified() : null;

        return $check;
    }

    /**
     * The day DNS was verified in the bakery's timezone, or null while it is not verified.
     */
    #[Computed]
    public function verifiedOn(): ?string
    {
        return $this->currentTenant()->custom_domain_verified_at
            ?->setTimezone(resolve(TenantSettings::class)->orders->timezone)
            ->format('M j, Y');
    }

    /**
     * The storefront address links in emails and messages currently use.
     */
    #[Computed]
    public function linkBaseUrl(): string
    {
        return resolve(TenantUrlGenerator::class)->primaryStorefront($this->currentTenant());
    }

    /**
     * The subdomain address links use while the custom domain is not verified.
     */
    #[Computed]
    public function subdomainUrl(): string
    {
        return resolve(TenantUrlGenerator::class)->storefront($this->currentTenant());
    }

    private function handleSslProvisioning(string $domain): void
    {
        $result = resolve(CustomDomainService::class)->provisionSsl($domain);

        if ($result === null) {
            $this->ssl_status = 'manual';

            Notification::make()
                ->title('SSL must be set up manually')
                ->body('Please contact support to set up SSL for your domain.')
                ->warning()
                ->send();

            return;
        }

        $this->ssl_status = $result ? 'provisioning' : 'failed';

        if ($result) {
            Notification::make()
                ->title('SSL Certificate Requested')
                ->body('Let\'s Encrypt is issuing your certificate. This usually takes 1-2 minutes.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('SSL Provisioning Failed')
                ->body('Please contact support to manually set up SSL for your domain.')
                ->danger()
                ->send();
        }
    }

    /** @return array<int, Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Domain')
                ->action('save'),
            Action::make('verify')
                ->label('Verify domain')
                ->color('gray')
                ->action('verifyDns'),
            Action::make('requestSsl')
                ->label('Request SSL certificate')
                ->color('gray')
                ->visible(fn (): bool => $this->canRequestSsl())
                ->action('requestSsl'),
        ];
    }

    private function currentTenant(): Tenant
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \LogicException('A tenant must be initialized to manage a custom domain.');
        }

        return $tenant;
    }
}
