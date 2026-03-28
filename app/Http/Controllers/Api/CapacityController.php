<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Http\JsonResponse;

class CapacityController extends Controller
{
    public function __invoke(string $date, CapacityCalculator $calculator): JsonResponse
    {
        return response()->json([
            'data' => [
                'available' => $calculator->isAvailable($date),
                'remaining' => $calculator->remainingSlots($date),
                'max' => $calculator->getMaxOrders($date),
            ],
            'message' => 'Capacity retrieved successfully.',
        ]);
    }
}
