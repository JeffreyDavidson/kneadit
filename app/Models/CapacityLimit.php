<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class CapacityLimit extends Model
{
    protected $fillable = [
        'date',
        'max_orders',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'max_orders' => 'integer',
        ];
    }

    /**
     * Get the capacity limit for a given date.
     */
    public static function forDate(Carbon|string $date): ?self
    {
        $date = Date::parse($date);

        return static::whereDate('date', $date)->first();
    }

    /**
     * Get the effective max orders for a date (uses Setting fallback).
     */
    public static function getMaxOrders(Carbon|string $date): int
    {
        $limit = static::forDate($date);

        if ($limit && $limit->max_orders > 0) {
            return $limit->max_orders;
        }

        return (int) Setting::get('default_daily_capacity', 20);
    }

    /**
     * Check if a date is available for orders.
     */
    public static function isAvailable(Carbon|string $date): bool
    {
        $maxOrders = static::getMaxOrders($date);
        $currentCount = static::ordersOnDate($date);

        return $currentCount < $maxOrders;
    }

    /**
     * Get remaining order slots for a date.
     */
    public static function remainingSlots(Carbon|string $date): int
    {
        $maxOrders = static::getMaxOrders($date);
        $currentCount = static::ordersOnDate($date);

        return max(0, $maxOrders - $currentCount);
    }

    /**
     * Count non-cancelled orders on a given date.
     */
    protected static function ordersOnDate(Carbon|string $date): int
    {
        return Order::whereDate('delivery_date', Date::parse($date))
            ->where('status', '!=', OrderStatus::Cancelled)
            ->count();
    }

    /**
     * Get capacity usage percentage for a date.
     */
    public static function usagePercent(Carbon|string $date): float
    {
        $maxOrders = static::getMaxOrders($date);
        if ($maxOrders <= 0) {
            return 0.0;
        }

        $currentCount = static::ordersOnDate($date);

        return min(100, ($currentCount / $maxOrders) * 100);
    }
}
