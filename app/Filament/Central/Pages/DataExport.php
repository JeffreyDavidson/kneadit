<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class DataExport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Data Export';

    protected string $view = 'filament.central.pages.data-export';

    public ?string $selectedTenant = null;

    /** @var array<string, mixed> */
    public array $counts = [];

    /** @return array<string, mixed> */
    public function getTenants(): array
    {
        return Tenant::query()->orderBy('store_name')
            ->get()
            ->mapWithKeys(fn (Tenant $t) => [$t->id => $t->store_name ?: $t->name])
            ->toArray();
    }

    public function updatedSelectedTenant(?string $value): void
    {
        if (! $value) {
            $this->counts = [];

            return;
        }

        $tenant = Tenant::query()->find($value);
        if (! $tenant) {
            $this->counts = [];

            return;
        }

        try {
            tenancy()->initialize($tenant);

            $this->counts = [
                'products' => DB::table('products')->count(),
                'categories' => DB::table('categories')->count(),
                'orders' => DB::table('orders')->count(),
                'customers' => DB::table('users')->count(),
                'reviews' => DB::table('reviews')->count(),
            ];

            tenancy()->end();
        } catch (\Throwable $e) {
            tenancy()->end();
            $this->counts = [];
        }
    }

    /** @return array<string, mixed> */
    public function getExportTypes(): array
    {
        return [
            'products' => [
                'icon' => Heroicon::OutlinedCube,
                'name' => 'Products',
                'description' => 'All products with prices, descriptions, and status.',
            ],
            'categories' => [
                'icon' => Heroicon::OutlinedTag,
                'name' => 'Categories',
                'description' => 'Product categories and their hierarchy.',
            ],
            'orders' => [
                'icon' => Heroicon::OutlinedShoppingBag,
                'name' => 'Orders & Items',
                'description' => 'All orders with line items and totals.',
            ],
            'customers' => [
                'icon' => Heroicon::OutlinedUsers,
                'name' => 'Customers',
                'description' => 'Customer accounts and contact details.',
            ],
            'reviews' => [
                'icon' => Heroicon::OutlinedStar,
                'name' => 'Reviews',
                'description' => 'Product reviews and ratings.',
            ],
        ];
    }
}
