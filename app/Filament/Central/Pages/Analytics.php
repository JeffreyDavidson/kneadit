<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Analytics';

    protected string $view = 'filament.central.pages.analytics';

    public function getSignupsByMonth(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Date::now()->subMonths($i);
            $months->push([
                'label' => $date->format('M Y'),
                'count' => Tenant::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ]);
        }

        return $months->toArray();
    }

    public function getPlanDistribution(): array
    {
        return Tenant::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();
    }

    public function getTrialConversion(): array
    {
        $total = Tenant::count();
        $onTrial = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();
        $expired = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->count();
        $converted = $total - $onTrial - $expired;

        return [
            'on_trial' => $onTrial,
            'expired' => $expired,
            'converted' => $converted,
        ];
    }

    public function getMonthlyGrowth(): array
    {
        $signups = $this->getSignupsByMonth();
        $growth = [];

        for ($i = 1; $i < count($signups); $i++) {
            $prev = $signups[$i - 1]['count'];
            $curr = $signups[$i]['count'];
            $rate = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
            $growth[] = [
                'label' => $signups[$i]['label'],
                'rate' => $rate,
            ];
        }

        return $growth;
    }

    public function getTotalSignups(): int
    {
        return Tenant::count();
    }

    public function getThisMonthSignups(): int
    {
        return Tenant::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function getAvgDaysOnTrial(): float
    {
        $tenants = Tenant::whereNotNull('trial_ends_at')
            ->select('trial_ends_at', 'created_at')
            ->get();

        if ($tenants->isEmpty()) {
            return 0;
        }

        $avgDays = $tenants->avg(function (Tenant $tenant) {
            return Date::parse($tenant->created_at)
                ->diffInDays(Date::parse($tenant->trial_ends_at));
        });

        return round($avgDays, 1);
    }

    public function getMostPopularPlan(): string
    {
        return Tenant::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->orderByDesc('count')
            ->value('plan') ?? 'N/A';
    }
}
