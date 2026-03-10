<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class RevenueLeaderboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Revenue Leaderboard';

    protected string $view = 'filament.central.pages.revenue-leaderboard';

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

    public function getSummaryStats(): array
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
