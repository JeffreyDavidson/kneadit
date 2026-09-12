<?php

namespace App\Services\Tenants;

use App\Contracts\Tenants\LegacySchedulingImporter;
use Illuminate\Support\Facades\DB;

class DatabaseLegacySchedulingImporter implements LegacySchedulingImporter
{
    /**
     * @param array<int, array<string, mixed>> $capacityLimits
     * @param array<int, array<string, mixed>> $holidays
     */
    public function import(array $capacityLimits, array $holidays): void
    {
        foreach ($capacityLimits as $limit) {
            $day = $limit['day_of_week'] ?? null;
            $specificDate = $limit['specific_date'] ?? null;
            $date = $specificDate ?? now()->startOfWeek()->addDays($this->integer($day))->toDateString();
            DB::table('capacity_limits')->updateOrInsert(
                $specificDate ? ['specific_date' => $specificDate] : ['day_of_week' => $this->string($day)],
                ['date' => $date, 'max_orders' => $limit['max_orders'], 'is_blocked' => $limit['is_blocked'] ?? false, 'notes' => $limit['notes'] ?? null, 'created_at' => $limit['created_at'] ?? now(), 'updated_at' => $limit['updated_at'] ?? now()],
            );
        }
        foreach ($holidays as $holiday) {
            DB::table('holidays')->updateOrInsert(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                ['lead_days' => $holiday['lead_days'] ?? 7, 'order_deadline' => $holiday['order_deadline'] ?? null, 'prep_start' => $holiday['prep_start'] ?? null, 'max_orders' => $holiday['max_orders'] ?? null, 'notes' => $holiday['notes'] ?? null, 'is_active' => $holiday['is_active'] ?? true, 'created_at' => $holiday['created_at'] ?? now(), 'updated_at' => $holiday['updated_at'] ?? now()],
            );
        }
    }

    private function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new \UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
