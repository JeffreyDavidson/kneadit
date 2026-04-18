<?php

namespace App\Filament\Central\Pages;

use App\Queries\Platform\TenantComparisonQuery;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TenantComparison extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Bakery Comparison';

    protected string $view = 'filament.central.pages.tenant-comparison';

    public string $activeTab = 'compare';

    /** @var array<string, mixed> */
    public array $selectedTenants = [];

    public function mount(): void
    {
        $ids = request()->query('tenants', []);
        if (is_array($ids)) {
            $this->selectedTenants = array_slice(array_filter($ids), 0, 3);
        }
    }

    /** @return array<string, mixed> */
    public function getAllTenants(): array
    {
        return TenantComparisonQuery::allTenants();
    }

    /** @return array<int, array<string, mixed>> */
    public function getComparisonData(): array
    {
        /** @var array<int, string> $tenantIds */
        $tenantIds = array_values(array_map('strval', $this->selectedTenants));

        return TenantComparisonQuery::comparison($tenantIds);
    }

    /** @return array<int, array<string, mixed>> */
    public function getLeaderboardData(): array
    {
        return TenantComparisonQuery::leaderboard();
    }

    /** @return array<string, mixed> */
    public function getLeaderboardSummaryStats(): array
    {
        return TenantComparisonQuery::leaderboardSummary();
    }
}
