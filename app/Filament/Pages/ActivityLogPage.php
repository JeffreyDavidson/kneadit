<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ActivityLogPage extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return true;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.activity-log';

    public ?string $filterAction = null;

    public ?string $filterModelType = null;

    public ?string $filterUser = null;

    public ?string $filterDateFrom = null;

    public ?string $filterDateTo = null;

    public int $page = 1;

    public int $perPage = 25;

    public ?int $expandedId = null;

    public function getActivitiesProperty(): LengthAwarePaginator
    {
        $query = ActivityLog::query()->latest();

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterModelType) {
            $query->where('model_type', $this->filterModelType);
        }

        if ($this->filterUser) {
            $query->whereLike('user_name', "%{$this->filterUser}%");
        }

        if ($this->filterDateFrom) {
            $query->where('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('created_at', '<=', $this->filterDateTo.' 23:59:59');
        }

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /** @return array<string, mixed> */
    public function getActionTypesProperty(): array
    {
        return ActivityLog::query()->distinct()->pluck('action')->filter()->sort()->values()->toArray();
    }

    /** @return array<int, array<string, string>> */
    public function getModelTypesProperty(): array
    {
        return ActivityLog::query()->distinct()->pluck('model_type')->filter()->map(fn (string $t) => [
            'value' => $t,
            'label' => class_basename($t),
        ])->sortBy('label')->values()->all();
    }

    public function toggleExpanded(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function resetFilters(): void
    {
        $this->filterAction = null;
        $this->filterModelType = null;
        $this->filterUser = null;
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->page = 1;
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function updatedFilterAction(): void
    {
        $this->page = 1;
    }

    public function updatedFilterModelType(): void
    {
        $this->page = 1;
    }

    public function updatedFilterUser(): void
    {
        $this->page = 1;
    }

    public function updatedFilterDateFrom(): void
    {
        $this->page = 1;
    }

    public function updatedFilterDateTo(): void
    {
        $this->page = 1;
    }
}
