<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Category;
use App\Models\Setting;
use Filament\Pages\Page;
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Printable Menu';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 16;

    protected string $view = 'filament.pages.printable-menu';

    public string $activeView = 'menu';

    public string $menuLayout = 'elegant';

    /** @return Collection<int, mixed> */
    public function getCategories(): Collection
    {
        return Category::query()->where('is_active', true)
            ->with(['products' => fn (Builder $q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $cat) => $cat->products->isNotEmpty());
    }

    /** @return array<string, mixed> */
    public function getStoreInfo(): array
    {
        return [
            'name' => Setting::get('store_name', 'My Bakery'),
            'tagline' => Setting::get('business_tagline', ''),
            'phone' => Setting::get('store_phone', ''),
            'email' => Setting::get('store_email', ''),
            'address' => Setting::get('store_address', ''),
            'disclaimer' => Setting::get('allergy_disclaimer', ''),
        ];
    }

    public function getStorefrontUrl(): string
    {
        return 'http://'.tenant()->domains->first()->domain;
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
