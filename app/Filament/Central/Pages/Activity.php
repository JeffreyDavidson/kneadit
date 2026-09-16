<?php

namespace App\Filament\Central\Pages;

use App\Enums\Platform\PlatformEventType;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\PlatformActivity;
use App\Queries\Platform\AdminAuditLogQuery;
use App\Queries\Platform\PlatformActivityQuery;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class Activity extends Page
{
    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[\Override]
    protected static ?int $navigationSort = 4;

    #[\Override]
    protected static ?string $title = 'Activity';

    #[\Override]
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
        return resolve(PlatformActivityQuery::class)->get([
            'event' => $this->filterEvent,
            'search' => $this->filterEventSearch,
            'date_from' => $this->filterEventDateFrom,
            'date_to' => $this->filterEventDateTo,
        ]);
    }

    public function getEventTodayCountProperty(): int
    {
        return resolve(PlatformActivityQuery::class)->todayCount();
    }

    public function getEventWeekCountProperty(): int
    {
        return resolve(PlatformActivityQuery::class)->weekCount();
    }

    public function getMostCommonEventProperty(): string
    {
        return resolve(PlatformActivityQuery::class)->mostCommonEvent();
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
        return resolve(AdminAuditLogQuery::class)->paginate([
            'action' => $this->filterAction,
            'search' => $this->filterSearch,
            'date_from' => $this->filterDateFrom,
            'date_to' => $this->filterDateTo,
        ], $this->perPage, $this->page);
    }

    public function getTodayCountProperty(): int
    {
        return resolve(AdminAuditLogQuery::class)->todayCount();
    }

    public function getWeekCountProperty(): int
    {
        return resolve(AdminAuditLogQuery::class)->weekCount();
    }

    public function getMostCommonActionProperty(): string
    {
        return resolve(AdminAuditLogQuery::class)->mostCommonAction();
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
