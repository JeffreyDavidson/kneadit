<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class TenantHealth extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Tenant Health';

    protected string $view = 'filament.central.pages.tenant-health';

    public function getTenantHealthData(): Collection
    {
        $tenants = Tenant::all();

        $data = $tenants->map(function (Tenant $tenant) {
            $loginScore = $this->getLoginScore($tenant);
            $orderScore = $this->getOrderScore($tenant);
            $productScore = $this->getProductScore($tenant);
            $setupScore = $this->getSetupScore($tenant);
            $total = $loginScore + $orderScore + $productScore + $setupScore;

            return [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan ?? 'free',
                'health_score' => $total,
                'login_score' => $loginScore,
                'order_score' => $orderScore,
                'product_score' => $productScore,
                'setup_score' => $setupScore,
            ];
        });

        return $data->sortBy('health_score')->values();
    }

    protected function getLoginScore(Tenant $tenant): int
    {
        try {
            tenancy()->initialize($tenant);
            $lastLogin = DB::table('users')->max('updated_at');
            tenancy()->end();

            if (! $lastLogin) {
                return 0;
            }

            $days = Carbon::parse($lastLogin)->diffInDays(now());

            if ($days <= 7) return 25;
            if ($days <= 14) return 15;
            if ($days <= 30) return 5;

            return 0;
        } catch (\Throwable $e) {
            try { tenancy()->end(); } catch (\Throwable) {}
            return 0;
        }
    }

    protected function getOrderScore(Tenant $tenant): int
    {
        try {
            tenancy()->initialize($tenant);
            $count = DB::table('orders')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
            tenancy()->end();

            if ($count >= 10) return 25;
            if ($count >= 5) return 15;
            if ($count >= 1) return 10;

            return 0;
        } catch (\Throwable $e) {
            try { tenancy()->end(); } catch (\Throwable) {}
            return 0;
        }
    }

    protected function getProductScore(Tenant $tenant): int
    {
        try {
            tenancy()->initialize($tenant);
            $count = DB::table('products')->count();
            tenancy()->end();

            if ($count >= 10) return 20;
            if ($count >= 5) return 15;
            if ($count >= 1) return 5;

            return 0;
        } catch (\Throwable $e) {
            try { tenancy()->end(); } catch (\Throwable) {}
            return 0;
        }
    }

    protected function getSetupScore(Tenant $tenant): int
    {
        $pointsPer = 30 / 7; // ~4.3 per check
        $completed = 0;

        // Central checks
        if (! empty($tenant->store_name)) $completed++;
        if (! empty($tenant->store_logo)) $completed++;
        if ($tenant->storefront_enabled) $completed++;
        if (! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c') $completed++;

        // Tenant DB checks
        try {
            tenancy()->initialize($tenant);

            if (DB::table('products')->count() > 0) $completed++;
            if (DB::table('categories')->count() > 0) $completed++;
            if (DB::table('orders')->count() > 0) $completed++;

            tenancy()->end();
        } catch (\Throwable $e) {
            try { tenancy()->end(); } catch (\Throwable) {}
        }

        return (int) round($completed * $pointsPer);
    }

    public function getSummaryStats(): array
    {
        $data = $this->getTenantHealthData();

        $healthy = $data->filter(fn ($t) => $t['health_score'] > 70)->count();
        $atRisk = $data->filter(fn ($t) => $t['health_score'] >= 40 && $t['health_score'] <= 70)->count();
        $critical = $data->filter(fn ($t) => $t['health_score'] < 40)->count();
        $avg = $data->count() > 0 ? round($data->avg('health_score')) : 0;

        return [
            'average' => $avg,
            'healthy' => $healthy,
            'at_risk' => $atRisk,
            'critical' => $critical,
            'total' => $data->count(),
        ];
    }
}
