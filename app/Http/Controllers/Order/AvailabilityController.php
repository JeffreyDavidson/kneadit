<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\BusinessSchedule;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Date;

class AvailabilityController extends Controller
{
    /**
     * Return availability for the next 30 days.
     */
    public function __invoke()
    {
        $dates = [];
        $today = Date::today();

        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->addDays($i);
            $dayOfWeek = (int) $date->dayOfWeek;
            $dateStr = $date->toDateString();

            $schedule = BusinessSchedule::forDay($dayOfWeek);
            $isOpen = $schedule?->is_open ?? false;

            // Check blocked dates
            $blocked = BlockedDate::where('date', $dateStr)->where('is_all_day', true)->first();

            if ($blocked) {
                $dates[] = [
                    'date' => $dateStr,
                    'available' => false,
                    'reason' => $blocked->reason ?? 'Blocked',
                    'remaining_capacity' => 0,
                ];

                continue;
            }

            if (! $isOpen) {
                $dates[] = [
                    'date' => $dateStr,
                    'available' => false,
                    'reason' => 'Closed',
                    'remaining_capacity' => 0,
                ];

                continue;
            }

            // Check capacity
            $maxOrders = $schedule->max_orders
                ?? (int) Setting::get('default_daily_capacity', 100);
            $currentOrders = Order::whereDate('delivery_date', $dateStr)
                ->whereNotIn('status', [OrderStatus::Cancelled])
                ->count();
            $remaining = max(0, $maxOrders - $currentOrders);

            $dates[] = [
                'date' => $dateStr,
                'available' => $remaining > 0,
                'reason' => $remaining > 0 ? 'Open' : 'Fully booked',
                'remaining_capacity' => $remaining,
            ];
        }

        return response()->json($dates);
    }
}
