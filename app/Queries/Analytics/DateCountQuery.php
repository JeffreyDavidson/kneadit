<?php

namespace App\Queries\Analytics;

use App\DataTransferObjects\Analytics\DateSeries;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

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

    /**
     * Counts rows per local calendar day of `created_at`, for the days from
     * $start to $end in $timezone. Each local day is turned into a [start, end)
     * range of app-timezone instants and counted in one query, so it behaves the
     * same on SQLite and MySQL. Days without rows are left out.
     *
     * @param  Builder<covariant Model>  $query
     * @return array<string, int>
     */
    public static function countByLocalDay(Builder $query, Carbon $start, Carbon $end, DateTimeZone $timezone): array
    {
        $days = DateSeries::between($start, $end)->dates();
        $appTimezone = Config::string('app.timezone');
        $counts = $query->getQuery()->newQuery();

        foreach ($days as $index => $day) {
            $dayStart = Date::parse($day, $timezone)->startOfDay();

            $counts->selectSub(
                $query->clone()->toBase()
                    ->selectRaw('COUNT(*)')
                    ->where('created_at', '>=', $dayStart->copy()->setTimezone($appTimezone))
                    ->where('created_at', '<', $dayStart->copy()->addDay()->startOfDay()->setTimezone($appTimezone)),
                "day_{$index}",
            );
        }

        $row = (array) $counts->first();
        $result = [];

        foreach ($days as $index => $day) {
            $count = Arr::integer(['count' => $row["day_{$index}"] ?? 0], 'count', 0);

            if ($count > 0) {
                $result[$day] = $count;
            }
        }

        return $result;
    }
}
