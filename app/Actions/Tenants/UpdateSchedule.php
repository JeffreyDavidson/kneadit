<?php

namespace App\Actions\Tenants;

use App\Models\Operations\BusinessSchedule;

class UpdateSchedule
{
    /**
     * @param array<int, array{is_open: bool, open_time: ?string, close_time: ?string, order_cutoff_time: ?string, max_orders: ?int}> $schedule
     */
    public function __invoke(array $schedule): void
    {
        foreach ($schedule as $dayOfWeek => $data) {
            BusinessSchedule::query()->updateOrCreate(['day_of_week' => $dayOfWeek], [
                'is_open' => $data['is_open'] ?? false,
                'open_time' => ($data['is_open'] ?? false) ? ($data['open_time'] ?: null) : null,
                'close_time' => ($data['is_open'] ?? false) ? ($data['close_time'] ?: null) : null,
                'order_cutoff_time' => ($data['is_open'] ?? false) ? ($data['order_cutoff_time'] ?: null) : null,
                'max_orders' => ($data['is_open'] ?? false) ? ($data['max_orders'] ?: null) : null,
            ]);
        }
    }
}
