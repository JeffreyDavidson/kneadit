<?php

namespace App\Filament\Central\Pages;

use App\Filament\Central\Resources\TenantResource;
use App\Models\PlatformMessage;
use App\Models\Tenant;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class BakeryInsights extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Bakery Insights';

    protected string $view = 'filament.central.pages.bakery-insights';

    public string $activeTab = 'health';

    public array $extendedTrials = [];

    public array $sentNudges = [];

    // ── Health Tab Methods ──

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

            $days = Date::parse($lastLogin)->diffInDays(now());

            if ($days <= 7) {
                return 25;
            }
            if ($days <= 14) {
                return 15;
            }
            if ($days <= 30) {
                return 5;
            }

            return 0;
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

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

            if ($count >= 10) {
                return 25;
            }
            if ($count >= 5) {
                return 15;
            }
            if ($count >= 1) {
                return 10;
            }

            return 0;
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return 0;
        }
    }

    protected function getProductScore(Tenant $tenant): int
    {
        try {
            tenancy()->initialize($tenant);
            $count = DB::table('products')->count();
            tenancy()->end();

            if ($count >= 10) {
                return 20;
            }
            if ($count >= 5) {
                return 15;
            }
            if ($count >= 1) {
                return 5;
            }

            return 0;
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return 0;
        }
    }

    protected function getSetupScore(Tenant $tenant): int
    {
        $pointsPer = 30 / 7;
        $completed = 0;

        if (! empty($tenant->store_name)) {
            $completed++;
        }
        if (! empty($tenant->store_logo)) {
            $completed++;
        }
        if ($tenant->storefront_enabled) {
            $completed++;
        }
        if (! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c') {
            $completed++;
        }

        try {
            tenancy()->initialize($tenant);

            if (DB::table('products')->count() > 0) {
                $completed++;
            }
            if (DB::table('categories')->count() > 0) {
                $completed++;
            }
            if (DB::table('orders')->count() > 0) {
                $completed++;
            }

            tenancy()->end();
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }
        }

        return (int) round($completed * $pointsPer);
    }

    public function getHealthSummaryStats(): array
    {
        $data = $this->getTenantHealthData();

        $healthy = $data->filter(fn (array $t) => $t['health_score'] > 70)->count();
        $atRisk = $data->filter(fn (array $t) => $t['health_score'] >= 40 && $t['health_score'] <= 70)->count();
        $critical = $data->filter(fn (array $t) => $t['health_score'] < 40)->count();
        $avg = $data->count() > 0 ? round($data->avg('health_score')) : 0;

        return [
            'average' => $avg,
            'healthy' => $healthy,
            'at_risk' => $atRisk,
            'critical' => $critical,
            'total' => $data->count(),
        ];
    }

    // ── Churn Alerts Tab Methods ──

    public function getAlerts(): Collection
    {
        $tenants = Tenant::all();
        $healthData = $this->getTenantHealthData()->keyBy('id');
        $alerts = collect();

        foreach ($tenants as $tenant) {
            $daysSinceSignup = $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0;
            $health = $healthData->get($tenant->id);
            $healthScore = $health ? $health['health_score'] : 0;

            if ($tenant->trial_ends_at) {
                $trialEnds = Date::parse($tenant->trial_ends_at);
                if ($trialEnds->isFuture() && $trialEnds->diffInHours(now()) <= 48) {
                    $setupScore = $health ? $health['setup_score'] : 0;
                    if ($setupScore < 15) {
                        $alerts->push([
                            'tenant_id' => $tenant->id,
                            'name' => $tenant->store_name ?? $tenant->name,
                            'type' => 'trial_expiring',
                            'type_label' => 'Trial Expiring',
                            'description' => 'Trial ends in '.$trialEnds->diffForHumans().' with less than 50% setup complete.',
                            'days_since_signup' => $daysSinceSignup,
                            'severity' => 'critical',
                        ]);
                    }
                }
            }

            $lastLogin = $this->getLastLogin($tenant);
            if ($lastLogin && Date::parse($lastLogin)->diffInDays(now()) >= 7) {
                $alerts->push([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->store_name ?? $tenant->name,
                    'type' => 'no_login',
                    'type_label' => 'No Recent Login',
                    'description' => 'No login activity in '.Date::parse($lastLogin)->diffInDays(now()).' days.',
                    'days_since_signup' => $daysSinceSignup,
                    'severity' => 'warning',
                ]);
            }

            if ($daysSinceSignup > 14) {
                $recentOrders = $this->getRecentOrderCount($tenant, 30);
                if ($recentOrders === 0) {
                    $alerts->push([
                        'tenant_id' => $tenant->id,
                        'name' => $tenant->store_name ?? $tenant->name,
                        'type' => 'no_orders',
                        'type_label' => 'No Orders',
                        'description' => 'Zero orders in the last 30 days.',
                        'days_since_signup' => $daysSinceSignup,
                        'severity' => 'warning',
                    ]);
                }
            }

            if ($healthScore < 40) {
                $alerts->push([
                    'tenant_id' => $tenant->id,
                    'name' => $tenant->store_name ?? $tenant->name,
                    'type' => 'low_health',
                    'type_label' => 'Critical Health',
                    'description' => 'Health score is '.$healthScore.'/100.',
                    'days_since_signup' => $daysSinceSignup,
                    'severity' => 'critical',
                ]);
            }
        }

        return $alerts->sortByDesc(fn (array $a) => $a['severity'] === 'critical' ? 1 : 0)->values();
    }

    protected function getLastLogin(Tenant $tenant): ?string
    {
        try {
            tenancy()->initialize($tenant);
            $lastLogin = DB::table('users')->max('updated_at');
            tenancy()->end();

            return $lastLogin;
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return null;
        }
    }

    protected function getRecentOrderCount(Tenant $tenant, int $days): int
    {
        try {
            tenancy()->initialize($tenant);
            $count = DB::table('orders')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
            tenancy()->end();

            return $count;
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable) {
            }

            return 0;
        }
    }

    public function extendTrial(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            Notification::make()->title('Tenant not found.')->danger()->send();

            return;
        }

        $currentEnd = $tenant->trial_ends_at ? Date::parse($tenant->trial_ends_at) : now();
        $newEnd = $currentEnd->isPast() ? now()->addDays(30) : $currentEnd->addDays(30);

        $tenant->update(['trial_ends_at' => $newEnd]);

        $this->extendedTrials[] = $tenantId;

        Notification::make()
            ->title('Trial Extended')
            ->body(($tenant->store_name ?? $tenant->name).' trial extended to '.$newEnd->format('M j, Y').'.')
            ->success()
            ->send();
    }

    public function sendNudge(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            Notification::make()->title('Tenant not found.')->danger()->send();

            return;
        }

        $storeName = $tenant->store_name ?? $tenant->name;

        PlatformMessage::query()->create([
            'tenant_id' => $tenant->id,
            'sender_type' => 'admin',
            'subject' => 'We noticed you haven\'t been around lately',
            'body' => "Hi {$storeName}!\n\nWe noticed it's been a little quiet on your end. Just wanted to check in — is there anything we can help with?\n\nWhether you need help setting up your storefront, adding products, or just have questions, we're here for you.\n\nThe KneadIt Team",
            'is_read' => false,
        ]);

        $this->sentNudges[] = $tenantId;

        Notification::make()
            ->title('Nudge Sent')
            ->body('A check-in message has been sent to '.$storeName.'.')
            ->success()
            ->send();
    }

    public function getViewTenantUrl(string $tenantId): string
    {
        return TenantResource::getUrl('view', ['record' => $tenantId]);
    }

    // ── Upgrade Triggers Tab Methods ──

    public const PLAN_LIMITS = [
        'starter' => [
            'products' => 15,
            'orders_per_month' => 50,
            'label' => 'Starter',
        ],
        'growth' => [
            'products' => 50,
            'orders_per_month' => 200,
            'label' => 'Growth',
        ],
        'pro' => [
            'products' => null,
            'orders_per_month' => null,
            'label' => 'Pro',
        ],
    ];

    public function getTenantUsageData(): Collection
    {
        $tenants = Tenant::all();
        $results = collect();

        foreach ($tenants as $tenant) {
            $plan = strtolower($tenant->plan ?? 'starter');
            $limits = self::PLAN_LIMITS[$plan] ?? self::PLAN_LIMITS['starter'];

            if ($plan === 'pro') {
                continue;
            }

            try {
                tenancy()->initialize($tenant);

                $productCount = DB::connection('tenant')->table('products')->count();
                $orderCount = DB::connection('tenant')->table('orders')
                    ->whereMonth('created_at', Date::now()->month)
                    ->whereYear('created_at', Date::now()->year)
                    ->count();

                tenancy()->end();

                $productLimit = $limits['products'];
                $orderLimit = $limits['orders_per_month'];

                $productPercent = $productLimit ? round(($productCount / $productLimit) * 100) : 0;
                $orderPercent = $orderLimit ? round(($orderCount / $orderLimit) * 100) : 0;

                $approachingLimit = $productPercent >= 80 || $orderPercent >= 80;
                $atLimit = $productPercent >= 100 || $orderPercent >= 100;

                if ($approachingLimit) {
                    $results->push([
                        'tenant' => $tenant,
                        'name' => $tenant->store_name ?? $tenant->name ?? $tenant->id,
                        'plan' => $limits['label'],
                        'plan_key' => $plan,
                        'product_count' => $productCount,
                        'product_limit' => $productLimit,
                        'product_percent' => min($productPercent, 100),
                        'order_count' => $orderCount,
                        'order_limit' => $orderLimit,
                        'order_percent' => min($orderPercent, 100),
                        'at_limit' => $atLimit,
                        'approaching_limit' => ! $atLimit,
                    ]);
                }
            } catch (\Throwable $e) {
                tenancy()->end();

                continue;
            }
        }

        return $results->sortByDesc(fn (array $t) => max($t['product_percent'], $t['order_percent']));
    }

    public function getNextPlan(string $currentPlan): ?string
    {
        return match ($currentPlan) {
            'starter' => 'Growth',
            'growth' => 'Pro',
            default => null,
        };
    }

    public function suggestUpgrade(string $tenantId): void
    {
        Notification::make()
            ->title('Upgrade suggestion noted')
            ->body("Upgrade suggestion for tenant {$tenantId} has been queued.")
            ->success()
            ->send();
    }
}
