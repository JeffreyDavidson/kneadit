<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CapacityLimit;
use Illuminate\Http\JsonResponse;

class CapacityController extends Controller
{
    public function __invoke(string $date): JsonResponse
    {
        return response()->json([
            'data' => [
                'available' => CapacityLimit::isAvailable($date),
                'remaining' => CapacityLimit::remainingSlots($date),
                'max' => CapacityLimit::getMaxOrders($date),
            ],
            'message' => 'Capacity retrieved successfully.',
        ]);
    }
}
