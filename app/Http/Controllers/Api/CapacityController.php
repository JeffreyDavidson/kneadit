<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Http\JsonResponse;

class CapacityController extends Controller
{
    public function __invoke(string $date): JsonResponse
    {
        return response()->json([
            'data' => [
                'available' => resolve(CapacityCalculator::class)->isAvailable($date),
                'remaining' => resolve(CapacityCalculator::class)->remainingSlots($date),
                'max' => resolve(CapacityCalculator::class)->getMaxOrders($date),
            ],
            'message' => 'Capacity retrieved successfully.',
        ]);
    }
}
