<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class TenantComparison extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Bakery Comparison';

    protected string $view = 'filament.central.pages.tenant-comparison';

    public string $activeTab = 'compare';

    public array $selectedTenants = [];

    public function mount(): void
    {
        $ids = request()->query('tenants', []);
        if (is_array($ids)) {
            $this->selectedTenants = array_slice(array_filter($ids), 0, 3);
        }
    }

    public function getAllTenants(): array
    {
        return Tenant::orderBy('store_name')
            ->get()
            ->mapWithKeys(fn ($t) => [$t->id => $t->store_name ?: $t->name])
            ->toArray();
    }

    public function getComparisonData(): array
    {
        if (empty($this->selectedTenants)) {
            return [];
        }

        $tenants = Tenant::whereIn('id', $this->selectedTenants)->get();
        $results = [];

        foreach ($tenants as $tenant) {
            $data = [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'plan' => $tenant->plan ?? 'free',
                'days_since_signup' => $tenant->created_at ? (int) Carbon::parse($tenant->created_at)->diffInDays(now()) : 0,
                'total_orders' => 0,
                'month_orders' => 0,
                'total_products' => 0,
                'total_categories' => 0,
                'avg_review' => 0,
                'setup_completed' => 0,
            ];

            $setupChecks = [
                ! empty($tenant->store_name),
                ! empty($tenant->store_logo),
                (bool) $tenant->storefront_enabled,
                ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c',
            ];

            try {
                tenancy()->initialize($tenant);

                $data['total_orders'] = DB::table('orders')->count();
                $data['month_orders'] = DB::table('orders')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
                $data['total_products'] = DB::table('products')->count();
                $data['total_categories'] = DB::table('categories')->count();
                $data['avg_review'] = round((float) DB::table('reviews')->avg('rating'), 1);

                $setupChecks[] = $data['total_products'] > 0;
                $setupChecks[] = $data['total_categories'] > 0;
                $setupChecks[] = $data['total_orders'] > 0;

                tenancy()->end();
            } catch (\Throwable $e) {
                tenancy()->end();
                $setupChecks = array_pad($setupChecks, 7, false);
            }

            $data['setup_completed'] = collect($setupChecks)->filter()->count();
            $data['health_score'] = $this->calculateHealthScore($tenant, $data);

            $results[] = $data;
        }

        return $results;
    }

    protected function calculateHealthScore(Tenant $tenant, array $data): int
    {
        $score = 0;

        if ($tenant->last_login_at) {
            $daysSinceLogin = Carbon::parse($tenant->last_login_at)->diffInDays(now());
            if ($daysSinceLogin < 1) {
                $score += 30;
            } elseif ($daysSinceLogin < 3) {
                $score += 25;
            } elseif ($daysSinceLogin < 7) {
                $score += 15;
            } elseif ($daysSinceLogin < 30) {
                $score += 5;
            }
        }

        if ($data['total_orders'] >= 50) {
            $score += 30;
        } elseif ($data['total_orders'] >= 20) {
            $score += 20;
        } elseif ($data['total_orders'] >= 5) {
            $score += 10;
        } elseif ($data['total_orders'] > 0) {
            $score += 5;
        }

        if ($data['total_products'] >= 20) {
            $score += 20;
        } elseif ($data['total_products'] >= 10) {
            $score += 15;
        } elseif ($data['total_products'] >= 3) {
            $score += 10;
        } elseif ($data['total_products'] > 0) {
            $score += 5;
        }

        $score += (int) round(($data['setup_completed'] / 7) * 20);

        return min($score, 100);
    }

    // ── Revenue Leaderboard Methods ──

    public function getLeaderboardData(): array
    {
        $tenants = Tenant::all();
        $results = [];

        foreach ($tenants as $tenant) {
            $data = [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan ?? 'free',
                'total_orders' => 0,
                'month_orders' => 0,
                'total_products' => 0,
                'avg_review' => 0,
            ];

            try {
                tenancy()->initialize($tenant);

                $data['total_orders'] = DB::table('orders')->count();
                $data['month_orders'] = DB::table('orders')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
                $data['total_products'] = DB::table('products')->count();
                $data['avg_review'] = round((float) DB::table('reviews')->avg('rating'), 1);

                tenancy()->end();
            } catch (\Throwable $e) {
                tenancy()->end();
            }

            $results[] = $data;
        }

        usort($results, fn ($a, $b) => $b['total_orders'] <=> $a['total_orders']);

        return $results;
    }

    public function getLeaderboardSummaryStats(): array
    {
        $data = $this->getLeaderboardData();
        $totalOrders = array_sum(array_column($data, 'total_orders'));
        $count = count($data);

        return [
            'total_orders' => $totalOrders,
            'avg_orders' => $count > 0 ? round($totalOrders / $count, 1) : 0,
            'total_bakeries' => $count,
        ];
    }
}
