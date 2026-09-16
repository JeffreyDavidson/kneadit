<?php

namespace App\Queries\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

final class DateCountQuery
{
    /**
     * @param  Builder<covariant Model>  $query
     * @return array<string, int>
     */
    public static function count(Builder $query, string $dateColumn, Carbon $start, Carbon $end): array
    {
        $query = $query
            ->whereBetween($dateColumn, [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->toBase();

        $query = match ($dateColumn) {
            'created_at' => $query->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate'),
            'delivery_date' => $query->selectRaw('DATE(delivery_date) as date, COUNT(*) as aggregate'),
            default => throw new \InvalidArgumentException("Unsupported date column: {$dateColumn}"),
        };

        return $query
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->mapWithKeys(fn (mixed $count, mixed $date): array => [
                Arr::string(['date' => $date], 'date') => Arr::integer(['count' => $count], 'count', 0),
            ])
            ->all();
    }
}
