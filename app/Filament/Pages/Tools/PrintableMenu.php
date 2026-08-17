<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Category;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Laravel\Pennant\Feature;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintableMenu extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Printable Menu';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 16;

    protected string $view = 'filament.pages.tools.printable-menu';

    public string $activeView = 'menu';

    public string $menuLayout = 'elegant';

    /** @return Collection<int, Category> */
    public function getCategories(): Collection
    {
        return Category::query()->active()
            ->with(['products' => function (Builder $q): void {
                $q->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $cat) => $cat->products->isNotEmpty());
    }

    /** @return array<string, mixed> */
    public function getStoreInfo(): array
    {
        $settings = resolve(TenantSettings::class);
        $branding = $settings->branding;

        return [
            'name' => $settings->store->name,
            'tagline' => $branding->businessTagline ?? '',
            'phone' => $settings->store->phone ?? '',
            'email' => $settings->store->email ?? '',
            'address' => $settings->store->address ?? '',
            'disclaimer' => $branding->allergyDisclaimer ?? '',
        ];
    }

    public function getStorefrontUrl(): string
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \LogicException('A tenant must be initialized to generate a printable menu.');
        }

        $domain = $tenant->domains->first();

        if ($domain === null) {
            throw new \LogicException('The tenant must have a domain to generate a printable menu.');
        }

        return 'http://' . $domain->domain;
    }

    public function getQrCode(): string
    {
        /** @var HtmlString $qr */
        $qr = QrCode::size(100)->generate($this->getStorefrontUrl());

        return $qr->toHtml();
    }

    public function setView(string $view): void
    {
        $this->activeView = $view;
    }

    public function setLayout(string $layout): void
    {
        $this->menuLayout = $layout;
    }
}
