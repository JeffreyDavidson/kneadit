<?php

namespace App\Filament\Central\Pages;

use App\Models\AdminAuditLog;
use App\Models\PlatformActivity;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Activity extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Activity';

    protected string $view = 'filament.central.pages.activity';

    public string $activeTab = 'platform';

    // ── Audit Trail Properties ──

    public string $filterAction = '';

    public string $filterSearch = '';

    public string $filterDateFrom = '';

    public string $filterDateTo = '';

    public int $page = 1;

    public int $perPage = 20;

    /** @var array<string, mixed> */
    protected array $queryString = [
        'filterAction' => ['except' => ''],
        'filterSearch' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // ── Platform Events (Activity Log) Methods ──

    public function getActivities(): Collection
    {
        return PlatformActivity::query()->latest('created_at')->limit(100)->get();
    }

    public static function getEventIcon(string $event): string
    {
        return match ($event) {
            'tenant_created' => 'heroicon-o-plus-circle',
            'tenant_deactivated' => 'heroicon-o-x-circle',
            'plan_changed' => 'heroicon-o-arrow-path',
            'storefront_toggled' => 'heroicon-o-globe-alt',
            'trial_expired' => 'heroicon-o-clock',
            default => 'heroicon-o-information-circle',
        };
    }

    public static function getEventColor(string $event): string
    {
        return match ($event) {
            'tenant_created' => '#d4920c',
            'tenant_deactivated' => '#ef4444',
            'plan_changed' => '#e8b04a',
            'storefront_toggled' => '#8b6844',
            'trial_expired' => '#f5d88e',
            default => '#d4920c',
        };
    }

    // ── Admin Actions (Audit Trail) Methods ──

    public function getLogsProperty(): LengthAwarePaginator
    {
        $query = AdminAuditLog::query()->latest();

        if ($this->filterAction) {
            $query->forAction($this->filterAction);
        }

        if ($this->filterSearch) {
            $query->whereLike('description', '%'.$this->filterSearch.'%');
        }

        if ($this->filterDateFrom) {
            $query->where('created_at', '>=', Date::parse($this->filterDateFrom)->startOfDay());
        }

        if ($this->filterDateTo) {
            $query->where('created_at', '<=', Date::parse($this->filterDateTo)->endOfDay());
        }

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function getTodayCountProperty(): int
    {
        return AdminAuditLog::query()->where('created_at', '>=', today())->count();
    }

    public function getWeekCountProperty(): int
    {
        return AdminAuditLog::query()->where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function getMostCommonActionProperty(): string
    {
        $action = AdminAuditLog::query()->select('action', DB::raw('count(*) as cnt'))
            ->where('created_at', '>=', now()->startOfWeek())
            ->groupBy('action')
            ->orderByDesc('cnt')
            ->first();

        return $action?->action ?? '—';
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function resetFilters(): void
    {
        $this->filterAction = '';
        $this->filterSearch = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->page = 1;
    }

    public static function getActionTypes(): array
    {
        return [
            'created_tenant',
            'updated_tenant',
            'deleted_tenant',
            'changed_plan',
            'extended_trial',
            'activated',
            'deactivated',
            'impersonated',
            'sent_announcement',
            'sent_campaign',
            'sent_message',
            'exported_data',
            'toggled_maintenance',
        ];
    }

    public static function getActionColor(string $action): string
    {
        return match ($action) {
            'created_tenant', 'updated_tenant', 'deleted_tenant', 'activated', 'deactivated' => '#3b82f6',
            'changed_plan', 'extended_trial' => '#22c55e',
            'sent_announcement', 'sent_campaign', 'sent_message' => '#a855f7',
            'impersonated' => '#f59e0b',
            'exported_data', 'toggled_maintenance' => '#6b7280',
            default => '#6b7280',
        };
    }

    public static function getActionCategory(string $action): string
    {
        return match ($action) {
            'created_tenant', 'updated_tenant', 'deleted_tenant', 'activated', 'deactivated' => 'Tenant',
            'changed_plan', 'extended_trial' => 'Billing',
            'sent_announcement', 'sent_campaign', 'sent_message' => 'Communication',
            'impersonated' => 'Security',
            'exported_data', 'toggled_maintenance' => 'Operations',
            default => 'Other',
        };
    }
}
