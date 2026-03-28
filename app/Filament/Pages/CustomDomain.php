<?php

namespace App\Filament\Pages;

use App\Services\ForgeService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Stancl\Tenancy\Database\Models\Domain;

class CustomDomain extends Page
{
    use InteractsWithFormActions;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Custom Domain';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.custom-domain';

    protected static ?string $title = 'Custom Domain';

    public ?string $custom_domain = '';

    public ?string $dns_status = null;

    public ?string $ssl_status = null;

    public function mount(): void
    {
        $this->custom_domain = tenant()->custom_domain ?? '';
        if ($this->custom_domain) {
            $this->checkDns();
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

            View::make('filament.pages.custom-domain-info'),
        ]);
    }

    public function save(): void
    {
        $plan = tenant()->plan ?? 'free';

        if (! in_array($plan, ['growth', 'pro'])) {
            Notification::make()
                ->title('Custom domains are available on Growth and Pro plans')
                ->warning()
                ->send();

            return;
        }

        $domain = trim((string) $this->custom_domain);

        if (empty($domain)) {
            $this->removeCustomDomain();

            return;
        }

        // Validate domain format
        if (! preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/', $domain)) {
            Notification::make()
                ->title('Invalid domain format')
                ->danger()
                ->send();

            return;
        }

        $tenant = tenant();

        // Update tenant custom_domain
        $tenant->update(['custom_domain' => $domain]);

        // Add to Stancl domains table if not already there
        $existingDomain = Domain::query()->where('domain', $domain)->first();
        if (! $existingDomain) {
            $tenant->domains()->create(['domain' => $domain]);
        }

        // Add domain alias to Forge if configured
        if (ForgeService::isConfigured()) {
            $forge = resolve(ForgeService::class);
            $forge->addDomainAlias($domain);
        }

        $this->checkDns();

        Notification::make()
            ->title('Custom domain saved')
            ->body($this->dns_status === 'verified'
                ? 'DNS is correctly configured! SSL will be provisioned automatically.'
                : 'Please configure your DNS records — point an A record to 137.184.194.56')
            ->success()
            ->send();

        // Auto-provision SSL if DNS is already verified
        if ($this->dns_status === 'verified') {
            $this->provisionSsl($domain);
        }
    }

    public function verifyDns(): void
    {
        $this->checkDns();

        if ($this->dns_status === 'verified') {
            Notification::make()
                ->title('DNS Verified!')
                ->body('Your domain is correctly pointing to our servers. Provisioning SSL...')
                ->success()
                ->send();

            // Auto-provision SSL when DNS is verified
            $this->provisionSsl((string) $this->custom_domain);
        } else {
            Notification::make()
                ->title('DNS Not Configured')
                ->body('Your domain is not yet pointing to 137.184.194.56. DNS changes can take up to 48 hours.')
                ->warning()
                ->send();
        }
    }

    protected function provisionSsl(string $domain): void
    {
        if (! ForgeService::isConfigured()) {
            $this->ssl_status = 'manual';

            return;
        }

        $forge = resolve(ForgeService::class);
        $success = $forge->obtainSslCertificate($domain);

        $this->ssl_status = $success ? 'provisioning' : 'failed';

        if ($success) {
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

    protected function checkDns(): void
    {
        if (empty($this->custom_domain)) {
            $this->dns_status = null;

            return;
        }

        $ip = gethostbyname($this->custom_domain);
        $this->dns_status = ($ip === '137.184.194.56') ? 'verified' : 'pending';
    }

    protected function removeCustomDomain(): void
    {
        $tenant = tenant();
        $oldDomain = $tenant->custom_domain;

        if ($oldDomain) {
            Domain::query()->where('domain', $oldDomain)->where('tenant_id', $tenant->id)->delete();

            // Remove from Forge if configured
            if (ForgeService::isConfigured()) {
                $forge = resolve(ForgeService::class);
                $forge->removeDomainAlias($oldDomain);
            }
        }

        $tenant->update(['custom_domain' => null]);
        $this->dns_status = null;
        $this->ssl_status = null;

        Notification::make()
            ->title('Custom domain removed')
            ->success()
            ->send();
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
