<?php

namespace App\Queries\Platform;

use App\Models\Platform\PlatformActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PlatformActivityQuery
{
    /**
     * @param array{event?: string, search?: string, date_from?: string, date_to?: string} $filters
     * @return Collection<int, PlatformActivity>
     */
    public function get(array $filters): Collection
    {
        $query = PlatformActivity::query()->latest('created_at');

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search): void {
                $query->whereLike('description', "%{$search}%")
                    ->orWhereLike('tenant_id', "%{$search}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Date::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Date::parse($filters['date_to'])->endOfDay());
        }

        return $query->limit(100)->get();
    }

    public function todayCount(): int
    {
        return PlatformActivity::query()->where('created_at', '>=', today())->count();
    }

    public function weekCount(): int
    {
        return PlatformActivity::query()->where('created_at', '>=', now()->startOfWeek())->count();
    }

    public function mostCommonEvent(): string
    {
        $row = PlatformActivity::query()->select('event', DB::raw('count(*) as cnt'))
            ->where('created_at', '>=', now()->startOfWeek())
            ->groupBy('event')->orderByDesc('cnt')->first();

        return $row->event ?? '—';
    }
}
