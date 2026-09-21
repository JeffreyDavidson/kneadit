<?php

namespace App\Filament\Central\Pages;

use App\DataTransferObjects\Platform\TenantComparisonResult;
use App\DataTransferObjects\Platform\TenantLeaderboardEntry;
use App\Queries\Platform\TenantComparisonQuery;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TenantComparison extends Page
{
    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    #[\Override]
    protected static ?int $navigationSort = 3;

    #[\Override]
    protected static ?string $title = 'Bakery Comparison';

    #[\Override]
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
        return resolve(TenantComparisonQuery::class)->allTenants();
    }

    /** @return array<int, array<string, mixed>> */
    public function getComparisonData(): array
    {
        return array_map(
            static fn (TenantComparisonResult $result): array => $result->toArray(),
            resolve(TenantComparisonQuery::class)->comparison(array_values($this->selectedTenants)),
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function getLeaderboardData(): array
    {
        return array_map(
            static fn (TenantLeaderboardEntry $entry): array => $entry->toArray(),
            resolve(TenantComparisonQuery::class)->leaderboard(),
        );
    }

    /** @return array<string, mixed> */
    public function getLeaderboardSummaryStats(): array
    {
        return resolve(TenantComparisonQuery::class)->leaderboardSummary()->toArray();
    }
}
