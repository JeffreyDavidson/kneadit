<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Http\JsonResponse;

class CapacityController extends Controller
{
    public function __invoke(string $date, CapacityCalculator $calculator): JsonResponse
    {
        return ApiResponse::success([
            'available' => $calculator->isAvailable($date),
            'remaining' => $calculator->remainingSlots($date),
            'max' => $calculator->getMaxOrders($date),
        ], 'Capacity retrieved successfully.');
    }
}
