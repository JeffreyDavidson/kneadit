<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Category;
use App\Models\Setting;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintableMenu extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'growth';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Printable Menu';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 16;

    protected string $view = 'filament.pages.printable-menu';

    public string $activeView = 'menu';

    public string $menuLayout = 'elegant';

    public function getCategories()
    {
        return Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($cat) => $cat->products->isNotEmpty());
    }

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
        return QrCode::size(100)->generate($this->getStorefrontUrl())->toHtml();
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
