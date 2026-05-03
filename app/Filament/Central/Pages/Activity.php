<?php

namespace App\Filament\Central\Pages;

use App\Enums\Platform\PlatformEventType;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\PlatformActivity;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Activity extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Activity';

    protected string $view = 'filament.central.pages.activity';

    public string $activeTab = 'platform';

    // ── Platform Events filters ──

    public string $filterEvent = '';

    public string $filterEventSearch = '';

    public string $filterEventDateFrom = '';

    public string $filterEventDateTo = '';

    // ── Audit Trail Properties ──

    public string $filterAction = '';

    public string $filterSearch = '';

    public string $filterDateFrom = '';

    public string $filterDateTo = '';

    public int $page = 1;

    public int $perPage = 20;

    /** @var array<string, mixed> */
    protected array $queryString = [
        'filterEvent' => ['except' => ''],
        'filterEventSearch' => ['except' => ''],
        'filterEventDateFrom' => ['except' => ''],
        'filterEventDateTo' => ['except' => ''],
        'filterAction' => ['except' => ''],
        'filterSearch' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // ── Platform Events (Activity Log) Methods ──

    /** @return Collection<int, PlatformActivity> */
    public function getActivities(): Collection
    {
        $query = PlatformActivity::query()->latest('created_at');

        if ($this->filterEvent) {
            $query->where('event', $this->filterEvent);
        }

        if ($this->filterEventSearch) {
            $query->where(function (\Illuminate\Database\Eloquent\Builder $q): void {
                $q->whereLike('description', '%' . $this->filterEventSearch . '%')
                    ->orWhereLike('tenant_id', '%' . $this->filterEventSearch . '%');
            });
        }

        if ($this->filterEventDateFrom) {
            $query->where('created_at', '>=', Date::parse($this->filterEventDateFrom)->startOfDay());
        }

        if ($this->filterEventDateTo) {
            $query->where('created_at', '<=', Date::parse($this->filterEventDateTo)->endOfDay());
        }

        return $query->limit(100)->get();
    }

    public function getEventTodayCountProperty(): int
    {
        return PlatformActivity::query()->where('created_at', '>=', today())->count();
    }

    public function getEventWeekCountProperty(): int
    {
        return PlatformActivity::query()->where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function getMostCommonEventProperty(): string
    {
        $row = PlatformActivity::query()->select('event', DB::raw('count(*) as cnt'))
            ->where('created_at', '>=', now()->startOfWeek())
            ->groupBy('event')
            ->orderByDesc('cnt')
            ->first();

        return $row->event ?? '—';
    }

    public function resetEventFilters(): void
    {
        $this->filterEvent = '';
        $this->filterEventSearch = '';
        $this->filterEventDateFrom = '';
        $this->filterEventDateTo = '';
    }

    /** @return array<int, string> */
    public static function getEventTypes(): array
    {
        return array_map(
            fn (PlatformEventType $type) => $type->value,
            PlatformEventType::cases(),
        );
    }

    public static function getEventIcon(string $event): Heroicon
    {
        return PlatformEventType::tryFrom($event)?->getIcon()
            ?? Heroicon::OutlinedInformationCircle;
    }

    public static function getEventColor(string $event): string
    {
        return PlatformEventType::tryFrom($event)?->getColor()
            ?? '#d4920c';
    }

    public static function getEventIconColorClass(string $event): string
    {
        return PlatformEventType::tryFrom($event)?->getIconColorClass()
            ?? 'text-honey';
    }

    public static function getEventBorderColorClass(string $event): string
    {
        return PlatformEventType::tryFrom($event)?->getBorderColorClass()
            ?? 'border-honey';
    }

    // ── Admin Actions (Audit Trail) Methods ──

    /** @return LengthAwarePaginator<int, AdminAuditLog> */
    public function getLogsProperty(): LengthAwarePaginator
    {
        $query = AdminAuditLog::query()->latest();

        if ($this->filterAction) {
            $query->forAction($this->filterAction);
        }

        if ($this->filterSearch) {
            $query->whereLike('description', '%' . $this->filterSearch . '%');
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

        return $action->action ?? '—';
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

    /** @return array<int, string> */
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

    public static function getActionColorClass(string $action): string
    {
        return match ($action) {
            'created_tenant', 'updated_tenant', 'deleted_tenant', 'activated', 'deactivated' => 'bg-blue-500',
            'changed_plan', 'extended_trial' => 'bg-green-500',
            'sent_announcement', 'sent_campaign', 'sent_message' => 'bg-purple-500',
            'impersonated' => 'bg-amber-500',
            'exported_data', 'toggled_maintenance' => 'bg-gray-500',
            default => 'bg-gray-500',
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
