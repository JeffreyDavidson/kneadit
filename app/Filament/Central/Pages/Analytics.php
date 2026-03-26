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

    /** @return array<int, array<string, mixed>> */
    public function getSignupsByMonth(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Date::now()->subMonths($i);
            $months->push([
                'label' => $date->format('M Y'),
                'count' => Tenant::query()->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ]);
        }

        return $months->values()->all();
    }

    /** @return array<string, mixed> */
    public function getPlanDistribution(): array
    {
        return Tenant::query()->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();
    }

    /** @return array<string, mixed> */
    public function getTrialConversion(): array
    {
        $total = Tenant::query()->count();
        $onTrial = Tenant::query()->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();
        $expired = Tenant::query()->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->count();
        $converted = $total - $onTrial - $expired;

        return [
            'on_trial' => $onTrial,
            'expired' => $expired,
            'converted' => $converted,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function getMonthlyGrowth(): array
    {
        $signups = $this->getSignupsByMonth();
        $growth = [];

        for ($i = 1; $i < count($signups); $i++) {
            $prev = (int) $signups[$i - 1]['count'];
            $curr = (int) $signups[$i]['count'];
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
        return Tenant::query()->count();
    }

    public function getThisMonthSignups(): int
    {
        return Tenant::query()->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    public function getAvgDaysOnTrial(): float
    {
        $tenants = Tenant::query()->whereNotNull('trial_ends_at')
            ->select('trial_ends_at', 'created_at')
            ->get();

        $avgDays = $tenants->avg(function (Tenant $tenant) {
            return Date::parse($tenant->created_at)
                ->diffInDays(Date::parse($tenant->trial_ends_at));
        });

        return round($avgDays ?? 0, 1);
    }

    public function getMostPopularPlan(): string
    {
        $value = Tenant::query()->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->orderByDesc('count')
            ->value('plan');

        return is_string($value) ? $value : 'N/A';
    }
}
