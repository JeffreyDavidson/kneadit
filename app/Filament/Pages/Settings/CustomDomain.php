<?php

namespace App\Filament\Pages\Settings;

use App\Actions\Platform\AddCustomDomain;
use App\Actions\Platform\RemoveCustomDomain;
use App\Enums\Platform\DnsVerificationStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Services\Platform\CustomDomainService;
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

class CustomDomain extends Page
{
    use InteractsWithFormActions;
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Custom Domain';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.settings.custom-domain';

    protected static ?string $title = 'Custom Domain';

    public ?string $custom_domain = '';

    public ?DnsVerificationStatus $dns_status = null;

    public ?string $ssl_status = null;

    public function mount(): void
    {
        $this->custom_domain = tenant()->custom_domain ?? '';
        if ($this->custom_domain) {
            $this->refreshDnsStatus();
        }
    }

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
        $plan = tenant()->plan;

        if (! $plan?->meetsRequirement(SubscriptionTier::Growth)) {
            Notification::make()
                ->title('Custom domains are available on Growth and Pro plans')
                ->warning()
                ->send();

            return;
        }

        $domain = trim((string) $this->custom_domain);

        if (empty($domain)) {
            resolve(RemoveCustomDomain::class)(tenant());
            $this->dns_status = null;
            $this->ssl_status = null;

            Notification::make()
                ->title('Custom domain removed')
                ->success()
                ->send();

            return;
        }

        if (! $domainService->isValidFormat($domain)) {
            Notification::make()
                ->title('Invalid domain format')
                ->danger()
                ->send();

            return;
        }

        resolve(AddCustomDomain::class)(tenant(), $domain);
        $this->refreshDnsStatus();

        $serverIp = $domainService->serverIp();

        Notification::make()
            ->title('Custom domain saved')
            ->body($this->dns_status === DnsVerificationStatus::Verified
                ? 'DNS is correctly configured! SSL will be provisioned automatically.'
                : "Please configure your DNS records — point an A record to {$serverIp}")
            ->success()
            ->send();

        if ($this->dns_status === DnsVerificationStatus::Verified) {
            $this->handleSslProvisioning($domain);
        }
    }

    public function verifyDns(): void
    {
        $this->refreshDnsStatus();

        if ($this->dns_status === DnsVerificationStatus::Verified) {
            Notification::make()
                ->title('DNS Verified!')
                ->body('Your domain is correctly pointing to our servers. Provisioning SSL...')
                ->success()
                ->send();

            $this->handleSslProvisioning((string) $this->custom_domain);
        } else {
            $serverIp = resolve(CustomDomainService::class)->serverIp();

            Notification::make()
                ->title('DNS Not Configured')
                ->body("Your domain is not yet pointing to {$serverIp}. DNS changes can take up to 48 hours.")
                ->warning()
                ->send();
        }
    }

    private function refreshDnsStatus(): void
    {
        if (empty($this->custom_domain)) {
            $this->dns_status = null;

            return;
        }

        $this->dns_status = resolve(CustomDomainService::class)->isDnsVerified($this->custom_domain)
            ? DnsVerificationStatus::Verified
            : DnsVerificationStatus::Pending;
    }

    private function handleSslProvisioning(string $domain): void
    {
        $result = resolve(CustomDomainService::class)->provisionSsl($domain);

        if ($result === null) {
            $this->ssl_status = 'manual';

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
                ->label('Verify DNS')
                ->color('gray')
                ->action('verifyDns'),
        ];
    }
}
