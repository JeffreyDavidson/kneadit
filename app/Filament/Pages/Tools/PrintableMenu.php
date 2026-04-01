<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Inventory\Category;
use App\Services\Settings\TenantSettings;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Laravel\Pennant\Feature;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintableMenu extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
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

    /** @return Collection<int, mixed> */
    public function getCategories(): Collection
    {
        return Category::query()->active()
            ->with(['products' => fn (Builder $q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $cat) => $cat->products->isNotEmpty());
    }

    /** @return array<string, mixed> */
    public function getStoreInfo(): array
    {
        $settings = app(TenantSettings::class);

        return [
            'name' => $settings->storeName,
            'tagline' => $settings->businessTagline ?? '',
            'phone' => $settings->storePhone ?? '',
            'email' => $settings->storeEmail ?? '',
            'address' => $settings->storeAddress ?? '',
            'disclaimer' => $settings->allergyDisclaimer ?? '',
        ];
    }

    public function getStorefrontUrl(): string
    {
        return 'http://' . tenant()->domains->first()->domain;
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
