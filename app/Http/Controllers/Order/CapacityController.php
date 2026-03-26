<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\CapacityCalculator;
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

        $available = resolve(CapacityCalculator::class)->isAvailable($carbon);
        $remaining = resolve(CapacityCalculator::class)->remainingSlots($carbon);
        $maxOrders = resolve(CapacityCalculator::class)->getMaxOrders($carbon);

        return response()->json([
            'available' => $available,
            'remaining' => $remaining,
            'max_orders' => $maxOrders,
            'usage_percent' => resolve(CapacityCalculator::class)->usagePercent($carbon),
        ]);
    }
}
