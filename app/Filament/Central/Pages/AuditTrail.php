<?php

namespace App\Filament\Central\Pages;

use App\Models\AdminAuditLog;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class AuditTrail extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Audit Trail';

    protected string $view = 'filament.central.pages.audit-trail';

    public string $filterAction = '';
    public string $filterSearch = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';
    public int $page = 1;
    public int $perPage = 20;

    protected $queryString = [
        'filterAction' => ['except' => ''],
        'filterSearch' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function getLogsProperty()
    {
        $query = AdminAuditLog::query()->orderByDesc('created_at');

        if ($this->filterAction) {
            $query->forAction($this->filterAction);
        }

        if ($this->filterSearch) {
            $query->where('description', 'like', '%' . $this->filterSearch . '%');
        }

        if ($this->filterDateFrom) {
            $query->where('created_at', '>=', Carbon::parse($this->filterDateFrom)->startOfDay());
        }

        if ($this->filterDateTo) {
            $query->where('created_at', '<=', Carbon::parse($this->filterDateTo)->endOfDay());
        }

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function getTodayCountProperty(): int
    {
        return AdminAuditLog::where('created_at', '>=', now()->startOfDay())->count();
    }

    public function getWeekCountProperty(): int
    {
        return AdminAuditLog::where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function getMostCommonActionProperty(): string
    {
        $action = AdminAuditLog::select('action', DB::raw('count(*) as cnt'))
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
