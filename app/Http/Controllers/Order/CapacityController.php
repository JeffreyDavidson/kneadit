<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;

class CapacityController extends Controller
{
    /**
     * Check capacity for a specific date.
     */
    public function __invoke(string $date, CapacityCalculator $calculator): JsonResponse
    {
        try {
            $carbon = Date::parse($date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 422);
        }

        return response()->json([
            'available' => $calculator->isAvailable($carbon),
            'remaining' => $calculator->remainingSlots($carbon),
            'max_orders' => $calculator->getMaxOrders($carbon),
            'usage_percent' => $calculator->usagePercent($carbon),
        ]);
    }
}
