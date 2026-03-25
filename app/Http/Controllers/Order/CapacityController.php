<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\CapacityLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class CapacityController extends Controller
{
    /**
     * Check capacity for a specific date.
     */
    public function __invoke(string $date): JsonResponse
    {
        try {
            $carbon = Date::parse($date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 422);
        }

        $available = CapacityLimit::isAvailable($carbon);
        $remaining = CapacityLimit::remainingSlots($carbon);
        $maxOrders = CapacityLimit::getMaxOrders($carbon);

        return response()->json([
            'available' => $available,
            'remaining' => $remaining,
            'max_orders' => $maxOrders,
            'usage_percent' => CapacityLimit::usagePercent($carbon),
        ]);
    }
}
