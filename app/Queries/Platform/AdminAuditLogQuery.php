<?php

namespace App\Queries\Platform;

use App\Models\Platform\AdminAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class AdminAuditLogQuery
{
    /**
     * @param array{action?: string, search?: string, date_from?: string, date_to?: string} $filters
     * @return LengthAwarePaginator<int, AdminAuditLog>
     */
    public function paginate(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        $query = $this->filteredQuery($filters);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function todayCount(): int
    {
        return AdminAuditLog::query()->where('created_at', '>=', today())->count();
    }

    public function weekCount(): int
    {
        return AdminAuditLog::query()->where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function mostCommonAction(): string
    {
        $row = AdminAuditLog::query()->select('action', DB::raw('count(*) as cnt'))
            ->where('created_at', '>=', now()->startOfWeek())
            ->groupBy('action')->orderByDesc('cnt')->first();

        return $row->action ?? '—';
    }

    /**
     * @param array{action?: string, search?: string, date_from?: string, date_to?: string} $filters
     * @return Builder<AdminAuditLog>
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = AdminAuditLog::query()->latest();

        if (! empty($filters['action'])) {
            $query->forAction($filters['action']);
        }

        if (! empty($filters['search'])) {
            $query->whereLike('description', "%{$filters['search']}%");
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Date::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Date::parse($filters['date_to'])->endOfDay());
        }

        return $query;
    }
}
