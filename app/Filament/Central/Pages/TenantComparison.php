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

    /** @var array<int, string> */
    public array $selectedTenants = [];

    public function mount(): void
    {
        $ids = request()->query('tenants', []);
        if (is_array($ids)) {
            $tenantIds = [];

            foreach ($ids as $id) {
                if (is_string($id) && $id !== '') {
                    $tenantIds[] = $id;
                }
            }

            $this->selectedTenants = array_slice($tenantIds, 0, 3);
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
        return TenantComparisonQuery::comparison($this->selectedTenants);
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
